<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\persistence\orm;

use n2n\core\container\PdoPool;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\BaseCriteria;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\store\PersistenceContext;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\persistence\orm\store\action\ActionQueueImpl;
use n2n\persistence\orm\store\operation\PersistOperation;
use n2n\persistence\orm\store\operation\MergeOperationImpl;
use n2n\persistence\orm\store\operation\RemoveOperation;
use n2n\persistence\orm\store\operation\DetachOperation;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\TransactionEvent;
use n2n\persistence\orm\store\operation\RefreshOperation;
use n2n\persistence\orm\nql\NqlParser;
use n2n\persistence\Pdo;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\store\LoadingQueue;
use n2n\persistence\orm\criteria\item\CriteriaFunction;

class LazyEntityManager implements EntityManager {
	private $closed = false;
	private $pdo = null;
	private $pdoListener = null;
	private $dataSource;
	private $dbhPool;
	private $transactionalScoped = false;
	private $entityModelManager;
	private $persistenceContext;
	private $actionQueue;
	private $loadingQueue;
	private $nqlParser;
	/**
	 * @param string $dataSourceName
	 * @param PdoPool $dbhPool
	 */
	public function __construct($dataSourceName, PdoPool $dbhPool, $transactionalScoped) {
		$this->dataSourceName = $dataSourceName;
		$this->dbhPool = $dbhPool;
		$this->transactionalScoped = (boolean) $transactionalScoped;
		$this->entityModelManager = $dbhPool->getEntityModelManager();
		$this->persistenceContext = new PersistenceContext($dbhPool->getEntityProxyManager());
		$this->actionQueue = new ActionQueueImpl($this, $dbhPool->getMagicContext());
		$this->loadingQueue = new LoadingQueue($this->persistenceContext, $this->actionQueue);
		$this->nqlParser = new NqlParser($this, $this->entityModelManager);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\EntityManager::getEntityModelManager()
	 */
	public function getEntityModelManager() {
		$this->ensureEntityManagerOpen();
		return $this->entityModelManager;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\EntityManager::getPersistenceContext()
	 */
	public function getPersistenceContext() {
		$this->ensureEntityManagerOpen();
		return $this->persistenceContext;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\EntityManager::getActionQueue()
	 */
	public function getActionQueue() {
		$this->ensureEntityManagerOpen();
		return $this->actionQueue;
	}
	
	public function getLoadingQueue() {
		$this->ensureEntityManagerOpen();
		return $this->loadingQueue;
	}
	
	public function getMagicContext() {
		return $this->dbhPool->getMagicContext();
	}
	/**
	 *
	 * @return \n2n\persistence\Pdo
	 */
	public function getPdo() {
		if ($this->pdo !== null) {
			return $this->pdo;
		}
		
		$pdo = $this->dbhPool->getPdo($this->dataSourceName);
		$this->bindPdo($pdo);		
		return $pdo;
	}
	/**
	 * @param Pdo $pdo
	 * @throws IllegalStateException
	 */
	public function bindPdo(Pdo $pdo) {
		$this->ensureEntityManagerOpen();
		
		if ($this->pdo !== null) {
			throw new IllegalStateException('Pdo already bound.');
		}
		
		$this->pdo = $pdo;
		$that = $this;
		$this->pdoListener = new ClosurePdoListener(function (TransactionEvent $e) use ($that) {
			$transaction = $e->getTransaction();
			$type = $e->getType();
			
			if ($type == TransactionEvent::TYPE_ON_COMMIT && $that->isOpen()
					&& ($transaction === null || !$transaction->isReadOnly())) {
				$that->flush();
				$that->actionQueue->commit();
			}
				
			if ($that->transactionalScoped
					&& ($type == TransactionEvent::TYPE_ON_COMMIT || $type == TransactionEvent::TYPE_ON_ROLL_BACK)) {
				$that->close();
			}
		});
		$pdo->registerListener($this->pdoListener);
	}
	
	/**
	 *
	 * @param \ReflectionClass $class
	 * @param string $entityAlias
	 * @return \n2n\persistence\orm\criteria\BaseCriteria
	 */
	public function createCriteria() {
		$this->ensureEntityManagerOpen();
		return new BaseCriteria($this);
	}
	
	/**
	 *
	 * @param \ReflectionClass $class
	 * @param array $matches
	 * @param array $order
	 * @param int $limit
	 * @param int $num
	 * @return \n2n\persistence\orm\criteria\BaseCriteria
	 */
	public function createSimpleCriteria(\ReflectionClass $class, array $matches = null, array $order = null, 
			$limit = null, $num = null) {
		$this->ensureEntityManagerOpen();
			
		$criteria = $this->createCriteria();
		$criteria->select(self::SIMPLE_ALIAS);
		$criteria->from($class, self::SIMPLE_ALIAS);

		$whereSelector = $criteria->where();
		foreach ((array) $matches as $propertyExpression => $constant) {
			if ($constant instanceof CriteriaProperty || $constant instanceof CriteriaFunction) {
				$constant = $this->preCriteriaItem($constant);
			}

			$whereSelector->match(
					$this->preCriteriaItem(CrIt::pf($propertyExpression)),
					CriteriaComparator::OPERATOR_EQUAL, $constant);
		}

		foreach ((array) $order as $propertyExpression => $direction) {
			$criteria->order($this->preCriteriaItem(CrIt::pf($propertyExpression)), $direction);
		}
	
		$criteria->limit($limit, $num);

		return $criteria;
	}
	
	private function preCriteriaItem($criteriaItem) {
		if ($criteriaItem instanceof CriteriaProperty) {
			return $criteriaItem->prep(self::SIMPLE_ALIAS);
		}
		
		if (!($criteriaItem instanceof CriteriaFunction)) {
			return $criteriaItem;
		}
		
		$newParameters = array();
		foreach ($criteriaItem->getParameters() as $parameter) {
			$newParameters[] = $this->preCriteriaItem($parameter);
		}
		return new CriteriaFunction($criteriaItem->getName(), $newParameters);
	}
	
	public function createNqlCriteria($nql, array $params = array()) {
		$this->ensureEntityManagerOpen();
		
		return $this->nqlParser->parse($nql, $params);
	}
	
	public function find(\ReflectionClass $class, $id) {
		$this->ensureEntityManagerOpen();
		
		if ($id === null) return null;
		
		$entityModel = $this->entityModelManager->getEntityModelByClass($class);
		
		if (null !== ($entity = $this->persistenceContext->getManagedEntityObj($entityModel, $id))) {
			return $entity;
		}
		
		return $this->createSimpleCriteria($class, array($entityModel->getIdDef()->getPropertyName() => $id))
				->toQuery()->fetchSingle();
	}
	
	public function getReference(\ReflectionClass $class, $id) {
		return $this->getPersistenceContext()->getOrCreateEntityProxy(
				EntityModelManager::getInstance()->getEntityModelByClass($class), $id);
	}
	
	private function ensureEntityManagerOpen() {
		if (!$this->closed) return;
		
		throw new IllegalStateException('EntityManager closed');
	}
	
	private function ensureTransactionOpen($operationName) {
		$this->ensureEntityManagerOpen();
		
		$pdo = $this->getPdo();
		
		if (!$pdo->inTransaction()) {
			throw new TransactionRequiredException($operationName 
					. ' operation requires transaction.');
		}
		
		$transactionManager = $pdo->getTransactionManager();
		if ($transactionManager === null) return;
		
		if ($transactionManager->isReadyOnly()) {
			throw new IllegalStateException($operationName 
					. ' operation disallowed in ready only transaction.');
		}
	}
	
	public function merge($entity) {
		$this->ensureTransactionOpen('Merge');
		
		$mergeOperation = new MergeOperationImpl($this->actionQueue);
		return $mergeOperation->mergeEntity($entity);
	}
	
	public function persist($entity) {
		$this->ensureTransactionOpen('Persist');
		
		$persitOperation = new PersistOperation($this->actionQueue);
		$persitOperation->cascade($entity);
	}
	
	public function refresh($entity) {
		$this->ensureEntityManagerOpen();
		
		$refreshOperation = new RefreshOperation($this);
		$refreshOperation->cascade($entity);
	}
	
	public function remove($entity) {
		$this->ensureTransactionOpen('Remove');
		
		$removeOperation = new RemoveOperation($this->actionQueue);
		$removeOperation->cascade($entity);
	}

	public function detach($entity) {
		$this->ensureEntityManagerOpen();
			
		$removeOperation = new DetachOperation($this->actionQueue);
		$removeOperation->cascade($entity);
	}
	
	public function swap($entity, $newEntity) {
		$this->ensureTransactionOpen('Swap');
		
		$tcaq = $this->getPersistenceContext()->createTypeChangeActionQueue();
		$tcaq->initialize($entity, $newEntity);
		$tcaq->activate();
	}
	
	public function flush() {
		$this->ensureTransactionOpen('Flush');
		
		$persistOperation = new PersistOperation($this->actionQueue);
		foreach ($this->persistenceContext->getManagedEntityObjs() as $entity) {
			$persistOperation->cascade($entity);
		}
		
		$this->actionQueue->flush();
	}
	
	public function close() {
		if ($this->closed) return;
		
		if ($this->pdo !== null) {
			$this->pdo->unregisterListener($this->pdoListener);		
		}
		
		$this->clear();
		
		$this->closed = true;
		$this->pdo = null;
		$this->persistenceContext = null;
		$this->actionQueue = null;
		$this->loadingQueue = null;
		$this->entityModelManager = null;
		$this->nqlParser = null;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\EntityManager::contains()
	 */
	public function contains($object) {
		$this->ensureEntityManagerOpen();
		
		$this->getPersistenceContext()->containsManagedEntityObj($object);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\EntityManager::clear()
	 */
	public function clear() {
		$this->ensureEntityManagerOpen();
		
		$this->persistenceContext->clear();
		$this->actionQueue->clear();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\EntityManager::isOpen()
	 */
	public function isOpen() {
		return !$this->closed;
	}
	
	public function registerLifecycleListener(LifecycleListener $listener) {
		$this->ensureEntityManagerOpen();
		
		$this->actionQueue->registerLifecycleListener($listener);
	}
	
	public function getScope(): string {
		return $this->transactionalScoped ? self::SCOPE_TRANSACTION : self::SCOPE_EXTENDED;
	}
}

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
// namespace n2n\persistence\orm;

// use n2n\persistence\PdoListener;
// use n2n\core\container\PdoPool;
// use n2n\core\container\TransactionManager;
// use n2n\persistence\TransactionEvent;
// use n2n\core\container\ContainerConflictException;
// use n2n\core\SysTextUtils;
// use n2n\persistence\orm\criteria\Criteria;
// use n2n\persistence\orm\criteria\item\CriteriaItem;
// use n2n\persistence\orm\criteria\compare\CriteriaComparator;
// use n2n\persistence\orm\store\PersistenceContextImpl;

// class LazyEntityManager, PdoListener {
// 	private $closed = false;
// 	private $dbh = null;
// 	private $dbhPool;
// 	private $transactionalContext;
// 	private $persistenceContext;
	
// 	public function __construct($dataSourceName, PdoPool $dbhPool, TransactionManager $transactionalContext) {
// 		$this->dataSourceName = $dataSourceName;
// 		$this->dbhPool = $dbhPool;
// 		$this->transactionalContext = $transactionalContext;
// 	}
	
// 	public function onTransactionEvent(TransactionEvent $e) {
// 		if ($this->transactionalContext->isReadyOnly()) {
// 			return;
// 		}
		
// 		switch ($e->getType()) {
// 			case TransactionEvent::TYPE_ON_COMMIT:
// 				$this->getPersistenceContext()->searchAndSaveChanges();
// 				$this->getPersistenceContext()->flushBuffer();
// 				break;
// 			case TransactionEvent::TYPE_ROLLED_BACK:
// 				$this->getPersistenceContext()->clearBuffer();
// 		}
// 	}
	
// 	private function checkIfNotReadOnly() {
// 		if ($this->transactionalContext->isReadyOnly()) {
// 			throw new ContainerConflictException(
// 					SysTextUtils::get('n2n_error_core_container_read_only_transaction_active'));
// 		}
// 	}
// 	/**
// 	 *
// 	 * @return \n2n\persistence\Pdo
// 	 */
// 	public function getPdo() {
// 		if (!isset($this->dbh)) {
// 			$this->dbh = $this->dbhPool->getPdo($this->dataSourceName);
// 			$this->dbh->registerListener($this);
// 		}
	
// 		return $this->dbh;
// 	}
	
// 	public function getPersistenceContext() {
// 		if (!isset($this->persistenceContext)) {
// 			$this->persistenceContext = new PersistenceContextImpl($this);
// 		}
	
// 		return $this->persistenceContext;
// 	}
// 	/**
// 	 *
// 	 * @param \ReflectionClass $class
// 	 * @param string $entityAlias
// 	 * @return \n2n\persistence\orm\criteria\Criteria
// 	 */
// 	public function createCriteria(\ReflectionClass $class, $entityAlias) {
// 		return new Criteria($this->getPersistenceContext(), $class, $entityAlias);
// 	}
// 	/**
// 	 *
// 	 * @param \ReflectionClass $class
// 	 * @param array $matches
// 	 * @param array $order
// 	 * @param int $limit
// 	 * @param int $num
// 	 * @return \n2n\persistence\orm\criteria\Criteria
// 	 */
// 	public function createSimpleCriteria(\ReflectionClass $class, array $matches = null, array $order = null, $limit = null, $num = null) {
// 		$criteria = $this->createCriteria($class, self::SIMPLE_CRITERIA_ENTITY_ALIAS);
	
// 		$whereSelector = $criteria->where();
// 		foreach ((array) $matches as $propertyExpression => $constant) {
// 			$whereSelector->match(CriteriaItem::createFromExpression($propertyExpression, self::SIMPLE_CRITERIA_ENTITY_ALIAS),
// 					CriteriaComparator::OPERATOR_EQUAL, $constant);
// 		}
	
// 		foreach ((array) $order as $propertyExpression => $direction) {
// 			$criteria->order(CriteriaItem::createFromExpression($propertyExpression, self::SIMPLE_CRITERIA_ENTITY_ALIAS), $direction);
// 		}
	
// 		$criteria->limit($limit, $num);
	
// 		return $criteria;
// 	}
	
// 	public function find(\ReflectionClass $class, $id) {
// 		return $this->getPersistenceContext()->getOrLookupEntity(
// 				EntityModelManager::getInstance()->getEntityModelByClass($class), $id);
// 	}
	
// 	public function getReference(\ReflectionClass $class, $id) {
// 		return $this->getPersistenceContext()->getOrCreateEntityProxy(
// 				EntityModelManager::getInstance()->getEntityModelByClass($class), $id);
// 	}
	
// 	public function merge($entity) {
// 		$this->checkIfNotReadOnly();
// 		$actionQueue = $this->getPersistenceContext()->createPeristenceActionQueue(true);
// 		return $actionQueue->initialize($entity);
// 	}
	
// 	public function persist($entity) {
// 		$this->checkIfNotReadOnly();
		
// 		$actionQueue = $this->getPersistenceContext()->createPeristenceActionQueue(false);
// 		$actionQueue->initialize($entity);
// 	}
	
// 	public function refresh(Entity $object) {
// 		$this->getPersistenceContext()->refreshEntity($object);
// 	}
	
// 	public function reset(Entity $object) {
// 		$this->getPersistenceContext()->resetObject($object);
// 	}
	
// 	public function remove($entity) {
// 		$this->checkIfNotReadOnly();
		
// 		$actionQueue = $this->getPersistenceContext()->createRemoveActionQueue();
// 		$actionQueue->getOrCreateRemoveAction($entity);
// 	}
	
// 	public function swap($entity, Entity $newEntity) {
// 		$tcaq = $this->getPersistenceContext()->createTypeChangeActionQueue();
// 		$tcaq->initialize($entity, $newEntity);
// 		$tcaq->activate();
// 	}
	
// 	public function flush() {
// 		$this->checkIfNotReadOnly();
// 		if ($this->getPdo()->inTransaction()) {
// 			$this->getPersistenceContext()->searchAndSaveChanges();
// 		}
// 		$this->getPersistenceContext()->flushBuffer();
// 	}
	
// 	public function close() {
// 		if (isset($this->dbh)) {
// 			$this->dbh->unregisterListener($this);		
// 		}
		
// 		$this->closed = false;
// 		$this->dbh = null;
// 		$this->persistenceContext = null;
		
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\EntityManager::detach()
// 	 */
// 	public function detach(Entity $object) {
// 		$this->getPersistenceContext()->detachObject($object);
		
// 	}

// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\EntityManager::contains()
// 	 */
// 	public function contains(Entity $object) {
// 		$this->getPersistenceContext()->containsObject($object);
// 	}

// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\EntityManager::clear()
// 	 */
// 	public function clear() {
// 		$this->getPersistenceContext()->clear();
// 	}

// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\EntityManager::isOpen()
// 	 */
// 	public function isOpen() {
// 		$this->persistenceContext->clear();
// 	}

// }

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
namespace n2n\persistence\orm\store\action;

use n2n\persistence\orm\proxy\EntityProxy;
use n2n\persistence\orm\store\EntityInfo;
use n2n\persistence\orm\store\PersistenceOperationException;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use n2n\persistence\orm\LifecycleEvent;
use n2n\persistence\orm\store\action\supply\RemoveSupplyJob;

class RemoveActionPool {
	private $actionQueue;
	private $removeActions = array();
	private $unsuppliedRemoveActions = array();
	private $removeSupplyJobs = array();
	private $frozen = false;
	
	public function __construct(ActionQueue $actionQueue) {
		$this->actionQueue = $actionQueue;
	}
	
	public function clear() {
		foreach ($this->removeActions as $action) {
			$this->actionQueue->remove($action);
		}
		
		$this->frozen = false;
		$this->removeActions = array();
		$this->unsuppliedRemoveActions = array();
		$this->removeSupplyJobs = array();
	}

	public function containsAction($entity) {
		ArgUtils::assertTrue(is_object($entity));
		return isset($this->removeActions[spl_object_hash($entity)]);
	}
	
	public function getAction($entity) {
		ArgUtils::assertTrue(is_object($entity));
		$objHash = spl_object_hash($entity);
		if (isset($this->removeActions[$objHash])) {
			return $this->removeActions[$objHash];
		}
	
		throw new \InvalidArgumentException('No RemoveAction available for passed entity.');
	}
	

	public function getOrCreateAction($entity) {
		$objHash = spl_object_hash($entity);
		if (isset($this->removeActions[$objHash])) {
			return $this->removeActions[$objHash];
		}

		IllegalStateException::assertTrue(!$this->frozen);
				
		$removeAction = $this->createRemoveAction($entity);
		if ($removeAction === null) {
			return null;
		}
		
		$this->removeActions[$objHash] = $removeAction;
		$this->unsuppliedRemoveActions[$objHash] = $removeAction;
		$this->actionQueue->add($removeAction);
		
		$this->actionQueue->announceLifecycleEvent(new LifecycleEvent(LifecycleEvent::PRE_REMOVE, $entity,
				$removeAction->getEntityModel(), $removeAction->getId()));
		
		$this->actionQueue->getEntityManager()->getPersistenceContext()->removeEntityObj($entity);
		
		$that = $this;
		$removeAction->executeAtEnd(function () use ($that, $removeAction) {
			$that->actionQueue->announceLifecycleEvent(new LifecycleEvent(LifecycleEvent::POST_REMOVE,
					$removeAction->getEntityObj(), $removeAction->getEntityModel(), $removeAction->getId()));
		});

		return $removeAction;
	}
	
	private function createRemoveAction($entity) {
		$em = $this->actionQueue->getEntityManager();
		if ($entity instanceof EntityProxy) {
			$em->getPersistenceContext()->getEntityProxyManager()
					->initializeProxy($entity);
		}

		$entityInfo = $em->getPersistenceContext()->getEntityInfo($entity, 
				$em->getEntityModelManager());
		
		switch ($entityInfo->getState()) {
			case EntityInfo::STATE_MANAGED:
				break;
			case EntityInfo::STATE_REMOVED:
				return null;
			default:
				throw new PersistenceOperationException('Unable to remove ' . $entityInfo->getState() 
						. ' entity: ' . $entityInfo->toEntityString());
		}

		$entityModel = $entityInfo->getEntityModel();
		$id = $entityInfo->getId();

		if (!$entityInfo->hasId()) {
			throw new IllegalStateException('Unable to remove entity with unkown id: '
					. $entityInfo->toEntityString());
		}

		$persistenceContext = $em->getPersistenceContext();
		$oldValueHashCol = $persistenceContext->getValueHashColByEntityObj($entity);
		IllegalStateException::assertTrue($oldValueHashCol !== null);

		$actionMeta = $entityModel->createActionMeta();
		$actionMeta->setIdRawValue($entityModel->getIdDef()->getEntityProperty()
				->buildRaw($entityInfo->getId(), $this->actionQueue->getEntityManager()->getPdo()));
		
		return new RemoveActionImpl($this->actionQueue, $entityModel, $id, $entity,
				$actionMeta, $oldValueHashCol);		
	}
	
	public function removeAction($entity) {
		ArgUtils::assertTrue(is_object($entity));
		
		$objHash = spl_object_hash($entity);
		if (!isset($this->removeActions[$objHash])) return;
	
		IllegalStateException::assertTrue(!$this->frozen);
		
		$this->removeActions[$objHash]->disable();
		$this->actionQueue->remove($this->removeActions[$objHash]);
		unset($this->removeActions[$objHash]);
		unset($this->unsuppliedRemoveActions[$objHash]);
	}
	
	public function supply() {
		IllegalStateException::assertTrue($this->frozen);
		
		foreach ($this->removeSupplyJobs as $supplyJob) {
			$supplyJob->execute();
		}
	}	

	public function isFrozend() {
		return $this->frozen;
	}
	
	public function freeze() {
		IllegalStateException::assertTrue(!$this->frozen && empty($this->unsuppliedPersistActions));
	
		$this->frozen = true;
		
		foreach ($this->removeSupplyJobs as $removeSupplyJob) {
			$removeSupplyJob->init();
		}
	}

	public function prepareSupplyJobs() {
		IllegalStateException::assertTrue(!$this->frozen);

		if (empty($this->unsuppliedRemoveActions)) return false;
		
		while (null !== ($removeAction = array_pop($this->unsuppliedRemoveActions))) {
			if ($removeAction->isDisabled()) continue;
			
			$this->removeSupplyJobs[] = $this->createRemoveSupplyJob($removeAction);
		}
		
		return true;
	}
	
	private function createRemoveSupplyJob(RemoveActionImpl $removeAction) {
		$entityModel = $removeAction->getEntityModel();
		$entity = $removeAction->getEntityObj();

		$oldValueHashCol = $this->actionQueue->getEntityManager()->getPersistenceContext()
				->getValueHashColByEntityObj($entity);
		$values = array();
		foreach ($entityModel->getEntityProperties() as $entityProperty) {
			$values[$entityProperty->toPropertyString()] = $entityProperty->readValue($entity);
		}
		
		$supplyJob = new RemoveSupplyJob($removeAction, $oldValueHashCol);
		$supplyJob->setValues($values);
		
		$supplyJob->prepare();
		
		return $supplyJob;
	}
}

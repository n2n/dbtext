<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\veto;

use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\LifecycleListener;
use n2n\persistence\orm\LifecycleEvent;
use rocket\spec\Spec;
use rocket\ei\manage\EiObject;
use n2n\util\ex\NotYetImplementedException;
use rocket\ei\manage\LiveEiObject;
use rocket\core\model\launch\TransactionApproveAttempt;
use rocket\ei\manage\draft\DraftManager;
use n2n\core\container\N2nContext;
use rocket\ei\EiType;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\model\EntityPropertyCollection;
use n2n\persistence\orm\model\EntityModel;

class EiLifecycleMonitor implements LifecycleListener {
	private $spec;
	private $em;
	private $n2nContext;
	/**
	 * @var VetoableLifecycleAction[]
	 */
	private $persistActions = array();
	/**
	 * @var VetoableLifecycleAction[]
	 */
	private $updateActions = array();
	/**
	 * @var VetoableLifecycleAction[]
	 */
	private $removeActions = array();
	
// 	private $unmangedRemovedEntityObjs = array();
	private $uninitializedActions = array();
	
	public function __construct(Spec $spec) {
		$this->spec = $spec;
	}
	
	/**
	 * @return \n2n\persistence\orm\EntityManager
	 */
	public function getEntityManager() {
		return $this->em;
	}
	
	/**
	 * @return VetoableLifecycleAction[]
	 */
	public function getPersistActions() {
		return $this->persistActions;
	}
	
	/**
	 * @return VetoableLifecycleAction[]
	 */
	public function getUpdateActions() {
		return $this->updateActions;
	}
	
	/**
	 * @return VetoableLifecycleAction[]
	 */
	public function getRemoveActions() {
		return $this->removeActions;
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function containsEiObject(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			throw new NotYetImplementedException();
		}
		
		return $this->containsEntityObj($eiObject->getEiEntityObj()->getEntityObj());
	}
	
	/**
	 * @param object $entityObj
	 * @return boolean
	 */
	public function containsEntityObj($entityObj) {
		$objHash = spl_object_hash($entityObj);
		
		return isset($this->persistActions[$objHash]) || isset($this->updateActions[$objHash]) 
			|| isset($this->removeActions[$objHash]);
	}
	
	public function isEntityObjRemoved($entityObj) {
		$objHash = spl_object_hash($entityObj);
		
		return isset($this->removeActions[$objHash]);
	}
	
	/**
	 * @param EntityManager $em
	 * @param DraftManager $draftManager
	 */
	public function initialize(EntityManager $em, DraftManager $draftManager, N2nContext $n2nContext) {
		$this->em = $em;
		$this->n2nContext = $n2nContext;
		
		$persistenceContext = $this->em->getPersistenceContext();
		foreach ($persistenceContext->getRemovedEntityObjs() as $entityObj) {
			$this->remove($persistenceContext->getEntityModelByEntityObj($entityObj), $entityObj);
		}
		
		$this->em->getActionQueue()->registerLifecycleListener($this);
	}
	
	public function onPreFinalized($em) {
		/**
		 * @var VetoableLifecycleAction $action
		 */
		$action = null;
		while (null !== ($action = array_pop($this->uninitializedActions))) {
			$eiEntityObj = $action->getEiObject()->getEiEntityObj();
// 			if (!$eiEntityObj->hasId()) {
// 				$eiEntityObj->refreshId();
// 				if ($eiEntityObj->hasId()) {
// 					$eiEntityObj->setPersistent(true);
// 				}
// 			}
			$eiEntityObj->getEiType()->validateLifecycleAction($action, $this->n2nContext);
			
			if (!$action->hasVeto()) {
				$action->approve();
			}
		}
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @return \rocket\core\model\launch\TransactionApproveAttempt
	 */
	public function approve() {
		$this->em->flush();
		
		$reasonMessages = array();
		foreach ($this->persistActions as $action) {
			if (!$action->hasVeto()) continue;
			
			$reasonMessages[] = $action->getReasonMessage();
		}
		foreach ($this->updateActions as $action) {
			if (!$action->hasVeto()) continue;
			
			$reasonMessages[] = $action->getReasonMessage();
		}
		foreach ($this->removeActions as $action) {
			if (!$action->hasVeto()) continue;
			
			$reasonMessages[] = $action->getReasonMessage();
		}
		
		return new TransactionApproveAttempt($reasonMessages);
	}
	
	public function onLifecycleEvent(LifecycleEvent $e, EntityManager $em) {
		$entityModel = $e->getEntityModel();
		
		if (!$this->spec->containsEiTypeClass($entityModel->getClass())) {
			return;
		}
		
		$eiType = $this->spec->getEiTypeByClass($entityModel->getClass());
		
		switch ($e->getType()) {
			case LifecycleEvent::PRE_REMOVE:
				$this->remove($eiType, $e->getEntityObj());
				break;
			case LifecycleEvent::PRE_PERSIST:
				$this->persist($eiType, $e->getEntityObj());
				break;
			case LifecycleEvent::PRE_UPDATE:
				$this->update($eiType, $e->getEntityObj());
				break;
			case LifecycleEvent::POST_PERSIST:
				$this->postPersist($eiType, $e->getEntityObj());
				break;
		}
	}
	
	public function removeEiObject(EiObject $eiObject) {
		if ($eiObject->isDraft()) {
			throw new NotYetImplementedException();
		}
		
		$objHash = spl_object_hash($eiObject->getEiEntityObj()->getEntityObj());
		if (isset($this->removeActions[$objHash])) return;
		
		$vla = new VetoableLifecycleAction($eiObject, $this, VetoableLifecycleAction::TYPE_REMOVE);
		$this->removeActions[$objHash] = $vla;
		$this->uninitializedActions[$objHash] = $vla;
		
		unset($this->persistActions[$objHash]);
		unset($this->updateActions[$objHash]);
		
// 		$entityModel = $eiObject->getEiEntityObj()->getEiType()->getEntityModel();
// 		foreach ($this->em->getEntityModelManager()->getRegisteredClassNames() as $className) {
// 			if (!$this->spec->containsEiTypeClassName($className)) {
// 				continue;
// 			}
			
// 			$this->recCheck($this->em->getEntityModelManager()->getEntityModelByClass($class), $entityModel);
// 		}
	}
	
// 	private function recCheck(EntityPropertyCollection $entityPropertyCollection, EntityModel $targetEntityModel) {
// 		foreach ($entityPropertyCollection->getEntityProperties() as $entityProperty) {
			
// 			if ($entityProperty->hasTargetEntityModel()) {
// 				$entityProperty->getTargetEntityModel()->equals($entityModel);
				
// 			}
// 		}
// 	}
	
	private function remove(EiType $eiType, $entityObj) {
		$this->removeEiObject(LiveEiObject::create($eiType, $entityObj));
	}
	
	private function persist(EiType $eiType, $entityObj) {
		$objHash = spl_object_hash($entityObj);
		
		if (isset($this->persistActions[$objHash])) return;
		
		$vla = new VetoableLifecycleAction(LiveEiObject::create($eiType, $entityObj), $this,
				VetoableLifecycleAction::TYPE_PERSIST);
		$this->persistActions[$objHash] = $vla;
		$this->uninitializedActions[$objHash] = $vla;
		
		unset($this->removeActions[$objHash]);
		IllegalStateException::assertTrue(!isset($this->updateActions[$objHash]));
	}
	
	private function postPersist(EiType $eiType, $entityObj) {
		$objHash = spl_object_hash($entityObj);
		
		if (!isset($this->persistActions[$objHash])) {
			return;
		}
		
		$eiEntityObj = $this->persistActions[$objHash]->getEiObject()->getEiEntityObj();
		if (!$eiEntityObj->hasId()) {
			$eiEntityObj->refreshId();
			if ($eiEntityObj->hasId()) {
				$eiEntityObj->setPersistent(true);
			}
		}
	}
	
	private function update(EiType $eiType, $entityObj) {
		$objHash = spl_object_hash($entityObj);
		
		if (isset($this->updateActions[$objHash]) || isset($this->persistActions[$objHash])) {
			return;
		}
		
		$vla = new VetoableLifecycleAction(LiveEiObject::create($eiType, $entityObj), $this,
				VetoableLifecycleAction::TYPE_UPDATE);
		$this->updateActions[$objHash] = $vla; 
		$this->uninitializedActions[$objHash] = $vla;
		
		IllegalStateException::assertTrue(!isset($this->removeActions[$objHash]));
	}
}

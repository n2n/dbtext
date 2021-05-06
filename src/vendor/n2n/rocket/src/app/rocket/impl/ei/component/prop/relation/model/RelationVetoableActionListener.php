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
namespace rocket\impl\ei\component\prop\relation\model;

use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\util\type\CastUtils;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\LiveEiObject;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\ei\manage\EiEntityObj;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\veto\VetoableLifecycleAction;
use n2n\core\container\N2nContext;
use rocket\ei\EiLifecycleListener;
use n2n\l10n\Message;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use n2n\util\col\ArrayUtils;

class RelationVetoableActionListener implements EiLifecycleListener {
	const STRATEGY_PREVENT = 'prevent';
	const STRATEGY_UNSET = 'unset';
	const STRATEGY_SELF_REMOVE = 'selfRemove';
	
	private $relationModel;
	private $strategy = true;
	
	public function __construct(RelationModel $relationModel, string $strategy) {
		$this->relationModel = $relationModel;
		$this->strategy = $strategy;
	}
	
	public function onRemove(VetoableLifecycleAction $vetoableRemoveAction, N2nContext $n2nContext) {
		$eiObject = $vetoableRemoveAction->getEiObject();
		if ($eiObject->isDraft()) return;
				
		$vetoCheck = new VetoCheck($this->relationModel, $eiObject->getEiEntityObj(), $vetoableRemoveAction, 
				$n2nContext);
		
		switch ($this->strategy) {
			case self::STRATEGY_PREVENT:
				$vetoCheck->prevent();
				break;
			case self::STRATEGY_UNSET:
				$vetoCheck->release();
				break;
			case self::STRATEGY_SELF_REMOVE:
				$vetoCheck->remove();
		}
	}
	
	public function onPersist(VetoableLifecycleAction $vetoableLifecycleAction, N2nContext $n2nContext) {
	}
	
	public function onUpdate(VetoableLifecycleAction $vetoableLifecycleAction, N2nContext $n2nContext) {
	}
	
	
	public static function getStrategies(): array {
		return array(self::STRATEGY_PREVENT, self::STRATEGY_UNSET, self::STRATEGY_SELF_REMOVE);
	}

}

class VetoCheck {
	private $relationEiProp;
	private $targetEiEntityObj;
	private $vetoableRemoveAction;
	private $n2nContext;
	
	public function __construct(RelationModel $relationEiProp, EiEntityObj $targetEiEntityObj, 
			VetoableLifecycleAction $vetoableRemoveAction, N2nContext $n2nContext) {
		$this->relationEiProp = $relationEiProp;
		$this->targetEiEntityObj = $targetEiEntityObj;
		$this->vetoableRemoveAction = $vetoableRemoveAction;
		$this->n2nContext = $n2nContext;
	}
	
	public function prevent() {
		$num = 0;
		$entityObj = null;
		$queue = $this->vetoableRemoveAction->getMonitor();
		foreach ($this->findAll() as $entityObj) {
			if (!$queue->isEntityObjRemoved($entityObj)
					&& $this->isStillAssigned($entityObj)) {
				$num++;
			}
		}
		
		if ($num === 0) return;
		
		$attrs = array('entry' => $this->createIdentityString($entityObj),
				'generic_label' => $this->getGenericLabel(), 
				'field' => $this->relationEiProp->getLabelLstr()->t($this->n2nContext->getN2nLocale()),
				'target_entry' => $this->createTargetIdentityString(),
				'target_generic_label' => $this->getTargetGenericLabel());
// 		$dtc = new DynamicTextCollection('rocket', N2nLocale::getAdmin());
		if ($num === 1) {
			$this->vetoableRemoveAction->prevent(Message::createCodeArg('ei_impl_relation_remove_veto_err', $attrs, null, 'rocket'));
		} else {
			$attrs['num_more'] = ($num - 1);
			$this->vetoableRemoveAction->prevent(Message::createCodeArg('ei_impl_relation_remove_veto_one_and_more_err', 
					$attrs, null, 'rocket'));
		}
	}
	
	public function release() {
		foreach ($this->findAll() as $entityObj) {
			if ($this->vetoableRemoveAction->getMonitor()->isEntityObjRemoved($entityObj)) continue;
			
			$that = $this;
			$this->vetoableRemoveAction->executeWhenApproved(function () use ($that, $entityObj) {
				$that->releaseEntityObj($entityObj);
			});
		}
	}
	
	public function remove() {
		$queue = $this->vetoableRemoveAction->getMonitor();
		foreach ($this->findAll() as $entityObj) {
			if ($queue->isEntityObjRemoved($entityObj)) continue;
				
			$this->vetoableRemoveAction->executeWhenApproved(function () use ($queue, $entityObj) {
				
				$queue->getEntityManager()->remove($entityObj);
// 				$queue->getEntityManager()->getActionQueue()->getOrCreateRemoveAction($entityObj);
// 				$queue->removeEiObject(LiveEiObject::create(
// 						$that->relationEiProp->getEiMask()->getEiType(), $entityObj));
			});
		}
	}

	private function findAll() {
		$criteria = $this->createCriteria()->select('eo');
		return $criteria->toQuery()->fetchArray();
	}
	
	private function getRelationEntityProperty(): RelationEntityProperty {
		$entityProperty = $this->relationEiProp->getRelationEntityProperty();
		CastUtils::assertTrue($entityProperty instanceof RelationEntityProperty);
		return $entityProperty;
	}
	
	
	private function createCriteria() {
		$entityProperty = $this->getRelationEntityProperty();
		$manageState = $this->n2nContext->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		$criteria = $manageState->getEntityManager()->createCriteria();
		
		$operator = ($this->isToOne() ? CriteriaComparator::OPERATOR_EQUAL : CriteriaComparator::OPERATOR_CONTAINS);
		
		$criteria
				->from($entityProperty->getEntityModel()->getClass(), 'eo')
				->where()->match(CrIt::p('eo', $entityProperty), $operator, 
						CrIt::c($this->targetEiEntityObj->getEntityObj()));
		return $criteria;
	}
		
	private function isToOne(): bool {
		$entityProperty = $this->getRelationEntityProperty();
		return $entityProperty->getType() == RelationEntityProperty::TYPE_MANY_TO_ONE 
				|| $entityProperty->getType() == RelationEntityProperty::TYPE_ONE_TO_ONE;
	}
	
	private function isStillAssigned($entityObj) {
		$objectPropertyAccessProxy = $this->relationEiProp->getObjectPropertyAccessProxy();
		
		$value = $objectPropertyAccessProxy->getValue($entityObj);
		if ($this->isToOne()) {
			return $value === $entityObj;
		}
		
		return ArrayUtils::isArrayLike($value) && ArrayUtils::isArrayLike($entityObj, $value);
	}
	
	private function releaseEntityObj($entityObj) {
		$objectPropertyAccessProxy = $this->relationEiProp->getObjectPropertyAccessProxy();
		
		if ($this->isToOne()) {
			$objectPropertyAccessProxy->setValue($entityObj, null);
			return;
		}
		
		$currentTargetEntityObjs = $objectPropertyAccessProxy->getValue($entityObj);
		if ($currentTargetEntityObjs === null) {
			$currentTargetEntityObjs = new \ArrayObject();
		}
		
		IllegalStateException::assertTrue($currentTargetEntityObjs instanceof \ArrayObject);
		
		$targetEntityObj = $this->targetEiEntityObj->getEntityObj();
		foreach ($currentTargetEntityObjs as $key => $currentTargetEntityObj) {
			if ($currentTargetEntityObj === $targetEntityObj) {
				$currentTargetEntityObjs->offsetUnset($key);
			}
		}
	}
	
	private function getGenericLabel(): string {
		return $this->relationEiProp->getEiMask()->getLabelLstr()->t($this->n2nContext->getN2nLocale());
	}
	
	private function createIdentityString($entityObj): string {
// 		$eiType = $this->relationEiProp->getEiMask()->getEiType();
// 		return $eiType->getEiTypeExtensionCollection()->getOrCreateDefault()->createIdentityString(
// 				LiveEiObject::create($eiType, $entityObj), $this->n2nContext->getN2nLocale());

		$eiMask = $this->relationEiProp->getEiMask();
		$manageState = $this->n2nContext->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		return $manageState->getDef()->getGuiDefinition($eiMask)
				->createIdentityString(LiveEiObject::create($eiMask->getEiType(), $entityObj),
						$this->n2nContext, $this->n2nContext->getN2nLocale());
	}
	
	private function getTargetGenericLabel(): string {
		return $this->relationEiProp->getEiPropRelation()->getTargetEiMask()->getLabelLstr()
				->t($this->n2nContext->getN2nLocale());
	}
	
	private function createTargetIdentityString() {
		$manageState = $this->n2nContext->lookup(ManageState::class);
		CastUtils::assertTrue($manageState instanceof ManageState);
		return $manageState->getDef()->getGuiDefinition($this->relationEiProp->getEiPropRelation()->getTargetEiMask())
				->createIdentityString(new LiveEiObject($this->targetEiEntityObj), $this->n2nContext, $this->n2nContext->getN2nLocale());
	}
}

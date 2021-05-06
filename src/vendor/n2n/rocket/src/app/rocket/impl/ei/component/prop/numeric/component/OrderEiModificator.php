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
namespace rocket\impl\ei\component\prop\numeric\component;

use rocket\impl\ei\component\modificator\adapter\EiModificatorAdapter;
use rocket\ei\manage\entry\OnWriteMappingListener;
use rocket\impl\ei\component\prop\numeric\OrderEiProp;
use rocket\ei\manage\critmod\sort\SortCriteriaConstraintGroup;
use rocket\ei\manage\critmod\sort\SimpleSortConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\util\Eiu;
use rocket\ei\manage\frame\Boundry;
use rocket\ei\EiPropPath;
use rocket\ei\util\entry\EiuObject;

class OrderEiModificator extends EiModificatorAdapter {
	private $eiProp;
	
	/**
	 * @param OrderEiProp $eiProp
	 */
	public function __construct(OrderEiProp $eiProp) {
		$this->eiProp = $eiProp;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\modificator\adapter\EiModificatorAdapter::setupEiFrame()
	 */
	public function setupEiFrame(Eiu $eiu) {
		$eiu->frame()->getEiFrame()->getBoundry()->addCriteriaConstraint(Boundry::TYPE_HARD_SORT,
				new SortCriteriaConstraintGroup(array(
						new SimpleSortConstraint(CrIt::p($this->eiProp->getEntityProperty()), 'ASC'))));
		
		$eiu->frame()->setSortAbility(
				function (array $eiuObjects, EiuObject $afterEiuObject) use ($eiu) {
					foreach ($eiuObjects as $eiuObject) {
						$this->move($eiu, $eiuObject->getPid(), $afterEiuObject->getPid(), false);
					}
				},
				function (array $eiuObjects, EiuObject $beforeEiuObject) use ($eiu) {
					foreach ($eiuObjects as $eiuObject) {
						$this->move($eiu, $eiuObject->getPid(), $beforeEiuObject->getPid(), true);
					}
				});
	}
				
	
	private function move(Eiu $eiu, string $pid, string $targetPid, bool $before) {
		if ($pid === $targetPid) return;
		
		$eiuEntry = $eiu->frame()->lookupEntry($pid);
		$targetEiuEntity = $eiu->frame()->lookupEntry($targetPid);
		
		if ($eiuEntry === null || $targetEiuEntity === null) {
			return;
		}
		
		$entityProperty = $this->eiProp->getEntityProperty();
		$targetOrderIndex = $entityProperty->readValue($targetEiuEntity->getEntityObj());
		if (!$before) {
			$targetOrderIndex++;
		}
		
		$em = $eiu->frame()->em();
		$criteria = $em->createCriteria();
		$criteria->select('eo')
				->from($entityProperty->getEntityModel()->getClass(), 'eo')
				->where()->match(CrIt::p('eo', $entityProperty), '>=', $targetOrderIndex)->endClause()
				->order(CrIt::p('eo', $entityProperty), 'ASC');
		
		$newOrderIndex = $targetOrderIndex + OrderEiProp::ORDER_INCREMENT;
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$newOrderIndex += OrderEiProp::ORDER_INCREMENT;
			$entityProperty->writeValue($entityObj, $newOrderIndex);
		}
		
		$entityProperty->writeValue($eiuEntry->getEntityObj(), $targetOrderIndex);
		$em->flush();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\modificator\adapter\EiModificatorAdapter::setupEiEntry()
	 */
	public function setupEiEntry(Eiu $eiu) {
		$ssm = $eiu->entry()->getEiEntry();
		$eiFrame = $eiu->frame()->getEiFrame();
		$eiProp = $this->eiProp;
		$ssm->registerListener(new OnWriteMappingListener(function() use ($eiFrame, $ssm, $eiProp) {
			$orderIndex = $ssm->getValue(EiPropPath::from($eiProp));
			
			if (mb_strlen($orderIndex)) return;
			
			$entityProperty = $eiProp->getEntityProperty();
			
			$em = $eiFrame->getManageState()->getEntityManager();
			$criteria = $em->createCriteria()
					->select(CrIt::f('MAX', CrIt::p('eo', $entityProperty)))
					->from($entityProperty->getEntityModel()->getClass(), 'eo');
			
			$ssm->setValue(EiPropPath::from($eiProp), $criteria->toQuery()->fetchSingle() + OrderEiProp::ORDER_INCREMENT);
		}));
	}
}

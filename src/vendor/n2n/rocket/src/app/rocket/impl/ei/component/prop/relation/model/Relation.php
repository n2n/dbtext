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

use rocket\ei\util\Eiu;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\entry\EiuObject;
use rocket\impl\ei\component\prop\relation\model\relation\MappedOneToCriteriaFactory;
use rocket\impl\ei\component\prop\relation\model\relation\RelationCriteriaFactory;
use rocket\impl\ei\component\prop\relation\model\relation\MappedRelationEiModificator;
use rocket\ei\util\entry\EiuEntry;
use rocket\impl\ei\component\prop\relation\model\relation\PlainMappedRelationEiModificator;
use rocket\impl\ei\component\prop\relation\model\relation\MasterRelationEiModificator;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ei\manage\frame\EiForkLink;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use rocket\impl\ei\component\prop\relation\model\relation\OneToManySelectCriteriaConstraint;
use rocket\impl\ei\component\prop\relation\model\relation\OneToOneSelectCriteriaConstraint;

class Relation {
	private $eiProp;
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	
	/**
	 * @param RelationModel $relationModel
	 */
	function __construct(RelationEiProp $eiProp, RelationModel $relationModel) {
		$this->eiProp = $eiProp;
		$this->relationModel = $relationModel;
	}
	
	/**
	 * @param Eiu $eiu
	 * @return Eiu
	 */
	function createForkEiFrame(Eiu $eiu, EiForkLink $eiForkLink) {
		$targetEiuFrame = $this->relationModel->getTargetEiuEngine()->newFrame($eiForkLink);
		
		
		if ($eiForkLink->getMode() == EiForkLink::MODE_SELECT && !$this->relationModel->isSourceMany()
				&& null !== ($eiuEntry = $eiu->entry(false)) && $this->relationModel->isFiltered()) {
			$this->applyOneToTargetSelectConstraints($targetEiuFrame, $eiuEntry);
		} else if ($eiForkLink->getMode() == EiForkLink::MODE_DISCOVER && null !== ($eiuObject = $eiu->object(false))) {
			$this->applyTargetCriteriaFactory($targetEiuFrame, $eiuObject);
		}
		
		if ($eiForkLink->getMode() != EiForkLink::MODE_SELECT && null !== ($eiuEntry = $eiu->entry(false))) {
			$this->applyTargetModificators($targetEiuFrame, $eiu->frame(), $eiuEntry);
		}
		
		return $targetEiuFrame->getEiFrame();
	}
	
	
	private function applyOneToTargetSelectConstraints(EiuFrame $targetEiuFrame, EiuEntry $eiuEntry) {
		$srcEntityObj = $eiuEntry->getEntityObj();
		$srcEntityProperty = $this->relationModel->getRelationEntityProperty();
		
		$criteriaConstraint = null;
		if ($this->relationModel->isTargetMany()) {
			$criteriaConstraint = new OneToManySelectCriteriaConstraint($srcEntityObj, $srcEntityProperty);
		} else {
			$criteriaConstraint = new OneToOneSelectCriteriaConstraint($srcEntityObj, $srcEntityProperty);
		}
		
		$targetEiuFrame->addCriteriaConstraint($criteriaConstraint);
	}
	
	/**
	 * @param EiuFrame $targetEiuFrame
	 * @param EiuObject $eiuObject
	 */
	private function applyTargetCriteriaFactory(EiuFrame $targetEiuFrame, EiuObject $eiuObject) {
		if ($eiuObject->isNew()) {
			return;
		}
		
		$relationEntityProperty = $this->relationModel->getRelationEntityProperty();
		
		if (!$relationEntityProperty->isMaster() && !$this->relationModel->isSourceMany()) {
			$targetEiuFrame->setCriteriaFactory(new MappedOneToCriteriaFactory(
					$this->relationModel->getRelationEntityProperty()->getRelation(),
					$eiuObject->getEntityObj()));
			return;
		}
		
		$targetEiuFrame->setCriteriaFactory(new RelationCriteriaFactory($relationEntityProperty, 
				$eiuObject->getEntityObj()));
	}
	
	/**
	 * @param EiuFrame $targetEiuFrame
	 * @param EiuFrame $eiuFrame
	 * @param EiuEntry $eiuEntry
	 */
	private function applyTargetModificators(EiuFrame $targetEiuFrame, EiuFrame $eiuFrame, EiuEntry $eiuEntry) {
		$targetEiFrame = $targetEiuFrame->getEiFrame();
		$targetPropInfo = $this->relationModel->getTargetPropInfo();
		
		if (null !== $targetPropInfo->eiPropPath) {
			$targetEiuFrame->setRelation($targetPropInfo->eiPropPath, $eiuFrame, $eiuEntry);
			
			if (!$eiuEntry->isDraft()) {
				$relationEiuEntry = $eiuEntry;
				$targetEiFrame->registerListener(new MappedRelationEiModificator($targetEiFrame,
						$relationEiuEntry, $targetPropInfo->eiPropPath, $this->relationModel->isSourceMany()));
			}
		} else if ($targetPropInfo->masterAccessProxy !== null) {
			$targetEiFrame->registerListener(
					new PlainMappedRelationEiModificator($targetEiFrame, $eiuEntry->getEntityObj(),
							$targetPropInfo->masterAccessProxy, $this->relationModel->isSourceMany()));
		}
		
		if ($this->relationModel->getRelationEntityProperty()->isMaster() && !$eiuEntry->isDraft()) {
			$targetEiFrame->registerListener(new MasterRelationEiModificator($targetEiFrame, $eiuEntry->getEntityObj(),
					$this->eiProp->getObjectPropertyAccessProxy(), $this->relationModel->isTargetMany()));
		}
	}	
}
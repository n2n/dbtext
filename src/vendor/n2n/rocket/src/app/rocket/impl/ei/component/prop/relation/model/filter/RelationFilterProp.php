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
namespace rocket\impl\ei\component\prop\relation\model\filter;

use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\ei\util\frame\EiuFrame;
use n2n\l10n\Lstr;
use n2n\persistence\orm\property\EntityProperty;
use rocket\ei\manage\critmod\filter\ComparatorConstraint;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\entry\UnknownEiObjectException;
use n2n\util\type\TypeConstraint;
use n2n\util\type\attrs\AttributesException;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\util\filter\controller\FilterJhtmlHook;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\critmod\filter\ComparatorConstraintGroup;
use rocket\ei\manage\frame\CriteriaConstraint;
use rocket\ei\manage\critmod\filter\impl\SimpleComparatorConstraint;
use rocket\ei\manage\entry\EiFieldConstraint;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use rocket\core\model\Rocket;

class RelationFilterProp implements FilterProp {
	protected $labelLstr;
	protected $entityProperty;
	protected $targetEiuFrame;
	protected $targetFilterDef;
	protected $targetSelectUrlCallback;
	
	public function __construct($labelLstr, EntityProperty $entityProperty, EiuFrame $targetEiuFrame, 
			TargetFilterDef $targetFilterDef) {
		$this->labelLstr = Lstr::create($labelLstr);
		$this->entityProperty = $entityProperty;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->targetFilterDef = $targetFilterDef;
	}
	
	public function setTargetSelectUrlCallback(\Closure $targetSelectUrlCallback) {
		$this->targetSelectUrlCallback = $targetSelectUrlCallback;
	}
	
	public function getLabel(): string {
		return (string) $this->labelLstr;
	}
	
	public function createComparatorConstraint(DataSet $dataSet): ComparatorConstraint {
		$relationFilterConf = new RelationFilterConf($dataSet);
		
		$operator = $relationFilterConf->getOperator();
		switch ($operator) {
			case CriteriaComparator::OPERATOR_IN:
			case CriteriaComparator::OPERATOR_NOT_IN:
				if ($this->entityProperty->isToMany()) break;
				
				return new SimpleComparatorConstraint(CrIt::p($this->entityProperty), $operator, 
						CrIt::c($this->lookupTargetEntityObjs($relationFilterConf->getTargetPids())));
			case CriteriaComparator::OPERATOR_CONTAINS:
			case CriteriaComparator::OPERATOR_CONTAINS_NOT:
				if (!$this->entityProperty->isToMany()) break;
				
				$group = new ComparatorConstraintGroup(true);
				foreach ($this->lookupTargetEntityObjs($relationFilterConf->getTargetPids()) as $targetEntityObj) {
					$group->addComparatorConstraint(new SimpleComparatorConstraint(CrIt::p($this->entityProperty), 
							$operator, CrIt::c($targetEntityObj)));
				}
				return $group;
			case CriteriaComparator::OPERATOR_EXISTS:
			case CriteriaComparator::OPERATOR_NOT_EXISTS:
				$targetComparatorConstraint = $this->targetFilterDef->getFilterDefinition()->createComparatorConstraint(
						$relationFilterConf->getTargetFilterSettingGroup());
				
				return new TestComparatorConstraint($this->entityProperty, $targetComparatorConstraint);
		}
	}
	
	private function lookupTargetEntityObjs(array $targetPids) {
		$targetEntityObjs = array();
		foreach ($targetPids as $targetPid) {
			try {
				$targetEntityObjs[] = $this->targetEiuFrame->lookupEntry($targetPid, CriteriaConstraint::ALL_TYPES)
						->getEiEntityObj();
			} catch (UnknownEiObjectException $e) { }
		}
		return $targetEntityObjs;
	}

	public function createMagDispatchable(DataSet $dataSet): MagDispatchable {
		$form = new RelationFilterMagForm($this->entityProperty->isToMany(), $this->targetEiuFrame, 
				$this->targetFilterDef->getFilterDefinition(), $this->targetFilterDef->getFilterJhtmlHook(), 
				$this->targetSelectUrlCallback);
		$relationFilterConf = new RelationFilterConf($dataSet);
		
		$form->getOperatorMag()->setValue($relationFilterConf->getOperator());
		
		if ($this->targetSelectUrlCallback !== null) {
			$targetLiveEntries = array();
			foreach ($relationFilterConf->getTargetPids() as $targetPid) {
				try {
					$targetLiveEntries[$targetPid] = $this->targetEiuFrame
							->lookupEntry($this->targetEiuFrame->pidToId($targetPid), CriteriaConstraint::ALL_TYPES)
							->getEiEntityObj();
				} catch (UnknownEiObjectException $e) {}
			}
			$form->getSelectorMag()->setTargetLiveEntries($targetLiveEntries);
		}
		
		$form->getFilterGroupMag()->setValue($relationFilterConf->getTargetFilterSettingGroup());
		
		return $form;
	}
	
	public function buildDataSet(MagDispatchable $form): DataSet {
		ArgUtils::assertTrue($form instanceof RelationFilterMagForm);
		
		$relationFilterConf = new RelationFilterConf(new DataSet());
		
		$relationFilterConf->setOperator($form->getOperatorMag()->getValue());
		
		$targetPids = array();
		foreach ($form->getTargetLiveEntries() as $targetEiEntityObj) {
			$targetPids[] = $this->targetEiuFrame->idToPid($targetEiEntityObj->getId());
		}
		$relationFilterConf->setTargetPids($targetPids);	
		
		return $relationFilterConf->getDataSet();
	}
	
	/**
	 * 
	 * @param DataSet $dataSet
	 * @return EiFieldConstraint
	 */
	public function createEiFieldConstraint(DataSet $dataSet) {
		$relationFilterConf = new RelationFilterConf(new DataSet());
		
		$operator = $relationFilterConf->getOperator();
		switch ($operator) {
			case CriteriaComparator::OPERATOR_IN:
			case CriteriaComparator::OPERATOR_NOT_IN:
				if ($this->entityProperty->isToMany()) break;
		
				return new SimpleComparatorConstraint(CrIt::p($this->entityProperty), $operator,
						CrIt::c($this->lookupTargetEntityObjs($relationFilterConf->getTargetPids())));
			case CriteriaComparator::OPERATOR_CONTAINS:
			case CriteriaComparator::OPERATOR_CONTAINS_NOT:
				if (!$this->entityProperty->isToMany()) break;
		
				$group = new ComparatorConstraintGroup(true);
				foreach ($this->lookupTargetEntityObjs($relationFilterConf->getTargetPids()) as $targetEntityObj) {
					$group->addComparatorConstraint(new SimpleComparatorConstraint(CrIt::p($this->entityProperty),
							$operator, CrIt::c($targetEntityObj)));
				}
				return $group;
			case CriteriaComparator::OPERATOR_EXISTS:
			case CriteriaComparator::OPERATOR_NOT_EXISTS:
				$targetComparatorConstraint = $this->targetFilterDef->getFilterDefinition()->createComparatorConstraint(
				$relationFilterConf->getTargetFilterSettingGroup());
		
				return new TestComparatorConstraint($this->entityProperty, $targetComparatorConstraint);
		}
	}
}

class RelationFilterConf {
	const OPERATOR_KEY = 'operator';
	const TARGET_ID_REPS = 'targetPids';
	const TARGET_FILTER_GROUP_ATTRS = 'targetFilterGroupAttrs';
	
	private $dataSet;
	
	public function __construct(DataSet $dataSet) {
		$this->dataSet = $dataSet;
	}
	
	public function getOperator() {
		return $this->dataSet->getString(self::OPERATOR_KEY, false);
	}
	
	public function setOperator(string $operator) {
		$this->dataSet->set(self::OPERATOR_KEY, $operator);
	}
	
	public function getTargetPids(): array {
		return $this->dataSet->getArray(self::TARGET_ID_REPS, false, array(), TypeConstraint::createSimple('string'));
	}
	
	public function setTargetPids(array $targetPids) {
		$this->dataSet->set(self::TARGET_ID_REPS, $targetPids);
	}
	
	public function getTargetFilterSettingGroup(): FilterSettingGroup {
		try {
			return FilterSettingGroup::create(new DataSet($this->dataSet
					->getArray(self::TARGET_FILTER_GROUP_ATTRS, false)));
		} catch (AttributesException $e) {
			return new FilterSettingGroup();
		}
	}
	
	public function setTargetFilterSettingGroup(FilterSettingGroup $targetFilterSettingGroup) {
		$this->dataSet->set(self::TARGET_FILTER_GROUP_ATTRS, $targetFilterSettingGroup->toAttrs());
	}
}


class RelationFilterMagForm extends MagForm {
	private $toMany;
	private $operatorMag;
	private $selectorMag;
	private $filterGroupMag;
	
	public function __construct(bool $toMany, EiuFrame $targetEiuFrame, FilterDefinition $targetFilterDefinition, 
			FilterJhtmlHook $filterJhtmlHook, \Closure $targetSelectUrlCallback = null) {
		$this->toMany = $toMany;
				
		if ($targetSelectUrlCallback !== null) {
			$this->selectorMag = new RelationSelectorMag('selector', $targetEiuFrame, 
					$targetSelectUrlCallback);
		}
		$this->filterGroupMag = new RelationFilterGroupMag($targetFilterDefinition, $filterJhtmlHook);
		$this->operatorMag = new EnumMag('Operator', $this->buildOperatorOptions(), null, true);
		
		$magCollection = new MagCollection();
		$magCollection->addMag('operator', $this->operatorMag);
		
		if (null !== $this->selectorMag) {
			$magCollection->addMag('selector', $this->selectorMag);
		}
		
		$magCollection->addMag('filterGroup', $this->filterGroupMag);
		
		parent::__construct($magCollection);
	}
	
	public function getOperatorMag(): EnumMag {
		return $this->operatorMag;
	}
	
	public function getSelectorMag(): RelationSelectorMag{
		return $this->selectorMag;
	}
	
	public function getFilterGroupMag(): RelationFilterGroupMag {
		return $this->filterGroupMag;
	}
		
	public function buildOperatorOptions(): array {
		$operatorOptions = array();

		if ($this->selectorMag !== null) {
			if ($this->toMany) {
				$operatorOptions[CriteriaComparator::OPERATOR_CONTAINS] = Rocket::createLstr('common_operator_contains_label', 'rocket');
				$operatorOptions[CriteriaComparator::OPERATOR_CONTAINS_NOT] = Rocket::createLstr('common_operator_contains_not_label', 'rocket');
			} else {
				$operatorOptions[CriteriaComparator::OPERATOR_IN] = Rocket::createLstr('common_operator_in_label', 'rocket');
				$operatorOptions[CriteriaComparator::OPERATOR_NOT_IN] = Rocket::createLstr('common_operator_not_in_label', 'rocket');
			}
		}
		
		$operatorOptions[CriteriaComparator::OPERATOR_EXISTS] = Rocket::createLstr('common_operator_exists_label', 'rocket');
		$operatorOptions[CriteriaComparator::OPERATOR_NOT_EXISTS] = Rocket::createLstr('common_operator_not_exists_label', 'rocket');
		
		return $operatorOptions;
	}
}

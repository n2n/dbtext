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
namespace rocket\ei\manage\security\filter;

use rocket\ei\EiPropPath;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\entry\EiEntryConstraint;
use rocket\ei\manage\entry\EiFieldConstraint;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use n2n\util\type\attrs\AttributesException;

class SecurityFilterDefinition {
	/**
	 * @var SecurityFilterProp[] $props
	 */
	private $props = array();
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param SecurityFilterProp $securityFilterProp
	 */
	public function putProp(EiPropPath $eiPropPath, SecurityFilterProp $securityFilterProp) {
		$this->props[(string) $eiPropPath] = $securityFilterProp;
		$this->filterDefinition = null;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 */
	public function removeProp(EiPropPath $eiPropPath) {
		unset($this->props[(string) $eiPropPath]);
		$this->filterDefinition = null;
	}
	
	/**
	 * @return \rocket\ei\manage\security\filter\SecurityFilterProp[]
	 */
	public function getProps() {
		return $this->props;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws UnknownSecurityFilterPropException
	 * @return \rocket\ei\manage\security\filter\SecurityFilterProp
	 */
	public function getFilterPropById(EiPropPath $eiPropPath) {
		if (isset($this->props[(string) $eiPropPath])) {
			return $this->props[(string) $eiPropPath];
		}
		
		throw new UnknownSecurityFilterPropException();
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return bool
	 */
	public function containsProp(EiPropPath $eiPropPath) {
		return isset($this->props[(string) $eiPropPath]);
	}
	
	/**
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->props);
	}
	
	private $filterDefinition;
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	function toFilterDefinition() {
		if ($this->filterDefinition !== null) {
			return $this->filterDefinition;
		}
		
		$this->filterDefinition = $fd =  new FilterDefinition();
		foreach ($this->props as $eiPropPath => $prop) {
			$fd->putFilterProp($eiPropPath, $prop);
		}
		return $fd;
	}
	
	public function createEiEntryConstraint(FilterSettingGroup $filterSettingGroup): EiEntryConstraint {
		$group = new EiFieldConstraintGroup($filterSettingGroup->isAndUsed());
		
		foreach ($filterSettingGroup->getFilterSettings() as $subFilterSetting) {
			$id = $subFilterSetting->getFilterPropId();
			if (!isset($this->props[$id])) {
				continue;
			}
			
			try {
				$group->addEiFieldConstraint(EiPropPath::create($id), 
						$this->props[$id]->createEiFieldConstraint($subFilterSetting->getDataSet()));
			} catch (AttributesException $e) {}
		}
		
		foreach ($filterSettingGroup->getFilterSettingGroups() as $subFilterSettingGroup) {
			$group->addEiEntryConstraint($this->createEiEntryConstraint($subFilterSettingGroup));
		}
		
		return $group;
	}
	
// 	public function createComparatorConstraint(FilterSettingGroup $filterSettingGroup): ComparatorConstraint {
// 		$criteriaComparators = array();
		
// 		foreach ($filterSettingGroup->getFilterSettings() as $subFilterSetting) {
// 			$id = $subFilterSetting->getFilterPropId();
// 			if (!isset($this->props[$id])) {
// 				continue;
// 			}
			
// 			try {
// 				$criteriaComparators[] = $this->props[$id]->createComparatorConstraint(
// 						$subFilterSetting->getDataSet());
// 			} catch (AttributesException $e) {}
// 		}
		
// 		foreach ($filterSettingGroup->getFilterSettingGroups() as $subFilterSettingGroup) {
// 			$criteriaComparators[] = $this->createComparatorConstraint($subFilterSettingGroup);
// 		}
		
// 		return new ComparatorConstraintGroup($filterSettingGroup->isAndUsed(), $criteriaComparators);
// 	}
	
	// 	private function createElementComparatorConstraint(FilterDataElement $element) {
	// 		if ($element instanceof FilterDataUsage) {
	// 			$itemId = $element->getItemId();
	// 			if (isset($this->filterProps[$itemId])) {
	// 				$comparatorConstraint = $this->filterProps[$itemId]->createComparatorConstraint($element->getDataSet());
	// 				ArgUtils::valTypeReturn($comparatorConstraint,
	// 						'rocket\ei\manage\critmod\ComparatorConstraint',
	// 						$this->filterProps[$itemId], 'createComparatorConstraint');
	// 				return $comparatorConstraint;
	// 			}
	// 		} else if ($element instanceof FilterDataGroup) {
	// 			$group = new CriteriaComparatorConstraintGroup($element->isAndUsed());
	// 			foreach ($element->getAll() as $childElement) {
	// 				$group->addComparatorConstraint($this->createElementComparatorConstraint($childElement));
	// 			}
	// 			return $group;
	// 		}
	
	// 		return null;
	// 	}
	
	
	// 	public static function createFromFilterProps(FilterData $filterData, array $filterItems) {
	// 		$filterModel = new FilterModel($filterData);
	// 		foreach ($filterItems as $id => $filterItem) {
	// 			$filterModel->putFilterProp($id, $filterItem);
	// 		}
	// 		return $filterModel;
	// 	}
}

class EiFieldConstraintGroup implements EiEntryConstraint {
	private $useAnd;
	/**
	 * @var EiFieldConstraint[][]
	 */
	private $eiFieldConstraints = array();
	/**
	 * @var EiEntryConstraint[]
	 */
	private $eiEntryConstraints = array();
	
	function __construct(bool $useAnd = true) {
		$this->useAnd= $useAnd;
	}
	
	public function addEiFieldConstraint(EiPropPath $eiPropPath, EiFieldConstraint $eiFieldConstraint) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->eiFieldConstraints[$eiPropPathStr])) {
			$this->eiFieldConstraints[$eiPropPathStr] = array();
		}
		
		$this->eiFieldConstraints[$eiPropPathStr][] = $eiFieldConstraint;
	}
	
	
	
	public function acceptsValue(EiPropPath $eiPropPath, $value): bool {
		$eiPropPathStr = (string) $eiPropPath;
		
		if (!isset($this->eiFieldConstraints[$eiPropPathStr])) return true;
		
		$eiField = $eiEntry->getEiField($eiPropPath);
		foreach ($this->eiFieldConstraints[$eiPropPathStr] as $eiFieldConstraint) {
			if ($eiFieldConstraint->acceptsValue($value)) {
				if (!$this->useAnd) return true;
			} else {
				if ($this->useAnd) return false;
			}
		}
		
		foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
			if ($eiEntryConstraint->acceptsValue($eiPropPath, $value)) {
				if (!$this->useAnd) return true;
			} else {
				if ($this->useAnd) return false;
			}
		}
		
		return $this->useAnd;
	}

	public function check(EiEntry $eiEntry): bool {
		if (empty($this->eiFieldConstraints)) return true;
		
		foreach ($this->eiFieldConstraints as $eiPropPathStr => $eiFieldConstraints) {
			$eiField = $eiEntry->getEiField(EiPropPath::create($eiPropPathStr));
			foreach ($eiFieldConstraints as $eiFieldConstraint) {
				if ($eiFieldConstraint->check($eiField)) {
					if (!$this->useAnd) return true;
				} else {
					if ($this->useAnd) return false;
				}
			}
		}
		
		foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
			if ($eiEntryConstraint->check($eiEntry)) {
				if (!$this->useAnd) return true;
			} else {
				if ($this->useAnd) return false;
			}
		}
		
		return $this->useAnd;
	}

	public function validate(EiEntry $eiEntry) {
		if ($this->check($eiEntry)) return;
		
		foreach ($this->eiFieldConstraints as $eiPropPathStr => $eiFieldConstraints) {
			$eiPropPath = EiPropPath::create($eiPropPathStr);
			$eiField = $eiEntry->getEiField($eiPropPath);
			$validationResult = $eiEntry->getValidationResult()->getEiFieldValidationResult($eiPropPath);
			foreach ($eiFieldConstraints as $eiFieldConstraint) {
				$eiFieldConstraint->validate($eiField, $validationResult);
			}
		}
		
		foreach ($this->eiEntryConstraints as $eiEntryConstraint) {
			$eiEntryConstraint->validate($eiEntry);
		}
	}
}

class UnknownSecurityFilterPropException extends \RuntimeException {
	
}

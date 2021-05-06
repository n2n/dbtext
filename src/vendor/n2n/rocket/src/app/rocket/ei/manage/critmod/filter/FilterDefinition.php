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
namespace rocket\ei\manage\critmod\filter;

use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use n2n\util\type\attrs\AttributesException;

class FilterDefinition {
	private $filterProps = array();
	
	public function putFilterProp(string $id, FilterProp $filterItem) {
		$this->filterProps[$id] = $filterItem;
	}
	
	public function getFilterProps(): array {
		return $this->filterProps;
	}
	
	public function getFilterPropById(string $id): FilterProp {
		if (isset($this->filterProps[$id])) {
			return $this->filterProps[$id];
		}
		
		throw new UnknownFilterPropException();
	}
	
	public function containsFilterPropId(string $id): bool {
		return isset($this->filterProps[$id]);
	}
	
	public function isEmpty(): bool {
		return empty($this->filterProps);
	}
	
	public function createComparatorConstraint(FilterSettingGroup $filterSettingGroup): ComparatorConstraint {
		$comparatorConstraints = array();
		
		foreach ($filterSettingGroup->getFilterSettings() as $subFilterSetting) {
			$id = $subFilterSetting->getFilterPropId();
			
			if (!isset($this->filterProps[$id])) {
				continue;
			}
			try {
				$comparatorConstraints[] = $this->filterProps[$id]->createComparatorConstraint(
						$subFilterSetting->getDataSet());
			} catch (AttributesException $e) {}
		}
		
		foreach ($filterSettingGroup->getFilterSettingGroups() as $subFilterSettingGroup) {
			$comparatorConstraints[] = $this->createComparatorConstraint($subFilterSettingGroup);
		}
		
		return new ComparatorConstraintGroup($filterSettingGroup->isAndUsed(), $comparatorConstraints);
	}
	
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

class UnknownFilterPropException extends \RuntimeException {
	
}

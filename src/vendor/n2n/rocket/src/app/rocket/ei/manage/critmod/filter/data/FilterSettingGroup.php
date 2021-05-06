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
namespace rocket\ei\manage\critmod\filter\data;

use n2n\util\col\GenericArrayObject;
use n2n\util\type\attrs\DataSet;
use n2n\util\type\TypeConstraint;

class FilterSettingGroup {
	const ATTR_USE_AND_KEY = 'useAnd';
	const ATTR_FILTER_ITEMS_KEY = 'items';
	const ATTR_FILTER_GROUPS_KEY = 'groups';

	private $filterPropSettings = array();
	private $filterSettingGroups = array();
	private $andUsed = true;

	public function __construct() {
		$this->filterPropSettings = new GenericArrayObject(null, FilterSetting::class);
		$this->filterSettingGroups = new GenericArrayObject(null, FilterSettingGroup::class);
	}
	
	public function getFilterSettings(): \ArrayObject {
		return $this->filterPropSettings;
	}
	
	public function getFilterSettingGroups(): \ArrayObject {
		return $this->filterSettingGroups;
	}

	public function isEmpty() {
		return empty($this->filterPropSettings) && empty($this->filterSettingGroups);
	}
	
	public function isAndUsed(): bool {
		return $this->andUsed;
	}

	public function setAndUsed(bool $andUsed) {
		$this->andUsed = $andUsed;
	}
	
	public function toAttrs(): array {
		$filterItemsAttrs = array();
		foreach ($this->filterPropSettings as $filterItemSetting) {
			$filterItemsAttrs[] = $filterItemSetting->toAttrs();
		}
		
		$filterGroupsAttrs = array();
		foreach ($this->filterSettingGroups as $filterGroupData) {
			$filterGroupsAttrs[] = $filterGroupData->toAttrs();
		}

		return array(
				self::ATTR_USE_AND_KEY => $this->andUsed,
				self::ATTR_FILTER_ITEMS_KEY => $filterItemsAttrs,
				self::ATTR_FILTER_GROUPS_KEY => $filterGroupsAttrs);
	}

	public static function create(DataSet $dataSet): FilterSettingGroup {
		$fgd = new FilterSettingGroup();
		$fgd->setAndUsed($dataSet->optBool(self::ATTR_USE_AND_KEY, true));

		$settings = $fgd->getFilterSettings();
		foreach ($dataSet->optArray(self::ATTR_FILTER_ITEMS_KEY, 
				TypeConstraint::createArrayLike('array')) as $filterItemAttrs) {
			$settings->append(FilterSetting::create(new DataSet($filterItemAttrs)));
		}
		
		$settingGroups = $fgd->getFilterSettingGroups();
		foreach ($dataSet->optArray(self::ATTR_FILTER_GROUPS_KEY, 
				TypeConstraint::createArrayLike('array')) as $filterGroupAttrs) {
			$settingGroups->append(FilterSettingGroup::create(new DataSet($filterGroupAttrs)));
		}

		return $fgd;
	}
}

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
namespace rocket\ei\util\sort\form;

use n2n\web\dispatch\Dispatchable;
use rocket\ei\manage\critmod\sort\SortSettingGroup;
use rocket\ei\manage\critmod\sort\SortDefinition;
use n2n\persistence\orm\criteria\Criteria;
use rocket\ei\manage\critmod\sort\SortSetting;
use rocket\ei\EiPropPath;

class SortForm implements Dispatchable {
	private $sortData;
	private $sortDefinition;
	
	protected $sortPropIds;
	protected $directions;
	
	public function __construct(SortSettingGroup $sortData, SortDefinition $sortDefinition) {
		$this->sortData = $sortData;
		$this->sortDefinition = $sortDefinition;
		
		$this->clear();
		foreach ($sortData->getSortSettings() as $key => $sortItemData) {
			$this->sortPropIds['s-' . $key] = $sortItemData->getSortPropId(); 
			$this->directions['s-' . $key] = $sortItemData->getDirection();
		}
	}
	
	public function getSortDefinition(): SortDefinition {
		return $this->sortDefinition;
	}
	
	public function getSortPropIds(): array {
		return $this->sortPropIds;
	}
	
	public function setSortPropIds(array $sortPropIds) {
		$this->sortPropIds = $sortPropIds;
	}
	
	public function getDirections(): array {
		return $this->directions;
	}
	
	public function setDirections(array $directions) {
		$this->directions = $directions;
	}

	public function clear() {
		$this->sortPropIds = array();
		$this->directions = array();
	}
	
	private function _validation() {
	}
	
	public function buildSortSettingGroup(): SortSettingGroup {
		$sortData = new SortSettingGroup();
		
		$sortItemDatas = $sortData->getSortSettings();
		foreach ($this->sortPropIds as $key => $sortPropId) {
			if (!$this->sortDefinition->containsEiPropPath(EiPropPath::create($sortPropId))) continue;
			
			$direction = Criteria::ORDER_DIRECTION_ASC;
			if (isset($this->directions[$key]) && $this->directions[$key] === Criteria::ORDER_DIRECTION_DESC) {
				$direction = Criteria::ORDER_DIRECTION_DESC;
			}
			$sortItemDatas[] = new SortSetting($sortPropId, $direction);
		}
		
		return $sortData;
	}
	
	
}

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
// namespace rocket\impl\ei\component\command\common\model;

// use rocket\ei\manage\critmod\filter\data\FilterData;
// use n2n\context\SessionScoped;

// class ListTmpFilterStore implements SessionScoped {
// 	private $filterIds;
// 	private $filterDatas = array();
// 	private $sortDirections = array();
// 	private $searchStrs;
	
// 	private function _onSerialize() {}
// 	private function _onUnserialize() {}
	
// 	public function setFilterId($eiTypeId, $filterId) {
// 		$this->filterIds[$eiTypeId] = $filterId;
// 	}
	
// 	public function getFilterId($eiTypeId) {
// 		if (isset($this->filterIds[$eiTypeId])) {
// 			return $this->filterIds[$eiTypeId];
// 		}
		
// 		return null;
// 	}
	
// 	public function setTmpFilterData($eiTypeId, FilterData $filterData = null) {
// 		$this->filterDatas[$eiTypeId] = $filterData;
// 	}	
	
// 	public function getTmpFilterData($eiTypeId) {
// 		if (isset($this->filterDatas[$eiTypeId])) {
// 			return $this->filterDatas[$eiTypeId];
// 		}
		
// 		return null;
// 	}
	
// 	public function setTmpSortDirections($eiTypeId, array $sortDirections = null) {
// 		$this->sortDirections[$eiTypeId] = $sortDirections;
// 	}	
	
// 	public function getTmpSortDirections($eiTypeId) {
// 		if (isset($this->sortDirections[$eiTypeId])) {
// 			return $this->sortDirections[$eiTypeId];
// 		}
		
// 		return null;
// 	}
	
// 	public function setTmpSearchStr($eiTypeId, $searchStr) {
// 		$this->searchStrs[$eiTypeId] = $searchStr;
// 	}
	
// 	public function getTmpSearchStr($eiTypeId) {
// 		if (isset($this->searchStrs[$eiTypeId])) {
// 			return $this->searchStrs[$eiTypeId];
// 		}
// 		return null;
// 	}
// }

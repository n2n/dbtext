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
namespace rocket\ei\manage\critmod;

// use n2n\util\StringUtils;
// use n2n\reflection\ObjectAdapter;
// use n2n\reflection\annotation\AnnoInit;
// use rocket\ei\manage\critmod\filter\data\FilterData;
// use n2n\persistence\orm\annotation\AnnoTable;

// class Filter extends ObjectAdapter {
// 	private static function _annos(AnnoInit $ai) {
// 		$ai->c(new AnnoTable('rocket_filter'));
// 	}
	
// 	private $id;
// 	private $eiTypeId;
// 	private $name;
// 	private $filterDataJson = '[]';
// 	private $sortDirectionsJson  = '[]';
	
// 	public function getId() {
// 		return $this->id;
// 	}
	
// 	public function getName() {
// 		return $this->name;
// 	}
	
// 	public function setName($name) {
// 		$this->name = $name;
// 	}
	
// 	public function getEiTypeId() {
// 		return $this->eiTypeId;
// 	}
	
// 	public function setEiTypeId($eiTypeId) {
// 		$this->eiTypeId = $eiTypeId;
// 	}

// 	public function readFilterData() {
// 		$data = array();
// 		if (!empty($this->filterDataJson)) {
// 			$data = StringUtils::jsonDecode($this->filterDataJson, true);
// 		}
// 		return FilterData::createFromArray($data);
// 	}
	
// 	public function writeFilterData(FilterData $filterData) {
// 		$this->filterDataJson = StringUtils::jsonEncode($filterData->toArray());		
// 	}
	
// 	public function getSortDirections() {
// 		if (empty($this->filterDataJson)) {
// 			return array();
// 		}
// 		return StringUtils::jsonDecode($this->sortDirectionsJson, true);
// 	}
	
// 	public function setSortDirections(array $sortDirections) {
// 		$this->sortDirectionsJson = StringUtils::jsonEncode($sortDirections);
// 	}
// }

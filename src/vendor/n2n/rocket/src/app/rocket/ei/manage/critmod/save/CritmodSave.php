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
namespace rocket\ei\manage\critmod\save;

use n2n\util\StringUtils;
use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoTable;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use n2n\util\JsonDecodeFailedException;
use n2n\util\type\attrs\DataSet;
use rocket\ei\manage\critmod\sort\SortSettingGroup;

class CritmodSave extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('rocket_critmod_save'));
	}
	
	private $id;
	private $eiTypePath;
	private $name;
	private $filterDataJson = '[]';
	private $sortDataJson  = '[]';
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName(string $name) {
		$this->name = $name;
	}
	
	public function getEiTypePath() {
		return $this->eiTypePath;
	}
	
	public function setEiTypePath(string $eiTypePath) {
		$this->eiTypePath = $eiTypePath;
	}
	
	public function readFilterSettingGroup(): FilterSettingGroup {
		$data = array();
		try {
			$data = StringUtils::jsonDecode($this->filterDataJson, true);
		} catch (JsonDecodeFailedException $e) {}
		return FilterSettingGroup::create(new DataSet($data));
	}
	
	public function writeFilterData(FilterSettingGroup $filterSettingGroup) {
		$this->filterDataJson = StringUtils::jsonEncode($filterSettingGroup->toAttrs());		
	}
	
	public function readSortSettingGroup(): SortSettingGroup {
		$data = array();
		try {
			$data = StringUtils::jsonDecode($this->sortDataJson, true);
		} catch (JsonDecodeFailedException $e) {}
		return SortSettingGroup::create(new DataSet($data));
	}
	
	public function writeSortSettingGroup(SortSettingGroup $sortData) {
		$this->sortDataJson = StringUtils::jsonEncode($sortData->toAttrs());
	}
}

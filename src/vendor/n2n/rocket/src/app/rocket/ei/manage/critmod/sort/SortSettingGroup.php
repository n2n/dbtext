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
namespace rocket\ei\manage\critmod\sort;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\ArgUtils;
use n2n\persistence\orm\criteria\Criteria;
use n2n\util\col\GenericArrayObject;

class SortSettingGroup {
	private $sortItemDatas;

	public function __construct() {
		$this->sortItemDatas = new GenericArrayObject(null, SortSetting::class);
	}
	
	/**
	 * @return SortSetting[]
	 */
	public function getSortSettings(): \ArrayObject {
		return $this->sortItemDatas;
	}
	
	
	public function isEmpty() {
		return empty($this->sortItemDatas);
	}
	
	public function toAttrs(): array {
		$attrs = array();
		
		foreach ($this->sortItemDatas as $sortItemData) {
			$attrs[$sortItemData->getSortPropId()] = $sortItemData->getDirection();
		}
		
		return $attrs;
	}

	public static function create(DataSet $dataSet): SortSettingGroup {
		$sortData = new SortSettingGroup();
		$sortItemDatas = $sortData->getSortSettings();
		foreach ($dataSet->toArray() as $sortPropId => $direction) {
			if (!is_string($direction)) continue;
			try {
				$sortItemDatas[] = new SortSetting($sortPropId, $direction);
			} catch (\InvalidArgumentException $e) {}
		}
		
		return $sortData;
	}
}

class SortSetting {
	private $sortPropId;
	private $direction;
	
	public function __construct(string $sortPropId, string $direction) {
		$this->sortPropId = $sortPropId;
		$this->setDirection($direction);
	}
	
	public function getSortPropId(): string {
		return $this->sortPropId;
	}
	
	public function setSortPropId(string $sortPropId) {
		$this->sortPropId = $sortPropId;
	}
	
	public function getDirection(): string {
		return $this->direction;
	}
		
	public function setDirection(string $direction) {
		ArgUtils::valEnum($direction, Criteria::getOrderDirections());
		$this->direction = $direction;
	}
}

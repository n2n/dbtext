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

use n2n\util\type\attrs\DataSet;

class FilterSetting {
	const ATTR_ITEM_ID_KEY = 'prop';
	const ATTR_ATTRS_KEY = 'attrs';

	private $filterPropId;
	private $dataSet;

	/**
	 * @param string|null $filterPropId
	 * @param DataSet $dataSet
	 */
	public function __construct(?string $filterPropId, DataSet $dataSet) {
		$this->filterPropId = $filterPropId;
		$this->dataSet = $dataSet;
	}

	/**
	 * @param string $filterPropId
	 */
	public function setFilterPropId(string $filterPropId) {
		$this->filterPropId = $filterPropId;
	}

	/**
	 * @return string
	 */
	public function getFilterPropId() {
		return $this->filterPropId;
	}

	public function setDataSet(DataSet $dataSet) {
		$this->dataSet = $dataSet;
	}

	public function getDataSet(): DataSet {
		return $this->dataSet;
	}

	public function toAttrs(): array {
		return array(
				self::ATTR_ITEM_ID_KEY => $this->filterPropId,
				self::ATTR_ATTRS_KEY => $this->dataSet->toArray());
	}

	public static function create(DataSet $dataSet): FilterSetting {
		return new FilterSetting($dataSet->getString(self::ATTR_ITEM_ID_KEY),
				new DataSet($dataSet->getArray(self::ATTR_ATTRS_KEY, false, array())));
	}
}

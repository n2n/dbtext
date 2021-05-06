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
namespace rocket\ei\util\filter\form;

use n2n\web\dispatch\Dispatchable;
use n2n\web\dispatch\map\bind\MappingDefinition;
use rocket\ei\manage\critmod\filter\data\FilterSetting;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use n2n\web\dispatch\map\bind\BindingErrors;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\web\dispatch\map\MappingResult;
use n2n\web\dispatch\DispatchContext;
use n2n\util\ex\IllegalStateException;
use n2n\core\container\N2nContext;

class FilterPropItemForm implements Dispatchable {
	private $filterItemSetting;
	private $filterModel;
	
	protected $filterPropId;
	protected $magForm;
	
	public function __construct(FilterSetting $filterItemSetting, FilterDefinition $filterDefinition) {
		$this->filterItemSetting = $filterItemSetting;
		$this->filterModel = $filterDefinition;
		
		$this->filterPropId = $filterItemSetting->getFilterPropId();
		if ($this->filterPropId !== null && null !== ($filterItem = $filterDefinition->getFilterPropById($this->filterPropId))) {
			$this->magForm = $filterItem->createMagDispatchable($filterItemSetting->getDataSet());
		}
	}
	
	private function _mapping(MappingResult $mr, MappingDefinition $md, DispatchContext $dc, BindingErrors $be, 
			N2nContext $n2nContext) {
		if (!$md->isDispatched()) return;
		
		$filterProp = null;
		if (null !== ($fieldFieldId = $md->getDispatchedValue('filterPropId'))) {
			$filterProp = $this->filterModel->getFilterPropById($fieldFieldId);
		}
		
		if ($filterProp === null) {
			$be->addError('filterPropId', 'Invalid filter item.');
			return;
		}
		
		$this->magForm = $filterProp->createMagDispatchable($this->filterItemSetting->getDataSet());
// 		$mr->magForm = $dc->getOrCreateMappingResult($magForm, $n2nContext);
	}
	
	private function _validation() {
	}
	
	public function setFilterPropId(string $filterPropId) {
		$this->filterPropId = $filterPropId;
	}
	
	public function getFilterPropId() {
		return $this->filterPropId;
	}
	
	public function setMagForm(MagDispatchable $magForm) {
		$this->magForm = $magForm;
	}
	
	public function getMagForm() {
		return $this->magForm;
	}
	
	public function buildFilterSetting(): FilterSetting {
		$filterItem = $this->filterModel->getFilterPropById($this->filterPropId);
		if ($filterItem === null) {
			throw new IllegalStateException();
		}
		
		$this->filterItemSetting->setFilterPropId($this->filterPropId);
		$this->filterItemSetting->setDataSet($filterItem->buildDataSet($this->magForm));
	
		return $this->filterItemSetting;
	}
}

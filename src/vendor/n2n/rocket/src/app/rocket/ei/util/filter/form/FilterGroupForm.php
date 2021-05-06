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
use rocket\ei\manage\critmod\filter\data\FilterSetting;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObjectArray;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\map\bind\BindingDefinition;
use rocket\ei\manage\critmod\filter\UnknownFilterPropException;

class FilterGroupForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('filterPropItemForms', new AnnoDispObjectArray(function (FilterGroupForm $filterGroupForm) {
			return new FilterPropItemForm(new FilterSetting(null, new DataSet()), $filterGroupForm->filterDefinition);
		}));
		$ai->p('filterGroupForms', new AnnoDispObjectArray(function (FilterGroupForm $filterGroupForm) {
			return new FilterGroupForm(new FilterSettingGroup(), $filterGroupForm->filterDefinition);
		}));
	}
	
	private $filterSettingGroup;
	private $filterDefinition;
	
	protected $useAnd;
	protected $filterPropItemForms;
	protected $filterGroupForms;
	
	public function __construct(FilterSettingGroup $filterSettingGroup, FilterDefinition $filterDefinition) {
		$this->filterSettingGroup = $filterSettingGroup;
		$this->filterDefinition = $filterDefinition;
		
		$this->useAnd = $filterSettingGroup->isAndUsed();
		
		$this->filterPropItemForms = array();
		foreach ($filterSettingGroup->getFilterSettings() as $key => $filterPropSetting) {
			try {
				$this->filterPropItemForms['p-' . $key] = new FilterPropItemForm($filterPropSetting, $filterDefinition);
			} catch (UnknownFilterPropException $e) {}
		}
		
		$this->filterGroupForms = array();
		foreach ($filterSettingGroup->getFilterSettingGroups() as $key => $filterSettingGroup) {
			$this->filterGroupForms['g-' . $key] = new FilterGroupForm($filterSettingGroup, $filterDefinition);
		}
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\data\FilterSettingGroup
	 */
	public function getFilterSettingGroup() {
		return $this->filterSettingGroup;
	}
	
	public function getFilterDefinition(): FilterDefinition {
		return $this->filterDefinition;
	}

	public function setUseAnd($useAnd) {
		$this->useAnd = (bool) $useAnd;
	}
	
	public function isUseAnd(): bool {
		return $this->useAnd;
	}
	
	public function setFilterPropItemForms(array $filterPropItemForms) {
		$this->filterPropItemForms = $filterPropItemForms;
	}
	
	public function getFilterPropItemForms(): array {
		return $this->filterPropItemForms;
	}
	
	public function setFilterGroupForms(array $filterGroupForms) {
		$this->filterGroupForms = $filterGroupForms;
	}
	
	public function getFilterGroupForms(): array {
		return $this->filterGroupForms;
	}
	
	public function clear() {
		$this->filterPropItemForms = array();
		$this->filterGroupForms = array();
	}
	
// 	private function _mapping(MappingResult $mr, MappingDefinition $md, DispatchContext $dc, BindingErrors $be) {
// 		if (!$md->isDispatched()) return;
		
// 		$fieldItemId = $md->getDispatchedValue('fieldItemId');
// 		$filterItem = null;
// 		if (is_scalar($fieldItemId)) {
// 			$filterItem = $this->filterDefinition->getFilterPropById($fieldItemId);
// 		}
		
// 		if ($filterItem === null) {
// 			$be->addError('fieldItemId', 'Invalid filter item.');
// 			return;
// 		}
		
// 		$magForm = new MagDispatchable($filterItem->createMagCollection($this->filterSettingGroup->getDataSet()));
// 		$mr->magForm = new MappingResult($magForm, $dc->getDispatchModelManager()->getDispatchModel($magForm));
// 	}
	
	private function _validation(BindingDefinition $bd) {
		
	}
	
	public function buildFilterSettingGroup(): FilterSettingGroup {
		$this->filterSettingGroup->setAndUsed($this->useAnd);
		
		$filterItemSettings = $this->filterSettingGroup->getFilterSettings();
		$filterItemSettings->clear();
		foreach ($this->filterPropItemForms as $filterPropItemForm) {
			$filterItemSettings->append($filterPropItemForm->buildFilterSetting());
		}
		
		$filterSettingGroups = $this->filterSettingGroup->getFilterSettingGroups();
		$filterSettingGroups->clear();
		foreach ($this->filterGroupForms as $filterGroupForm) {
			$filterSettingGroups->append($filterGroupForm->buildFilterSettingGroup());
		}
		
		return $this->filterSettingGroup;
	}
}

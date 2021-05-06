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
namespace rocket\spec\extr;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\ArgUtils;
use rocket\ei\mask\model\DisplayScheme;
use rocket\ei\mask\model\DisplayStructure;
use n2n\util\type\CastUtils;

class SpecRawer {
	private $dataSet;
	
	public function __construct(DataSet $dataSet) {
		$this->dataSet = $dataSet;
	}
	// PUT
	
	public function rawTypes(array $eiTypeExtractions, array $customTypeExtractions) {
		ArgUtils::valArray($eiTypeExtractions, EiTypeExtraction::class);
		ArgUtils::valArray($customTypeExtractions, CustomTypeExtraction::class);
		
		$specsRawData = array();
		
		foreach ($eiTypeExtractions as $eiTypeExtraction) {
			$specsRawData[$eiTypeExtraction->getId()] = $this->buildEiTypeExtractionRawData($eiTypeExtraction);
		}
		
		foreach ($customTypeExtractions as $customTypeExtraction) {
			$specsRawData[$customTypeExtraction->getId()] = $this->buildCustomTypeExtractionRawData($customTypeExtraction);
		}
		
		$this->dataSet->set(RawDef::TYPES_KEY, $specsRawData);
	}
	
	private function buildCustomTypeExtractionRawData(CustomTypeExtraction $customTypeExtraction) {
		$rawData = array();
		$rawData[RawDef::TYPE_NATURE_KEY] = RawDef::NATURE_CUSTOM;
		$rawData[RawDef::CUSTOM_CONTROLLER_LOOKUP_ID_KEY] = $customTypeExtraction->getControllerLookupId();
		return $rawData;
	}
	
	private function buildEiTypeExtractionRawData(EiTypeExtraction $extraction) {
		$rawData = array();	
		$rawData[RawDef::TYPE_NATURE_KEY] = RawDef::NATURE_ENTITY;
		$rawData[RawDef::EI_CLASS_KEY] = $extraction->getEntityClassName();
		$rawData[RawDef::EI_DATA_SOURCE_NAME_KEY] = $extraction->getDataSourceName();
		
		if (null !== ($nestedSetStrategy = $extraction->getNestedSetStrategy())) {
			$rawData[RawDef::EI_NESTED_SET_STRATEGY_KEY] = array(
					RawDef::EI_NESTED_SET_STRATEGY_LEFT_KEY
							=> (string) $nestedSetStrategy->getLeftCriteriaProperty(),
					RawDef::EI_NESTED_SET_STRATEGY_RIGHT_KEY
							=> (string) $nestedSetStrategy->getRightCriteriaProperty());
		}
		
		$rawData = array_merge($rawData, $this->buildEiMaskExtractionRawData($extraction->getEiMaskExtraction()));
		return $rawData;
	}
	
	public function rawEiMasks(array $groupedEiTypeExtensionExtractions) {
		$rawData = array();
		foreach ($groupedEiTypeExtensionExtractions as $eiTypeId => $eiTypeExtensionExtractions) {
			if (empty($eiTypeExtensionExtractions)) continue;
			
			$eiMasksRawData = array();
			foreach ($eiTypeExtensionExtractions as $eiTypeExtensionExtraction) {
				$eiMasksRawData[$eiTypeExtensionExtraction->getId()] = $this->buildEiTypeExtensionExtractionRawData($eiTypeExtensionExtraction);
			}
			
			$rawData[$eiTypeId] = $eiMasksRawData;
		}
		
		$this->dataSet->set(RawDef::EI_TYPE_EXTENSIONS_KEY, $rawData);
	}
	
	public function rawEiModificatorExtractionGroups(array $eiModificatorExtractionGroups) {
		if (empty($eiModificatorExtractionGroups)) return;
		
		$rawData = array();
		
		foreach ($eiModificatorExtractionGroups as $eiTypeId => $eiModificatorExtractionGroup) {
			if (empty($eiModificatorExtractionGroup)) continue;
			
			
			foreach ($eiModificatorExtractionGroup as $eiModificatorExtraction) {
				CastUtils::assertTrue($eiModificatorExtraction instanceof EiModificatorExtraction);
				$typePathStr = (string) $eiModificatorExtraction->getTypePath();
				if (!isset($rawData[$typePathStr])) {
					$rawData[$typePathStr] = array();
				}
				
				$rawData[$typePathStr][$eiModificatorExtraction->getId()] = $this->buildEiModificatorExtractionRawData($eiModificatorExtraction);
			}
		}
		
		$this->dataSet->set(RawDef::EI_MODIFICATORS_KEY, $rawData);
	}
	
	private function buildEiTypeExtensionExtractionRawData(EiTypeExtensionExtraction $eiTypeExtensionExtraction) {
		return $this->buildEiMaskExtractionRawData($eiTypeExtensionExtraction->getEiMaskExtraction());
	}

	private function buildEiMaskExtractionRawData(EiMaskExtraction $extraction) {
		$rawData[RawDef::EI_DEF_LABEL_KEY] = $extraction->getLabel();
		$rawData[RawDef::EI_DEF_PLURAL_LABEL_KEY] = $extraction->getPluralLabel();
		$rawData[RawDef::EI_DEF_ICON_TYPE_KEY] = $extraction->getIconType();
		
		if (null !== ($identityStringPattern = $extraction->getIdentityStringPattern())) {
			$rawData[RawDef::EI_DEF_REPRESENTATION_STRING_PATTERN_KEY] = $identityStringPattern;
		}
		
		if (null !== ($draftingAllowed = $extraction->isDraftingAllowed())) {
			$rawData[RawDef::EI_DEF_DRAFTING_ALLOWED_KEY] = $draftingAllowed;
		}
		
		if (null !== ($previewControllerLookupId = $extraction->getPreviewControllerLookupId())) {
			$rawData[RawDef::EI_DEF_PREVIEW_CONTROLLER_LOOKUP_ID_KEY] = $previewControllerLookupId;
		}
		
		if (null !== ($filterData = $extraction->getFilterSettingGroup())) {
			$rawData[RawDef::EI_DEF_FILTER_DATA_KEY] = $filterData->toAttrs();
		}
		
		if (null !== ($defaultSortDirection = $extraction->getDefaultSortSettingGroup())) {
			$rawData[RawDef::EI_DEF_DEFAULT_SORT_KEY] = $defaultSortDirection->toAttrs();
		}
		
		$rawData[RawDef::EI_DEF_PROPS_KEY] = array();
		foreach ($extraction->getEiPropExtractions() as $eiPropExtraction) {
			$rawData[RawDef::EI_DEF_PROPS_KEY][$eiPropExtraction->getId()] 
					= $this->buildEiPropExtractionRawData($eiPropExtraction);
		}
	
		$rawData[RawDef::EI_DEF_COMMANDS_KEY] = array();
		foreach ($extraction->getEiCommandExtractions() as $eiComponentExtraction) {
			$rawData[RawDef::EI_DEF_COMMANDS_KEY][$eiComponentExtraction->getId()] 
					= $this->buildEiComponentExtractionRawData($eiComponentExtraction);
		}
		
		return array_merge($rawData, $this->buildDisplaySchemeRawData($extraction->getDisplayScheme()));
	}
	
	private function buildEiPropExtractionRawData(EiPropExtraction $extraction) {
		$rawData = array();
		$rawData[RawDef::EI_COMPONENT_CLASS_KEY] = $extraction->getClassName();
		$rawData[RawDef::EI_COMPONENT_PROPS_KEY] = $extraction->getProps();
		
		if (null !== ($label = $extraction->getLabel())) {
			$rawData[RawDef::EI_FIELD_LABEL_KEY] = $label;
		}
		
		if (null !== ($objectPropertyName = $extraction->getObjectPropertyName())) {
			$rawData[RawDef::EI_FIELD_OBJECT_PROPERTY_KEY] = $objectPropertyName;
		}

		if (null !== ($entityPropertyName = $extraction->getEntityPropertyName())) {
			$rawData[RawDef::EI_FIELD_ENTITY_PROPERTY_KEY] = $entityPropertyName;
		}
		
		return $rawData;
	}
	
	private function buildEiComponentExtractionRawData(EiComponentExtraction $extraction) {
		return array(
				RawDef::EI_COMPONENT_CLASS_KEY => $extraction->getClassName(),
				RawDef::EI_COMPONENT_PROPS_KEY => $extraction->getProps());
	}
	
	private function buildEiModificatorExtractionRawData(EiModificatorExtraction $eiModificatorExtraction) {
		return array(
				RawDef::EI_COMPONENT_CLASS_KEY => $eiModificatorExtraction->getClassName(),
				RawDef::EI_COMPONENT_PROPS_KEY => $eiModificatorExtraction->getProps());
	}
	
	private function buildDisplaySchemeRawData(DisplayScheme $guiOrder) {
		$rawData = array();
		
		if (null !== ($overviewDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getOverviewDisplayStructure()))) {
			$rawData[RawDef::OVERVIEW_GUI_FIELD_ORDER_KEY] = $overviewDisplayStructure;
		}
		
		if (null !== ($bulkyDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getBulkyDisplayStructure()))) {
			$rawData[RawDef::BULKY_GUI_FIELD_ORDER_KEY] = $bulkyDisplayStructure;
		}
		
		if (null !== ($bulkyDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getDetailDisplayStructure()))) {
			$rawData[RawDef::DETAIL_GUI_FIELD_ORDER_KEY] = $bulkyDisplayStructure;
		}
		
		if (null !== ($bulkyDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getEditDisplayStructure()))) {
			$rawData[RawDef::EDIT_GUI_FIELD_ORDER_KEY] = $bulkyDisplayStructure;
		}
		
		if (null !== ($bulkyDisplayStructure = $this->buildDisplayStructureRawData($guiOrder->getAddDisplayStructure()))) {
			$rawData[RawDef::ADD_GUI_FIELD_ORDER_KEY] = $bulkyDisplayStructure;
		}
				
		if (null !== ($controlOrder = $guiOrder->getPartialControlOrder())) {
			$rawData[RawDef::EI_DEF_PARTIAL_CONTROL_ORDER_KEY] = $controlOrder->getControlIds();
		}
		
		if (null !== ($controlOrder = $guiOrder->getOverallControlOrder())) {
			$rawData[RawDef::EI_DEF_OVERALL_CONTROL_ORDER_KEY] = $controlOrder->getControlIds();
		}
		
		if (null !== ($controlOrder = $guiOrder->getEntryGuiControlOrder())) {
			$rawData[RawDef::EI_DEF_ENTRY_CONTROL_ORDER_KEY] = $controlOrder->getControlIds();
		}
	
		return $rawData;
	}
	
	private function buildDisplayStructureRawData(DisplayStructure $displayStructure = null) {
		if ($displayStructure === null) return null;
	
		$displaStructureData = array();
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			$displayItemData = array(
					RawDef::DISPLAY_ITEM_LABEL_KEY => $displayItem->getLabel(),
					RawDef::DISPLAY_ITEM_GROUP_TYPE_KEY => $displayItem->getSiStructureType());
			if (!$displayItem->hasDisplayStructure()) {
				$displayItemData[RawDef::DISPLAY_ITEM_GUI_ID_PATH_KEY] = (string) $displayItem->getDefPropPath();
			} else {
				$displayItemData[RawDef::DISPLAY_ITEM_DISPLAY_STRUCTURE_KEY] = 
						$this->buildDisplayStructureRawData($displayItem->getDisplayStructure());
			}
			
			$displaStructureData[] = $displayItemData;
		}
		
		return $displaStructureData;
	}
	
	public function rawLaunchPads(array $launchPadExtractions) {
		ArgUtils::valArray($launchPadExtractions, LaunchPadExtraction::class);
	
		$launchPadsRawData = array();
		foreach ($launchPadExtractions as $launchPadExtraction) {
			$launchPadsRawData[(string) $launchPadExtraction->getTypePath()] = $this->buildLaunchPadExtractionRawData($launchPadExtraction);
		}
	
		$this->dataSet->set(RawDef::LAUNCH_PADS_KEY, $launchPadsRawData);
	}
	

	private function buildLaunchPadExtractionRawData(LaunchPadExtraction $launchPadExtraction) {
		if (null !== ($label = $launchPadExtraction->getLabel())) {
			return array(RawDef::LAUNCH_PAD_LABEL_KEY => $launchPadExtraction->getLabel());
		}
		
		return array();
	}
}

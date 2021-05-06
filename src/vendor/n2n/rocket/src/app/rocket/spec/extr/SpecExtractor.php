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
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\util\type\attrs\AttributesException;
use n2n\config\InvalidConfigurationException;
use rocket\spec\InvalidSpecConfigurationException;
use rocket\ei\mask\model\DisplayScheme;
use rocket\ei\mask\model\DisplayStructure;
use rocket\ei\manage\DefPropPath;
use rocket\spec\InvalidEiMaskConfigurationException;
use rocket\ei\mask\model\ControlOrder;
use n2n\util\type\TypeConstraint;
use rocket\spec\InvalidLaunchPadConfigurationException;
use rocket\ei\manage\critmod\sort\SortSettingGroup;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\util\type\attrs\InvalidAttributeException;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use n2n\util\StringUtils;
use rocket\spec\TypePath;
use rocket\core\model\Rocket;
use rocket\si\meta\SiStructureType;

class SpecExtractor {
	private $dataSet;
	private $moduleNamespace;
	
	public function __construct(DataSet $dataSet, string $moduleNamespace) {
		$this->dataSet = $dataSet;
		$this->moduleNamespace = $moduleNamespace;
	}

	private function createSpecException($id, \Exception $previous) {
		throw new InvalidSpecConfigurationException('Spec with following id is invalid configruated: ' . $id, 
				0, $previous);
	}
	
	private function createEiComponentException($componentName, \Exception $previous) {
		throw new InvalidEiComponentConfigurationException('Component invalid configurated: ' . $componentName, 
				0, $previous);
	}
	
	/**
	 * @return array
	 */
	public function extractTypes() {
		$eiTypeExtractions = array();
		$customTypeExtractions = array();
		
		$typesKey = RawDef::TYPES_KEY;
		if (!$this->dataSet->contains(RawDef::TYPES_KEY) && $this->dataSet->contains('specs')) {
			$typesKey = 'specs';
		}
		
		foreach ($this->dataSet->optArray($typesKey, 
				TypeConstraint::createArrayLike('array', true), array()) as $typeId => $typeRawData) {
// 			$eiTypeExtractions[$specId] = $this->createTypeExtraction($specId, );
			$typeDataSet = new DataSet($typeRawData);
			
			try {
				$natureKey = RawDef::TYPE_NATURE_KEY;
				if (!$typeDataSet->contains(RawDef::TYPE_NATURE_KEY) && $typeDataSet->contains('type')) {
					$natureKey = 'type';
				}
				$nature = $typeDataSet->reqEnum($natureKey, RawDef::getTypeNatures());
				
				if ($nature == RawDef::NATURE_ENTITY) {
					$eiTypeExtractions[$typeId] = $this->createEiTypeExtraction($typeId, $typeDataSet);
				} else {
					$customTypeExtractions[$typeId] = $this->createCustomTypeExtraction($typeId, $typeDataSet);
				}
			} catch (AttributesException $e) {
				throw $this->createSpecException($typeId, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createSpecException($typeId, $e);
			}
		}
		
		return array('eiTypeExtractions' => $eiTypeExtractions, 'customTypeExtractions' => $customTypeExtractions);
	}
	
	
	private function createCustomTypeExtraction($id, DataSet $customSpecDataSet) {
		$extraction = new CustomTypeExtraction($id, $this->moduleNamespace);
		$extraction->setControllerLookupId($customSpecDataSet->getScalar(RawDef::CUSTOM_CONTROLLER_LOOKUP_ID_KEY));
		return $extraction;
	}
	
	private function createEiTypeExtraction($id, DataSet $eiTypeDataSet) {
		$extraction = new EiTypeExtraction($id, $this->moduleNamespace);
		$extraction->setEntityClassName($this->upgradeTypeName($eiTypeDataSet->getString(RawDef::EI_CLASS_KEY)));
		$extraction->setEiMaskExtraction($this->createEiMaskExtraction($eiTypeDataSet));
		$extraction->setDataSourceName($eiTypeDataSet->getString(RawDef::EI_DATA_SOURCE_NAME_KEY, false, null, true));
		
		if (null !== ($nssAttrs = $eiTypeDataSet->getArray(RawDef::EI_NESTED_SET_STRATEGY_KEY, false, null))) {
			$nssDataSet = new DataSet($nssAttrs);
			try {
				$extraction->setNestedSetStrategy(new NestedSetStrategy(
						CrIt::p($nssDataSet->getString(RawDef::EI_NESTED_SET_STRATEGY_LEFT_KEY)),
						CrIt::p($nssDataSet->getString(RawDef::EI_NESTED_SET_STRATEGY_RIGHT_KEY))));
			} catch (\InvalidArgumentException $e) {
				throw new InvalidAttributeException(
						'NestedSetStrategy attribute could not be converted to CriteriaProperty', 0, $e);
			}
		}
		
// 		$extraction->setDefaultEiMaskId($eiTypeDataSet->getString(RawDef::EI_DEFAULT_MASK_ID, false, null, true));
	
		return $extraction;
	}
	
	public function createEiMaskExtraction(DataSet $eiMaskDataSet) {
		$eiMaskExtraction = new EiMaskExtraction();
	
		$label = $eiMaskDataSet->getScalar(RawDef::EI_DEF_LABEL_KEY, false, null, true);
		$eiMaskExtraction->setLabel($label);
	
		$pluralLabel = $eiMaskDataSet->getScalar(RawDef::EI_DEF_PLURAL_LABEL_KEY, false, null, true);
		if ($pluralLabel === null) $pluralLabel = $label;
		$eiMaskExtraction->setPluralLabel($pluralLabel);
		
		$eiMaskExtraction->setIconType($eiMaskDataSet->getScalar(RawDef::EI_DEF_ICON_TYPE_KEY, false, null, true));
	
		$eiMaskExtraction->setIdentityStringPattern(
				$eiMaskDataSet->getString(RawDef::EI_DEF_REPRESENTATION_STRING_PATTERN_KEY, false, null, true));
	
		$eiMaskExtraction->setDraftingAllowed($eiMaskDataSet->optBool(RawDef::EI_DEF_DRAFTING_ALLOWED_KEY, 
				$eiMaskExtraction->isDraftingAllowed()));
	
		$eiMaskExtraction->setPreviewControllerLookupId(
				$eiMaskDataSet->getString(RawDef::EI_DEF_PREVIEW_CONTROLLER_LOOKUP_ID_KEY, false, null, true));
	
	
		$eiPropRawDatas = $eiMaskDataSet->getArray(RawDef::EI_DEF_PROPS_KEY, false, array(),
				TypeConstraint::createSimple('array'));
		if (empty($eiPropRawDatas)) {
			$eiPropRawDatas = $eiMaskDataSet->getArray('fields', false, array(), 
					TypeConstraint::createSimple('array'));
		}
		
		foreach ($eiPropRawDatas as $eiPropId => $fieldRawData) {
			try {
				$eiMaskExtraction->addEiPropExtraction($this->createEiPropExtraction($eiPropId, new DataSet($fieldRawData)));
			} catch (AttributesException $e) {
				throw $this->createEiComponentException('EiProp ' . $eiPropId, $e);
			}
		}
	
		foreach ($eiMaskDataSet->getArray(RawDef::EI_DEF_COMMANDS_KEY, false, array(), 
				TypeConstraint::createSimple('array')) as $eiCommandId => $eiCommandRawData) {
			try {
				$eiMaskExtraction->addEiCommandExtraction($this->createEiComponentExtraction($eiCommandId, 
						new DataSet($eiCommandRawData)));
			} catch (AttributesException $e) {
				throw $this->createEiComponentException('EiCommand ' . $eiCommandId, $e);
			}
		}

		$eiMaskExtraction->setOverviewEiCommandId($eiMaskDataSet->getString(
				RawDef::EI_DEF_OVERVIEW_COMMAND_ID_KEY, false));	
		$eiMaskExtraction->setGenericDetailEiCommandId($eiMaskDataSet->getString(
				RawDef::EI_DEF_ENTRY_DETAIL_COMMAND_ID_KEY, false));	
		$eiMaskExtraction->setGenericEditEiCommandId($eiMaskDataSet->getString(
				RawDef::EI_DEF_ENTRY_EDIT_COMMAND_ID_KEY, false));	
		$eiMaskExtraction->setGenericEditEiCommandId($eiMaskDataSet->getString(
				RawDef::EI_DEF_ENTRY_ADD_COMMAND_ID_KEY, false));	
		
		if (null !== ($filterData = $eiMaskDataSet->getArray(RawDef::EI_DEF_FILTER_DATA_KEY, false, null))) {
			$eiMaskExtraction->setFilterSettingGroup(FilterSettingGroup::create(new DataSet($filterData)));
		}
		
		if (null !== ($defaultSortSettingGroup = $eiMaskDataSet->getScalarArray(RawDef::EI_DEF_DEFAULT_SORT_KEY, false, null))) {
			$eiMaskExtraction->setDefaultSortSettingGroup(SortSettingGroup::create(new DataSet($defaultSortSettingGroup)));
		}
		
		$eiMaskExtraction->setDisplayScheme($this->createDisplayScheme($eiMaskDataSet));	

		return $eiMaskExtraction;
	}
	
	private function upgradeTypeName($typeName) {
	    if (!StringUtils::startsWith('rocket\spec\ei\component', $typeName)) {
	        return $typeName;
	    }
	    
	    return str_replace(
	    		array('rocket\\spec\\ei\\component\\field\\impl', 'rocket\\spec\\ei\\component\\prop\\impl', 'EiField',
	    				'rocket\\spec\\ei\\component\\command\\impl'), 
	    		array('rocket\\impl\\ei\\component\\prop', 'rocket\\impl\\ei\\component\\prop', 'EiProp',
	    				'rocket\\impl\\ei\\component\\command'), 
	    		$typeName);
	}
	
	private function createEiPropExtraction($id, DataSet $dataSet, array $parentIds = array())  {
		$extraction = new EiPropExtraction();
		$extraction->setId($id);
		$extraction->setLabel($dataSet->getScalar(RawDef::EI_FIELD_LABEL_KEY, false, null, true));
		$extraction->setClassName($this->upgradeTypeName($dataSet->getScalar(RawDef::EI_COMPONENT_CLASS_KEY)));
		$extraction->setProps($dataSet->getArray(RawDef::EI_COMPONENT_PROPS_KEY, false));
		$extraction->setEntityPropertyName($dataSet->getString(RawDef::EI_FIELD_ENTITY_PROPERTY_KEY, false, null, true));
		$extraction->setObjectPropertyName($dataSet->getString(RawDef::EI_FIELD_OBJECT_PROPERTY_KEY, false, null, true));
		
		$forkedExtractions = array();
		$eiPropRawDatas = $dataSet->getArray(RawDef::EI_DEF_FORKED_PROPS_KEY, false, array(),
				TypeConstraint::createSimple('array'));
		$parentIds[] = $id;
		foreach ($eiPropRawDatas as $forkedId => $forkedEiPropRawData) {
			try {
				$forkedExtractions[] = $this->createEiPropExtraction($forkedId, new DataSet($forkedEiPropRawData), $parentIds);
			} catch (AttributesException $e) {
				throw $this->createEiComponentException('EiProp ' . implode('.' , array_merge($parentIds, [$id])), $e);
			}
		}
		$extraction->setForkedEiPropExtractions($forkedExtractions);
		
		return $extraction;
	}
	
	private function createEiComponentExtraction($eiCommandId, DataSet $dataSet) {
		$extraction = new EiComponentExtraction();
		$extraction->setId($eiCommandId);
		$extraction->setClassName($this->upgradeTypeName($dataSet->getScalar(RawDef::EI_COMPONENT_CLASS_KEY)));
		$extraction->setProps($dataSet->getArray(RawDef::EI_COMPONENT_PROPS_KEY, false));
		return $extraction;
	}
	
	private function createEiModficatorExtraction(string $eiModificatorId, DataSet $dataSet, 
			TypePath $eiTypePath) {
		$extraction = new EiModificatorExtraction($eiModificatorId, $this->moduleNamespace, $eiTypePath);
		$extraction->setClassName($this->upgradeTypeName($dataSet->getScalar(RawDef::EI_COMPONENT_CLASS_KEY)));
		$extraction->setProps($dataSet->getArray(RawDef::EI_COMPONENT_PROPS_KEY, false));
		return $extraction;
	}	
	
	private function createSpecEiMaskException($eiTypeId, \Exception $previous) {
		throw new InvalidSpecConfigurationException('Spec with id \'' . $eiTypeId 
				. '\' contains invalid EiMask configurations.', 0, $previous);
	}
	
	private function createEiTypeExtensionException($eiMaskId, \Exception $previous) {
		throw new InvalidEiMaskConfigurationException('EiMask with id \'' . $eiMaskId
				. '\' contains invalid configurations.', 0, $previous);
	}
	
	private function createEiModificatorsException(TypePath $typePath, \Exception $previous) {
		throw new InvalidEiComponentConfigurationException('EiModificators for type path \'' 
				. $typePath . '\' have invalid configurations.', 0, $previous);
	}
	
	private function createEiModificatorException(string $id, TypePath $typePath, \Exception $previous) {
		throw new InvalidEiComponentConfigurationException('EiModificator with id \'' . $id
				. '\' for type path \'' . $typePath . '\' contains invalid configurations.', 0, $previous);
	}
	
	/**
	 * @return \rocket\spec\extr\EiTypeExtensionExtraction[][]
	 */
	public function extractEiTypeExtensionGroups() {
		$eiTypeExtensionsKey = RawDef::EI_TYPE_EXTENSIONS_KEY;
		if (!$this->dataSet->contains(RawDef::EI_TYPE_EXTENSIONS_KEY) 
				&& $this->dataSet->contains('eiMasks')) {
			$eiTypeExtensionsKey = 'eiMasks';			
		}
		$dataSet = new DataSet($this->dataSet->getArray($eiTypeExtensionsKey, false));
		
		$eiTypeExtensionExtractionGroups = array();
		foreach ($dataSet->getNames() as $extendedTypePathStr) {
			try {
				$extendedTypePath = TypePath::create($extendedTypePathStr);
				$eiTypeExtensionDataSet = new DataSet($dataSet->getArray($extendedTypePathStr, false));
				$eiTypeExtensionExtractionGroups[$extendedTypePathStr] 
						= $this->createEiTypeExtensionExtractions($extendedTypePath, $eiTypeExtensionDataSet);
			} catch (\InvalidArgumentException $e) {
				throw $this->createSpecEiMaskException($extendedTypePathStr, $e);
			} catch (AttributesException $e) {
				throw $this->createSpecEiMaskException($extendedTypePathStr, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createSpecEiMaskException($extendedTypePathStr, $e);
			}
		}
		
		return $eiTypeExtensionExtractionGroups;
	}
	
	private function createEiTypeExtensionExtractions(TypePath $extendedTypePath, DataSet $eiMasksDataSet): array {
		$eiTypeExtensionExtraction = array();
		
		foreach ($eiMasksDataSet->getNames() as $eiTypeExtensionId) {
			try {
				$eiTypeExtensionExtraction[$eiTypeExtensionId] = $this->createEiTypeExtensionExtraction(
						$extendedTypePath, $eiTypeExtensionId,
						new DataSet($eiMasksDataSet->getArray($eiTypeExtensionId)));
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiTypeExtensionException($eiTypeExtensionId, $e);
			} catch (AttributesException $e) {
				throw $this->createEiTypeExtensionException($eiTypeExtensionId, $e);
			}
		}
		
		return $eiTypeExtensionExtraction;
	}
	
	private function createEiTypeExtensionExtraction(TypePath $extendedTypePath, $id, DataSet $dataSet): EiTypeExtensionExtraction {
		$eiTypeExtensionExtraction = new EiTypeExtensionExtraction($id, $this->moduleNamespace, $extendedTypePath);
		
		$eiTypeExtensionExtraction->setEiMaskExtraction($this->createEiMaskExtraction($dataSet));
		
		return $eiTypeExtensionExtraction;
	}
	
	public function extractEiModificatorGroups() {
		$dataSet = new DataSet($this->dataSet->getArray(RawDef::EI_MODIFICATORS_KEY, false));
		
		$eiModificatorGroups = array();
		foreach ($dataSet->getNames() as $typePathStr) {
			try {
				$typePath = TypePath::create($typePathStr);
				
				$eiModificatorsDataSet = new DataSet($dataSet->getArray($typePathStr, false));
				$eiModificatorGroups[$typePathStr] = $this->createEiModificatorExtractions($eiModificatorsDataSet, $typePath);
			} catch (AttributesException $e) {
				throw $this->createSpecEiMaskException($typePath, $e);
			} catch (InvalidConfigurationException $e) {
				throw $this->createSpecEiMaskException($typePath, $e);
			}
		}
		
		return $eiModificatorGroups;
	}
	
	public function createEiModificatorExtractions(DataSet $eiModificatorsDataSet, TypePath $eiTypePath): array {
		$commonEiModificators = array();
		
		foreach ($eiModificatorsDataSet->getNames() as $modificatorId) {
			try {
				$commonEiModificators[$modificatorId] = $this->createEiModficatorExtraction($modificatorId,
						new DataSet($eiModificatorsDataSet->getArray($modificatorId)), $eiTypePath);
			} catch (InvalidConfigurationException $e) {
				throw $this->createEiModificatorException($modificatorId, $eiTypePath, $e);
			} catch (AttributesException $e) {
				throw $this->createEiModificatorException($modificatorId, $eiTypePath, $e);
			}
		}
		
		return $commonEiModificators;
	}
	
	private function createDisplayScheme(DataSet $dataSet): DisplayScheme {
		$guiOrder = new DisplayScheme();
		
		$guiOrder->setOverviewDisplayStructure($this->extractDisplayStructure(RawDef::OVERVIEW_GUI_FIELD_ORDER_KEY, $dataSet));
		$guiOrder->setBulkyDisplayStructure($this->extractDisplayStructure(RawDef::BULKY_GUI_FIELD_ORDER_KEY, $dataSet));
		$guiOrder->setDetailDisplayStructure($this->extractDisplayStructure(RawDef::DETAIL_GUI_FIELD_ORDER_KEY, $dataSet));
		$guiOrder->setEditDisplayStructure($this->extractDisplayStructure(RawDef::EDIT_GUI_FIELD_ORDER_KEY, $dataSet));
		$guiOrder->setAddDisplayStructure($this->extractDisplayStructure(RawDef::ADD_GUI_FIELD_ORDER_KEY, $dataSet));
		
		if (null !== ($controlIds = $dataSet->getScalarArray(RawDef::EI_DEF_PARTIAL_CONTROL_ORDER_KEY, false))) {
			$guiOrder->setPartialControlOrder(new ControlOrder($controlIds));
		}
		
		if (null !== ($controlIds = $dataSet->getScalarArray(RawDef::EI_DEF_OVERALL_CONTROL_ORDER_KEY, false))) {
			$guiOrder->setOverallControlOrder(new ControlOrder($controlIds));
		}
		
		if (null !== ($controlIds = $dataSet->getScalarArray(RawDef::EI_DEF_ENTRY_CONTROL_ORDER_KEY, false))) {
			$guiOrder->setEntryGuiControlOrder(new ControlOrder($controlIds));
		}
		
		return $guiOrder;
	}
	
	private function extractDisplayStructure($key, DataSet $dataSet) {
		$data = $dataSet->getArray($key, false, null);
		if ($data === null) return null;
		
		try {
			return $this->createDisplayStructure($data);
		} catch (AttributesException $e) {
			throw new InvalidEiMaskConfigurationException('Field contains invalid DisplayStructure configuration: ' 
					. $key, 0, $e);
		}
	}
	
	private function createDisplayStructure(array $data) {
		$displayStructure = new DisplayStructure();
	
		foreach ($data as $key => $fieldId) {
			//Old specs (guiId)
			if (!is_array($fieldId)) {
				$displayStructure->addDefPropPath(DefPropPath::create($fieldId));
				continue;
			}
	
			$displayStructureDataSet = new DataSet($fieldId);
			
			//Old specs (fieldOrder)
			$title = $displayStructureDataSet->getScalar(RawDef::GUI_FIELD_ORDER_GROUP_TITLE_KEY, false);
			if (null !== $title) {
			    $dsa = $displayStructureDataSet->getArray('guiFieldOrder', false, null);
			    if ($dsa === null) {
			        $dsa = $displayStructureDataSet->getArray(RawDef::GUI_FIELD_ORDER_KEY);
			    }
			    $childDisplayStructure = $this->createDisplayStructure($dsa);
			    $groupType = $displayStructureDataSet->optEnum(RawDef::GUI_FIELD_ORDER_GROUP_TYPE_KEY, SiStructureType::groups(),
						SiStructureType::SIMPLE_GROUP);
				if ($groupType === null) {
					$groupType = SiStructureType::SIMPLE_GROUP;
				}
				
				$displayStructure->addDisplayStructure($childDisplayStructure, $groupType, $title);
				continue;
			}
						
			$label = $displayStructureDataSet->getScalar(RawDef::DISPLAY_ITEM_LABEL_KEY, false, null, true);
			$defPropPathStr = $displayStructureDataSet->getScalar(RawDef::DISPLAY_ITEM_GUI_ID_PATH_KEY, false, null, true);
			if (null !== $defPropPathStr) {
				$displayStructure->addDefPropPath(DefPropPath::create($defPropPathStr), 
						$displayStructureDataSet->optEnum(RawDef::DISPLAY_ITEM_GROUP_TYPE_KEY, SiStructureType::all()) ?? SiStructureType::ITEM, 
// 						$displayStructureDataSet->optBool(RawDef::DISPLAY_ITEM_AUTONOMIC_KEY, false),
						Rocket::buildLstr($label, $this->moduleNamespace));
				continue;
			}
			
			$childDisplayStructure = $this->createDisplayStructure(
					$displayStructureDataSet->getArray(RawDef::DISPLAY_ITEM_DISPLAY_STRUCTURE_KEY));
			$displayStructure->addDisplayStructure($childDisplayStructure, 
					$displayStructureDataSet->reqEnum(RawDef::DISPLAY_ITEM_GROUP_TYPE_KEY, SiStructureType::all()),
// 					$displayStructureDataSet->optBool(RawDef::DISPLAY_ITEM_AUTONOMIC_KEY, false),
					Rocket::buildLstr($label, $this->moduleNamespace));
		}
	
		return $displayStructure;
	}
	
	/**
	 * @return \rocket\spec\extr\LaunchPadExtraction[]
	 */
	public function extractLaunchPads() {
		$launchPadsKey = RawDef::LAUNCH_PADS_KEY;
		if (!$this->dataSet->contains(RawDef::LAUNCH_PADS_KEY)
				&& $this->dataSet->contains('menuItems')) {
			$launchPadsKey = 'menuItems';
		}
		
		$launchPadExtractions = array();
		foreach ($this->dataSet->getArray($launchPadsKey, false, array(), 
				TypeConstraint::createArrayLike('array', true)) as $typePathStr => $launchPadRawData) {
					
			$launchPadDataSet = null;
			if ($launchPadRawData !== null) {
				$launchPadDataSet = new DataSet($launchPadRawData);
			}
			
			$launchPadExtractions[$typePathStr] = $this->createLaunchPadExtraction($typePathStr, new DataSet($launchPadRawData));
		}
		return $launchPadExtractions;
	}

	/**
	 * @param string $launchPadId
	 * @param DataSet $specDataSet
	 * @return LaunchPadExtraction
	 */
	private function createLaunchPadExtraction($launchPadId, DataSet $specDataSet) {
		try {
			$launchPadExtraction = new LaunchPadExtraction(TypePath::create($launchPadId), $this->moduleNamespace);
			$launchPadExtraction->setLabel($specDataSet->getString(RawDef::LAUNCH_PAD_LABEL_KEY, false));
			return $launchPadExtraction;
		} catch (\InvalidArgumentException $e) {
			throw $this->createLaunchPadException($launchPadId, $e);
		} catch (AttributesException $e) {
			throw $this->createLaunchPadException($launchPadId, $e);
		} catch (\InvalidArgumentException $e) {
			throw $this->createLaunchPadException($launchPadId, $e);
		}
	}

	private function createLaunchPadException($id, \Exception $previous) {
		throw new InvalidLaunchPadConfigurationException('LaunchPad with following id is invalid configruated: ' . $id,
				0, $previous);
	}
}
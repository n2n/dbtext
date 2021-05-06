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
namespace rocket\impl\ei\component\prop\ci\conf;

use rocket\impl\ei\component\prop\ci\model\ContentItem;
use n2n\util\type\CastUtils;
use n2n\impl\web\dispatch\mag\model\MagCollectionArrayMag;
use rocket\spec\UnknownTypeException;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\impl\ei\component\prop\ci\model\PanelDeclaration;
use n2n\util\type\attrs\DataSet;
use rocket\ei\util\Eiu;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\ArgUtils;

class ContentItemsConfig extends PropConfigAdaption {
	const ATTR_PANELS_KEY = 'panels';
	
	/**
	 * @var PanelDeclaration[]
	 */
	private $panelDeclarations = array();
	
	function __construct() {
		$this->panelDeclarations = array(new PanelDeclaration('main', 'Main', null, 0));
	}
	
	/**
	 * @return bool
	 */
	function hasPanelDeclarations() {
		return !empty($this->panelDeclarations);
	}
	
	/**
	 * @return PanelDeclaration[]
	 */
	function getPanelDeclarations() {
		return $this->panelDeclarations;
	}
	
	/**
	 * @param PanelDeclaration[] $panelDeclarations
	 */
	function setPanelDeclarations(array $panelDeclarations) {
		ArgUtils::valArray($panelDeclarations, PanelDeclaration::class);
		$this->panelDeclarations = $panelDeclarations;
	}
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$ciConfigUtils = null;
		try {
			$ciConfigUtils = CiConfigUtils::createFromN2nContext($eiu->getN2nContext());
		} catch (UnknownTypeException $e) {
			return;
		}
		
		$panelDeclarationMag = new MagCollectionArrayMag('Panels',
				function() use ($ciConfigUtils) {
					return new MagForm($ciConfigUtils->createPanelDeclarationMagCollection(true));
				});
		
		$magCollection->addMag(self::ATTR_PANELS_KEY, $panelDeclarationMag);
		
		$lar = new LenientAttributeReader($dataSet);
// 		if ($lar->contains(self::ATTR_PANELS_KEY)) {
// 			$magValue = $lar->getArray(self::ATTR_PANELS_KEY, array(), TypeConstraint::createArrayLike('array',
// 					false, TypeConstraint::createSimple('scalar')));
			
// 			foreach ($magValue as $magValueField) {
// 				$magValueField[CiConfigUtils::ATTR_GRID_ENABLED_KEY] = isset($magValueField[CiConfigUtils::ATTR_GRID_KEY]);
// 			}
			
// 			if (!empty($magValue)) {
// 				$panelDeclarationMag->setValue($magValue);
// 				return $magDispatchable;
// 			}
// 		}
		
		$magValue = array();
		foreach ($lar->getArray(self::ATTR_PANELS_KEY) as $panelAttrs) {
			$magValue[] = $ciConfigUtils->buildPanelDeclarationMagCollectionValues($panelAttrs);
		}
		$panelDeclarationMag->setValue($magValue);
	}
	
	
		
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$mag = $magCollection->getMagByPropertyName(self::ATTR_PANELS_KEY);
		CastUtils::assertTrue($mag instanceof MagCollectionArrayMag);
		
		$panelDeclarationAttrs = array();
		foreach ($mag->getValue() as $panelValues) {
			$panelDeclarationAttrs[] = CiConfigUtils::buildPanelDeclarationAttrs($panelValues);
		}
		
		if (!empty($panelDeclarationAttrs)) {
			$dataSet->set(self::ATTR_PANELS_KEY, $panelDeclarationAttrs);
		}
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
// 		$this->eiComponent->setContentItemEiType($eiSetupProcess->getEiTypeByClass(
// 				ReflectionUtils::createReflectionClass('rocket\impl\ei\component\prop\ci\model\ContentItem')));
		if ($dataSet->contains(self::ATTR_PANELS_KEY)) {
			$panelDeclarations = array();
			foreach ((array) $dataSet->optArray(self::ATTR_PANELS_KEY) as $panelAttrs) {
				$panelDeclarations[] = CiConfigUtils::createPanelDeclaration($panelAttrs);
			}
			$this->panelDeclarations = $panelDeclarations;
		}
	}

	public function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$level = parent::testCompatibility($propertyAssignation);
		if (CompatibilityLevel::NOT_COMPATIBLE === $level) {
			return $level;
		}

		if ($propertyAssignation->getEntityProperty()->getTargetEntityModel()->getClass()
				->getName() == ContentItem::class) {
			return CompatibilityLevel::COMMON;
		}

		return $level;
	}
}

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
namespace rocket\impl\ei\component\prop\string\conf;

use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\util\StringUtils;
use n2n\persistence\meta\structure\Column;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use n2n\impl\persistence\orm\property\StringEntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;

class StringConfig extends PropConfigAdaption {
	const ATTR_MULTILINE_KEY = 'multiline';
	
	private $multiline = false;
	
	/**
	 * @return bool
	 */
	function isMultiline() {
		return $this->multiline;
	}
	
	/**
	 * @param bool $multiline
	 */
	function setMultiline(bool $multiline) {
		$this->multiline = $multiline;
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_MULTILINE_KEY)) {
			$this->setMultiline($dataSet->reqBool(self::ATTR_MULTILINE_KEY));
		}
	}
	
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$entityProperty = $propertyAssignation->getEntityProperty(false);
		if ($entityProperty !== null && $entityProperty instanceof StringEntityProperty) {
			return CompatibilityLevel::COMMON;
		}
		
		if ($entityProperty !== null && $entityProperty instanceof ScalarEntityProperty) {
			return CompatibilityLevel::SUITABLE;
		}
		
		return CompatibilityLevel::NOT_COMPATIBLE;
	}
	
	private static $multilineNeedles = array('description', 'lead', 'intro', 'content');
	
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		if (StringUtils::contains(self::$multilineNeedles, $this->requirePropertyName(), false)) {
			$dataSet->set(self::ATTR_MULTILINE_KEY, true);
			$dataSet->set(DisplayConfig::ATTR_DISPLAY_IN_OVERVIEW_KEY, false);
		}
	}
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$magCollection->addMag(self::ATTR_MULTILINE_KEY, new BoolMag('Multiline',
				$dataSet->optBool(self::ATTR_MULTILINE_KEY, $this->isMultiline())));
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$multilineMag = $magCollection->getMagByPropertyName(self::ATTR_MULTILINE_KEY);

		$dataSet->set(self::ATTR_MULTILINE_KEY, $multilineMag->getValue());
	}
}

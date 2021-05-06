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
namespace rocket\impl\ei\component\prop\date\conf;

use n2n\l10n\DateTimeFormat;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\impl\ei\component\prop\date\DateTimeEiProp;
use n2n\util\type\CastUtils;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\config\InvalidConfigurationException;
use n2n\util\type\ArgUtils;
 
class DateTimeConfig extends PropConfigAdaption {
	const ATTR_DATE_STYLE_KEY = 'dateStyle';
	const ATTR_TIME_STYLE_KEY = 'timeStyle';
	
	private $dateStyle = DateTimeFormat::STYLE_MEDIUM;
	private $timeStyle = DateTimeFormat::STYLE_NONE;
	
	function getDateStyle() {
		return $this->dateStyle;
	}
	
	function setDateStyle($dateStyle) {
		ArgUtils::valEnum($dateStyle, DateTimeFormat::getStyles());
		$this->dateStyle = $dateStyle;
	}
	
	function getTimeStyle() {
		return $this->timeStyle;
	}
	
	function setTimeStyle($timeStyle) {
		ArgUtils::valEnum($timeStyle, DateTimeFormat::getStyles());
		$this->timeStyle = $timeStyle;
	}
	
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		return CompatibilityLevel::SUITABLE;
	}
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$styles = DateTimeFormat::getStyles();
		$styleOptions = array_combine($styles, $styles);
		
		$lar = new LenientAttributeReader($dataSet);
		
		$magCollection->addMag(self::ATTR_DATE_STYLE_KEY, new EnumMag('Date Style', $styleOptions, 
				$lar->getEnum(self::ATTR_DATE_STYLE_KEY, $styles, $this->getDateStyle()), true));
		$magCollection->addMag(self::ATTR_TIME_STYLE_KEY, new EnumMag('Time Style', $styleOptions, 
				$lar->getEnum(self::ATTR_TIME_STYLE_KEY, $styles, $this->getTimeStyle()), true));
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_DATE_STYLE_KEY)) {
			try {
				$this->setDateStyle($dataSet->get(self::ATTR_DATE_STYLE_KEY));
			} catch (\InvalidArgumentException $e) {
				throw new InvalidConfigurationException('Invalid date style', $e);
			}
		}
		
		if ($dataSet->contains(self::ATTR_TIME_STYLE_KEY)) {
			try {
				$this->setTimeStyle($dataSet->get(self::ATTR_TIME_STYLE_KEY));
			} catch (\InvalidArgumentException $e) {
				throw new InvalidConfigurationException('Invalid time style', $e);
			}
		}
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$dataSet->appendAll($magCollection->readValues(
				array(self::ATTR_DATE_STYLE_KEY, self::ATTR_TIME_STYLE_KEY), true), true);
	}
}

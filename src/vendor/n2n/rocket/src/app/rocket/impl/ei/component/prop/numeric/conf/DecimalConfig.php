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
namespace rocket\impl\ei\component\prop\numeric\conf;

use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\util\type\CastUtils;
use rocket\impl\ei\component\prop\numeric\DecimalEiProp;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\StringMag;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use hangar\api\CompatibilityLevel;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use n2n\impl\persistence\orm\property\FloatEntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;

class DecimalConfig extends PropConfigAdaption {
	const ATTR_DECIMAL_PLACES_KEY = 'decimalPlaces';
// 	const ATTR_PREFIX_KEY = 'prefix';
	
	protected $decimalPlaces = 2;
// 	protected $prefix;
	
	/**
	 * @return int
	 */
	public function getDecimalPlaces() {
	    return $this->decimalPlaces;
	}
	
	/**
	 * @param int $decimalPlaces
	 */
	public function setDecimalPlaces(int $decimalPlaces) {
	    $this->decimalPlaces = $decimalPlaces;
	}
	
// 	/**
// 	 * @return string
// 	 */
// 	public function getPrefix() {
// 	    return $this->prefix;
// 	}
	
// 	/**
// 	 * @param string $prefix
// 	 */
// 	public function setPrefix(string $prefix = null) {
// 	    $this->prefix = $prefix;
// 	}
	
	
	public function getTypeName(): string {
		return 'Decimal';
	}
	
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$entityProperty = $propertyAssignation->getEntityProperty(false);
		if ($entityProperty !== null && $entityProperty instanceof FloatEntityProperty) {
			return CompatibilityLevel::COMMON;
		}
		
		if ($entityProperty !== null && $entityProperty instanceof ScalarEntityProperty) {
			return CompatibilityLevel::SUITABLE;
		}
		
		return CompatibilityLevel::NOT_COMPATIBLE;
	}
	
	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($dataSet);
		
		$magCollection->addMag(self::ATTR_DECIMAL_PLACES_KEY, new NumericMag(
				'Positions after decimal point', $lar->getNumeric(self::ATTR_DECIMAL_PLACES_KEY, 0), true, 0));
// 		$magCollection->addMag(self::ATTR_PREFIX_KEY, new StringMag('Prefix',
// 				$lar->getString(self::ATTR_PREFIX_KEY, false)));
	}
	
	public function setup(Eiu $eiu, DataSet $dataSet) {
		$this->setDecimalPlaces($dataSet->optInt(self::ATTR_DECIMAL_PLACES_KEY, 0));
// 		$this->setPrefix($dataSet->getString(self::ATTR_PREFIX_KEY, false));
	}
	
	public function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$dataSet->appendAll($magCollection->readValues(
				array(self::ATTR_DECIMAL_PLACES_KEY/*, self::ATTR_PREFIX_KEY*/), true), true);
	}
}

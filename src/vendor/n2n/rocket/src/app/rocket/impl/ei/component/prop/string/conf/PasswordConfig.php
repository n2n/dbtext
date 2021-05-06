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

use n2n\persistence\meta\structure\Column;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use hangar\api\CompatibilityLevel;
use n2n\util\StringUtils;
use n2n\impl\persistence\orm\property\StringEntityProperty;

class PasswordConfig extends PropConfigAdaption {
	const ATTR_ALGORITHM_KEY = 'algorithm';
	
	const ALGORITHM_SHA1 = 'sha1';
	const ALGORITHM_MD5 = 'md5';
	const ALGORITHM_BLOWFISH = 'blowfish';
	const ALGORITHM_SHA_256 = 'sha-256';
	
	private $algorithm = self::ALGORITHM_BLOWFISH;
	
	public function getAlgorithm() {
		return $this->algorithm;
	}
	
	public function setAlgorithm(string $algorithm) {
		$this->algorithm = $algorithm;
	}
	
	public function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
	}

	public function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_ALGORITHM_KEY)) {
			$this->setAlgorithm($dataSet->reqEnum(self::ATTR_ALGORITHM_KEY, self::getAlgorithms()));
		}
	}

	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$algorithms = self::getAlgorithms();
		$magCollection->addMag(self::ATTR_ALGORITHM_KEY, new EnumMag('Algortithm', 
				array_combine($algorithms, $algorithms), 
				$dataSet->optString(self::ATTR_ALGORITHM_KEY, $this->getAlgorithm()), true));
	}
	
	public static function getAlgorithms() {
		return array(self::ALGORITHM_BLOWFISH, self::ALGORITHM_SHA1, self::ALGORITHM_MD5, self::ALGORITHM_SHA_256);
	}

	public function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$algorithmMag = $magCollection->getMagByPropertyName(self::ATTR_ALGORITHM_KEY);
		
		$dataSet->set(self::ATTR_ALGORITHM_KEY, $algorithmMag->getValue());
	}

	public function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$entityProperty = $propertyAssignation->getEntityProperty(false);
		
		if (StringUtils::endsWith('assword', $propertyAssignation->getObjectPropertyAccessProxy(true)->getPropertyName())) {
			return CompatibilityLevel::COMMON;
		}
		
		if ($entityProperty !== null && $entityProperty instanceof StringEntityProperty) {
			return CompatibilityLevel::SUITABLE;
		}
		
		return CompatibilityLevel::NOT_COMPATIBLE;
	}

}

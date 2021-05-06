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

use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\impl\web\dispatch\mag\model\NumericMag;

class StringArrayConfig extends PropConfigAdaption {
	const ATTR_MIN_KEY = 'min';
	const ATTR_MAX_KEY = 'max';
	
	private $min = 0;
	private $max = null;
	
	public function getMin() {
		return $this->min;
	}
	
	public function setMin(int $min) {
		$this->min = $min;
	}
	
	public function getMax() {
		return $this->max;
	}
	
	public function setMax($max) {
		$this->max = $max;
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_MIN_KEY)) {
			$this->setMin($dataSet->reqInt(self::ATTR_MIN_KEY));
		}

		if ($dataSet->contains(self::ATTR_MAX_KEY)) {
			$this->setMax($dataSet->reqInt(self::ATTR_MAX_KEY));
		}
	}
	
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		if ($propertyAssignation->hasObjectPropertyAccessProxy() 
				&& $propertyAssignation->getObjectPropertyAccessProxy()->getConstraint()->isArrayLike()) {
			return CompatibilityLevel::COMPATIBLE;
		}
		
		return CompatibilityLevel::NOT_COMPATIBLE;
	}
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$magCollection->addMag(self::ATTR_MIN_KEY, new NumericMag('Min values',
				$dataSet->optInt(self::ATTR_MIN_KEY, $this->getMin())));
		$magCollection->addMag(self::ATTR_MAX_KEY, new NumericMag('Max values',
				$dataSet->optInt(self::ATTR_MAX_KEY, $this->getMin())));
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$minMag = $magCollection->getMagByPropertyName(self::ATTR_MIN_KEY);
		$dataSet->set(self::ATTR_MIN_KEY, $minMag->getValue());

		$maxMag = $magCollection->getMagByPropertyName(self::ATTR_MAX_KEY);
		$dataSet->set(self::ATTR_MAX_KEY, $maxMag->getValue());
	}
}

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

use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use n2n\impl\persistence\orm\property\IntEntityProperty;
use n2n\impl\persistence\orm\property\ScalarEntityProperty;

class IntegerConfig extends PropConfigAdaption {
	
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$entityProperty = $propertyAssignation->getEntityProperty(false);
		if ($entityProperty !== null && $entityProperty instanceof IntEntityProperty) {
			return CompatibilityLevel::COMMON;
		}
		
		if ($entityProperty !== null && $entityProperty instanceof ScalarEntityProperty) {
			return CompatibilityLevel::SUITABLE;
		}
		
		return CompatibilityLevel::NOT_COMPATIBLE;
	}
	
	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
	}

	public function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
	}

	public function setup(Eiu $eiu, DataSet $dataSet) {
	}

}

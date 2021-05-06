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
namespace rocket\impl\ei\component\prop\bool\conf;

use rocket\impl\ei\component\prop\bool\OnlineEiProp;
use rocket\impl\ei\component\prop\bool\command\OnlineEiCommand;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\persistence\meta\structure\Column;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;

class OnlineConfig extends PropConfigAdaption {
	const COMMON_ONLINE_PROP_NAME = 'online';

	private $onlineEiProp;
	
	function __construct(OnlineEiProp $onlineEiProp) {
		$this->onlineEiProp = $onlineEiProp;
	}
	
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$level = parent::testCompatibility($propertyAssignation);
		
		if (!$level) return $level;
		
		if ($this->requirePropertyName() == self::COMMON_ONLINE_PROP_NAME) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		$dataSet->set(DisplayConfig::ATTR_DISPLAY_IN_ADD_VIEW_KEY, false);
		$dataSet->set(DisplayConfig::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, false);
		$dataSet->set(DisplayConfig::ATTR_DISPLAY_IN_OVERVIEW_KEY, false);
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		$onlineEiCommand = new OnlineEiCommand();
		$onlineEiCommand->setOnlineEiProp($this->onlineEiProp);
		
		$eiu->mask()->addEiCommand($onlineEiCommand, true);
	}
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
	}

	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
	}

}

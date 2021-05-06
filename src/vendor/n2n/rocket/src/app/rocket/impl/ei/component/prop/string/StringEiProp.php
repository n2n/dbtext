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
namespace rocket\impl\ei\component\prop\string;

use rocket\ei\util\Eiu;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\string\conf\StringConfig;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\ei\util\factory\EifGuiField;

class StringEiProp extends AlphanumericEiProp {
	
	private $stringConfig;
	
	function __construct() {
		parent::__construct();
		
		$this->stringConfig = new StringConfig();
	}
	
	function prepare() {
		parent::prepare();
		$this->getConfigurator()
				->setDefaultCompatibilityLevel(CompatibilityLevel::SUITABLE)
				->addAdaption($this->stringConfig);
	}
	
	function createOutEifGuiField(Eiu $eiu): EifGuiField  {
		return $eiu->factory()->newGuiField(
				SiFields::stringOut($eiu->field()->getValue())
						->setMultiline($this->stringConfig->isMultiline()));
	}

	function createInEifGuiField(Eiu $eiu): EifGuiField {
		$addonConfig = $this->getAddonConfig();
		
		$siField = SiFields::stringIn($eiu->field()->getValue())
				->setMandatory($this->getEditConfig()->isMandatory())
				->setMinlength($this->getAlphanumericConfig()->getMinlength())
				->setMaxlength($this->getAlphanumericConfig()->getMaxlength())
				->setMultiline($this->stringConfig->isMultiline())
				->setPrefixAddons($addonConfig->getPrefixSiCrumbGroups())
				->setSuffixAddons($addonConfig->getSuffixSiCrumbGroups())
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
		
		return $eiu->factory()->newGuiField($siField)
				->setSaver(function () use ($siField, $eiu) { 
					$eiu->field()->setValue($siField->getValue()); 
				});
	}
}

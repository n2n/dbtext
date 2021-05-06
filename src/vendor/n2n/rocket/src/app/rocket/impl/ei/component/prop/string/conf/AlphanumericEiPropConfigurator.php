<?php
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\impl\ei\component\prop\string\conf;

// use n2n\core\container\N2nContext;
// use n2n\impl\web\dispatch\mag\model\NumericMag;
// use n2n\util\ex\IllegalStateException;
// use rocket\ei\component\EiSetup;
// use rocket\impl\ei\component\prop\string\AlphanumericEiProp;
// use n2n\web\dispatch\mag\MagDispatchable;
// use n2n\persistence\meta\structure\Column;
// use n2n\persistence\meta\structure\StringColumn;
// use n2n\util\type\attrs\LenientAttributeReader;
// use rocket\impl\ei\component\prop\meta\config\AddonConfig;
// use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;

// class AlphanumericConfig extends PropConfigAdaption {
// 	const ATTR_MINLENGTH_KEY = 'minlength';
// 	const ATTR_MAXLENGTH_KEY = 'maxlength';
	
// 	function __construct(AlphanumericEiProp $alphanumericEiProp) {
// 		parent::__construct($alphanumericEiProp);
		
// 		$this->autoRegister($alphanumericEiProp);
// 	}
	
// 	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
// 		parent::initAutoEiPropAttributes($n2nContext, $column);
		
// 		if ($column instanceof StringColumn) {
// 			$this->dataSet->set(self::ATTR_MAXLENGTH_KEY, $column->getLength());
// 		}
// 	}
	
// 	function setup(Eiu $eiu, DataSet $dataSet) {
// 		parent::setup($eiSetupProcess);
		
// 		IllegalStateException::assertTrue($this->eiComponent instanceof AlphanumericEiProp);
		
// 		if ($this->dataSet->contains(self::ATTR_MAXLENGTH_KEY)) {
// 			$this->eiComponent->setMaxlength($this->dataSet->optInt(self::ATTR_MAXLENGTH_KEY, null));
// 		}
		
// 		if ($this->dataSet->contains(self::ATTR_MINLENGTH_KEY)) {
// 			$this->eiComponent->setMinlength($this->dataSet->optInt(self::ATTR_MINLENGTH_KEY, null));
// 		}
		
// 		$this->eiComponent->setAddonConfig(AddonConfig::setup($this->dataSet));
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\impl\ei\component\prop\string\conf\AlphanumericEiPropConfigurator::createMagDispatchable($n2nContext)
// 	 * @return MagDispatchable
// 	 */
// 	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
// 		$magDispatchable = parent::createMagDispatchable($n2nContext);

// 		$lar = new LenientAttributeReader($this->dataSet);
		
// 		IllegalStateException::assertTrue($this->eiComponent instanceof AlphanumericEiProp);
		
// 		$magDispatchable->getMagCollection()->addMag(self::ATTR_MINLENGTH_KEY, new NumericMag('Minlength', 
// 				$lar->getInt(self::ATTR_MINLENGTH_KEY, $this->eiComponent->getMinlength())));
		
// 		$magDispatchable->getMagCollection()->addMag(self::ATTR_MAXLENGTH_KEY, new NumericMag('Maxlength',
// 				$lar->getInt(self::ATTR_MAXLENGTH_KEY, $this->eiComponent->getMaxlength())));
		
// 		AddonConfig::mag($magDispatchable->getMagCollection(), $this->dataSet);
		
// 		return $magDispatchable;
// 	}
	
// 	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
// 		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
// 		$magCollection = $magDispatchable->getMagCollection();
		
// 		$this->dataSet->set(self::ATTR_MINLENGTH_KEY,
// 				$magCollection->getMagByPropertyName(self::ATTR_MINLENGTH_KEY)->getValue());
		
// 		$this->dataSet->set(self::ATTR_MAXLENGTH_KEY,
// 				$magCollection->getMagByPropertyName(self::ATTR_MAXLENGTH_KEY)->getValue());
		
// 		AddonConfig::save($magDispatchable->getMagCollection(), $this->dataSet);
// 	}
	
// }

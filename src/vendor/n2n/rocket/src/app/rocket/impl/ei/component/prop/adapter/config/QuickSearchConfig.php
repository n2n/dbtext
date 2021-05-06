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
namespace rocket\impl\ei\component\prop\adapter\config;

use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\DataSet;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\ei\util\Eiu;
use n2n\impl\web\dispatch\mag\model\BoolMag;

class QuickSearchConfig extends PropConfigAdaption {
	const ATTR_QUICK_SEARCHABLE_KEY = 'quickSearchable';
	
	private $quickSearchable = true;
	
	/**
	 * @param bool $quickSearchable
	 */
	function __construct(bool $quickSearchable = true) {
		$this->quickSearchable = $quickSearchable;
	}
	
	/**
	 * @return bool
	 */
	function isQuickSerachable() {
		return $this->quickSearchable;
	}
	
	/**
	 * @return bool
	 */
	function setQuickSerachable(bool $quickSearchable) {
		$this->quickSearchable = $quickSearchable;
	}
	
	function mag(Eiu $eiu, DataSet $ds, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($ds);
		
		$magCollection->addMag(self::ATTR_QUICK_SEARCHABLE_KEY, 
				new BoolMag('Quicksearchable', 
						$lar->getBool(self::ATTR_QUICK_SEARCHABLE_KEY, $this->quickSearchable)));
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $ds) {
		$ds->set(self::ATTR_QUICK_SEARCHABLE_KEY, 
				$magCollection->getMagByPropertyName(self::ATTR_QUICK_SEARCHABLE_KEY)->getValue());
	}
	
	/**
	 * @param DataSet $ds
	 * @return \rocket\impl\ei\component\prop\meta\config\AddonConfig
	 */
	function setup(Eiu $eiu, DataSet $ds) {
		$this->quickSearchable = $ds->optBool(self::ATTR_QUICK_SEARCHABLE_KEY, $this->quickSearchable);
	}		
}
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

use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\persistence\meta\structure\Column;
use n2n\persistence\meta\structure\StringColumn;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\util\type\attrs\DataSet;
use rocket\ei\util\Eiu;
use n2n\web\dispatch\mag\MagCollection;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;

class AlphanumericConfig extends PropConfigAdaption {
	const ATTR_MINLENGTH_KEY = 'minlength';
	const ATTR_MAXLENGTH_KEY = 'maxlength';
	
	/**
	 * @var int|null
	 */
	private $minlength;
	/**
	 * @var int|null
	 */
	private $maxlength;
	
	/**
	 * @return int|null
	 */
	function getMinlength() {
		return $this->minlength;
	}
	
	/**
	 * @param int|null $minlength
	 */
	function setMinlength(?int $minlength) {
		$this->minlength = $minlength;
	}
	
	/**
	 * @return int|null
	 */
	function getMaxlength() {
		return $this->maxlength;
	}
	
	/**
	 * @param int|null $maxlength
	 */
	function setMaxlength(?int $maxlength) {
		$this->maxlength = $maxlength;
	}
	
	/**
	 * @param Eiu $eiu
	 * @param Column $column
	 */
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		if ($column instanceof StringColumn) {
			$dataSet->set(self::ATTR_MAXLENGTH_KEY, $column->getLength());
		}
	}
	
	/**
	 * @param Eiu $eiu
	 * @param DataSet $dataSet
	 */
	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_MAXLENGTH_KEY)) {
			$this->setMaxlength($dataSet->optInt(self::ATTR_MAXLENGTH_KEY, null));
		}
		
		if ($dataSet->contains(self::ATTR_MINLENGTH_KEY)) {
			$this->setMinlength($dataSet->optInt(self::ATTR_MINLENGTH_KEY, null));
		}
	}
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($dataSet);
		
		$magCollection->addMag(self::ATTR_MINLENGTH_KEY, new NumericMag('Minlength', 
				$lar->getInt(self::ATTR_MINLENGTH_KEY, $this->getMinlength())));
		
		$magCollection->addMag(self::ATTR_MAXLENGTH_KEY, new NumericMag('Maxlength',
				$lar->getInt(self::ATTR_MAXLENGTH_KEY, $this->getMaxlength())));
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$dataSet->set(self::ATTR_MINLENGTH_KEY,
				$magCollection->getMagByPropertyName(self::ATTR_MINLENGTH_KEY)->getValue());
		
		$dataSet->set(self::ATTR_MAXLENGTH_KEY,
				$magCollection->getMagByPropertyName(self::ATTR_MAXLENGTH_KEY)->getValue());
	}
}

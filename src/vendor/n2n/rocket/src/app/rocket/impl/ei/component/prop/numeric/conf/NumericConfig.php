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
use n2n\persistence\meta\structure\Column;
use n2n\persistence\meta\structure\IntegerColumn;
use n2n\util\type\attrs\LenientAttributeReader;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use n2n\util\type\attrs\DataSet;
use rocket\ei\util\Eiu;
use n2n\web\dispatch\mag\MagCollection;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;

class NumericConfig extends PropConfigAdaption {
	const ATTR_MIN_VALUE_KEY = 'minValue';
	const ATTR_MAX_VALUE_KEY = 'maxValue';
	
	protected $minValue = null;
	protected $maxValue = null;
	
	/**
	 * @return int
	 */
	function getMinValue() {
	    return $this->minValue;
	}
	
	/**
	 * @param int $minValue
	 * @return \rocket\impl\ei\component\prop\numeric\conf\NumericConfig
	 */
	function setMinValue(?float $minValue) {
	    $this->minValue = $minValue;
	    return $this;
	}
	
	/**
	 * @return float
	 */
	function getMaxValue() {
	    return $this->maxValue;
	}
	
	/**
	 * @param int $maxValue
	 * @return \rocket\impl\ei\component\prop\numeric\conf\NumericConfig
	 */
	function setMaxValue(?float $maxValue) {
	    $this->maxValue = $maxValue;
	    return $this;
	}
	
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		if ($this->isGeneratedId()) {
			$dataSet->set(DisplayConfig::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, false);
			$dataSet->set(DisplayConfig::ATTR_DISPLAY_IN_ADD_VIEW_KEY, false);
			$dataSet->set(EditConfig::ATTR_READ_ONLY_KEY, true);
		}
		
		if ($column instanceof IntegerColumn) {
			$dataSet->set(NumericConfig::ATTR_MIN_VALUE_KEY, $column->getMinValue());
			$dataSet->set(NumericConfig::ATTR_MAX_VALUE_KEY, $column->getMaxValue());
		}
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		if (null !== ($minValue = $dataSet->optNumeric(self::ATTR_MIN_VALUE_KEY))) {
			$this->setMinValue($minValue);
		}
		
		if (null !== ($maxValue = $dataSet->optNumeric(self::ATTR_MAX_VALUE_KEY))) {
			$this->setMaxValue($maxValue);
		}
	}
	
	protected function isGeneratedId(): bool {
		$entityProperty = $this->getPropertyAssignation()->getEntityProperty(false);
		if ($entityProperty === null) return false;
		
		$idDef = $entityProperty->getEntityModel()->getIdDef();
		return $idDef->isGenerated() && $idDef->getEntityProperty() === $entityProperty;
	}
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($dataSet);
		
		$magCollection->addMag(self::ATTR_MIN_VALUE_KEY, new NumericMag('Min Value',
				$lar->getNumeric(self::ATTR_MIN_VALUE_KEY, $this->getMinValue())));
		$magCollection->addMag(self::ATTR_MAX_VALUE_KEY, new NumericMag('Max Value',
				$lar->getNumeric(self::ATTR_MAX_VALUE_KEY, $this->getMaxValue())));
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		if (null !== ($minValue = $magCollection->readValue(self::ATTR_MIN_VALUE_KEY))) {
			$dataSet->set(self::ATTR_MIN_VALUE_KEY, $minValue);
		}
		
		if (null !== ($maxValue = $magCollection->readValue(self::ATTR_MAX_VALUE_KEY))) {
			$dataSet->set(self::ATTR_MAX_VALUE_KEY, $maxValue);
		}
	}
}

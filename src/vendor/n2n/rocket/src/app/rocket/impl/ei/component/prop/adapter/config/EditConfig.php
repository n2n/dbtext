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
use n2n\util\type\attrs\InvalidAttributeException;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\util\type\attrs\DataSet;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\ei\util\Eiu;
use n2n\persistence\meta\structure\Column;

class EditConfig extends PropConfigAdaption {
	protected $constant = false;
	protected $readOnly = false;
	protected $mandatory = false;
	
	protected $constantChoosable = true;
	protected $readOnlyChoosable = true;
	protected $mandatoryChoosable = true;
	protected $autoMandatoryCheck = true;
	
	/**
	 * @return bool
	 */
	function isConstant() {
		return $this->constant;
	}
	
	/**
	 * @param bool $constant
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function setConstant(bool $constant) {
		$this->constant = $constant;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isReadOnly(): bool {
		return $this->readOnly;
	}
	
	/**
	 * @param bool $readOnly
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function setReadOnly(bool $readOnly) {
		$this->readOnly = (bool) $readOnly;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isMandatory(): bool {
		return $this->mandatory;
	}
	
	/**
	 * @param bool $mandatory
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function setMandatory(bool $mandatory) {
		$this->mandatory = $mandatory;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isConstantChoosable() {
		return $this->constantChoosable;
	}

	/**
	 * @param bool $constantChoosable
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function setConstantChoosable(bool $constantChoosable) {
		$this->constantChoosable = $constantChoosable;
		return $this;
	}

	/**
	 * @return bool
	 */
	function isReadOnlyChoosable() {
		return $this->readOnlyChoosable;
	}

	/**
	 * @param bool $readOnlyChoosable
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function setReadOnlyChoosable(bool $readOnlyChoosable) {
		$this->readOnlyChoosable = $readOnlyChoosable;
		return $this;
	}

	/**
	 * @return bool
	 */
	function isMandatoryChoosable() {
		return $this->mandatoryChoosable;
	}

	/**
	 * @param bool $mandatoryChoosable
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function setMandatoryChoosable(bool $mandatoryChoosable) {
		$this->mandatoryChoosable = $mandatoryChoosable;
		return $this;
	}

	/**
	 * @return bool
	 */
	function isAutoMandatoryCheck() {
		return $this->autoMandatoryCheck;
	}

	/**
	 * @param bool $autoMandatoryCheck
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function setAutoMandatoryCheck(bool $autoMandatoryCheck) {
		$this->autoMandatoryCheck = $autoMandatoryCheck;
		return $this;
	}
	
	const ATTR_CONSTANT_KEY = 'constant';
	const ATTR_READ_ONLY_KEY = 'readOnly';
	const ATTR_MANDATORY_KEY = 'mandatory';
	
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		if ($this->mandatoryChoosable && $this->autoMandatoryCheck /*&& $this->mandatoryRequired()*/) {
			$dataSet->set(self::ATTR_MANDATORY_KEY, true);
		}
	}
	
	/**
	 * @param DataSet $dataSet
	 * @throws InvalidAttributeException
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($this->constantChoosable && $dataSet->contains(self::ATTR_CONSTANT_KEY)) {
			$this->setConstant($dataSet->reqBool(self::ATTR_CONSTANT_KEY));
		}
		
		if ($this->readOnlyChoosable && $dataSet->contains(self::ATTR_READ_ONLY_KEY)) {
			$this->setReadOnly($dataSet->reqBool(self::ATTR_READ_ONLY_KEY));
		}
		
		if ($this->mandatoryChoosable) {
			if ($dataSet->contains(self::ATTR_MANDATORY_KEY)) {
				$this->setMandatory($dataSet->reqBool(self::ATTR_MANDATORY_KEY));
			}
			
// 			if (!$this->isMandatory() && $this->mandatoryChoosable && $this->autoMandatoryCheck
// 					&& $this->mandatoryRequired()) {
// 				throw new InvalidAttributeException(self::ATTR_MANDATORY_KEY . ' must be true because '
// 						. $this->getPropertyAssignation()->getObjectPropertyAccessProxy(true)
// 						. ' does not allow null value.');
// 			}
		}
		
		return $this;
	}
	
	/**
	 * @param DataSet $dataSet
	 * @param MagCollection $magCollection
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($dataSet);
		
		if ($this->constantChoosable) {
			$magCollection->addMag(self::ATTR_CONSTANT_KEY, new BoolMag('Constant',
					$lar->getBool(self::ATTR_CONSTANT_KEY, $this->isConstant())));
		}
		
		if ($this->readOnlyChoosable) {
			$magCollection->addMag(self::ATTR_READ_ONLY_KEY, new BoolMag('Read only',
					$lar->getBool(self::ATTR_READ_ONLY_KEY, $this->isReadOnly())));
		}
		
		if ($this->mandatoryChoosable) {
			$magCollection->addMag(self::ATTR_MANDATORY_KEY, new BoolMag('Mandatory',
					$lar->getBool(self::ATTR_MANDATORY_KEY, $this->isMandatory())));
		}
		
		return $this;
	}
	
	/**
	 * @param MagCollection $magCollection
	 * @param DataSet $dataSet
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig
	 */
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$dataSet->remove(self::ATTR_CONSTANT_KEY, self::ATTR_READ_ONLY_KEY, self::ATTR_MANDATORY_KEY);
		
		if ($this->constantChoosable) {
			$dataSet->set(self::ATTR_CONSTANT_KEY, $magCollection->readValue(self::ATTR_CONSTANT_KEY));
		}
		
		if ($this->readOnlyChoosable) {
			$dataSet->set(self::ATTR_READ_ONLY_KEY, $magCollection->readValue(self::ATTR_READ_ONLY_KEY));
		}
		
		if ($this->mandatoryChoosable) {
			$dataSet->set(self::ATTR_MANDATORY_KEY, $magCollection->readValue(self::ATTR_MANDATORY_KEY));
		}
		
		return $this;
	}
}

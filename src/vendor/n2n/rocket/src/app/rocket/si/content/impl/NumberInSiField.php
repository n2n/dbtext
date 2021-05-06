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
namespace rocket\si\content\impl;

use n2n\util\type\attrs\DataSet;
use rocket\si\content\impl\meta\AddonsSiFieldTrait;

class NumberInSiField extends InSiFieldAdapter {
	use AddonsSiFieldTrait;
	
	/**
	 * @var float|null
	 */
	private $value;
	/**
	 * @var float|null
	 */
	private $min = null;
	/**
	 * @var bool
	 */
	private $max = null;
	/**
	 * @var float
	 */
	private $step = 1;
	/**
	 * @var float|null
	 */
	private $arrowStep = 1;
	/**
	 * @var bool
	 */
	private $fixed = false;
	/**
	 * @var bool
	 */
	private $mandatory = false;

	/**
	 * @param float|null $value
	 */
	function __construct(?float $value) {
		$this->value = $value;	
	}
	
	/**
	 * @param float|null $value
	 * @return \rocket\si\content\impl\NumberInSiField
	 */
	function setValue(?float $value) {
		$this->value = $value ;
		return $this;
	}
	
	/**
	 * @return float|null
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * @param int $min
	 * @return \rocket\si\content\impl\NumberInSiField
	 */
	function setMin(?float $min) {
		$this->min = $min;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int $max
	 * @return \rocket\si\content\impl\NumberInSiField
	 */
	function setMax(?float $max) {
		$this->max = $max;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getMax() {
		return $this->max;
	}
	
	/**
	 * @return float
	 */
	function getStep() {
		return $this->step;
	}
	
	/**
	 * @param float $step
	 */
	function setStep(float $step) {
		$this->step = $step;
		return $this;
	}
	
	/**
	 * @return float|null
	 */
	function getArrowStep() {
		return $this->arrowStep;
	}
	
	/**
	 * @param bool $fixed
	 * @return \rocket\si\content\impl\NumberInSiField
	 */
	function setFixed(bool $fixed) {
		$this->fixed = $fixed;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isFixed() {
		return $this->fixed;
	}
	
	/**
	 * @param float|null $arrowStep
	 * @return \rocket\si\content\impl\NumberInSiField
	 */
	function setArrowStep(?float $arrowStep) {
		$this->arrowStep = $arrowStep;
		return $this;
	}
	
	/**
	 * @param bool $mandatory
	 * @return \rocket\si\content\impl\NumberInSiField
	 */
	function setMandatory(bool $mandatory) {
		$this->mandatory = $mandatory;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isMandatory() {
		return $this->mandatory;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'number-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'value' => $this->value,
			'min' => $this->min,
			'max' => $this->max,
			'mandatory' => $this->mandatory,
			'step' => $this->step,
			'arrowStep' => $this->arrowStep,
			'fixed' => $this->fixed,
			'prefixAddons' => $this->prefixAddons,
			'suffixAddons' => $this->suffixAddons,
			'messages' => $this->getMessageStrs()
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$this->value = (new DataSet($data))->optNumeric('value');
	}
}

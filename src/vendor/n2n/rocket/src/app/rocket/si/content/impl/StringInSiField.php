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

class StringInSiField extends InSiFieldAdapter {
	use AddonsSiFieldTrait;
	
	/**
	 * @var string|null
	 */
	private $value;
	/**
	 * @var int|null
	 */
	private $minlength;
	/**
	 * @var int|null
	 */
	private $maxlength;
	/**
	 * @var bool
	 */
	private $multiline = false;
	/**
	 * @var bool
	 */
	private $mandatory = false;
	
	function __construct(?string $value) {
		$this->value = $value;	
	}
	
	/**
	 * @param string|null $value
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setValue(?string $value) {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * @param int|null $minlength
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setMinlength(?int $minlength) {
		$this->minlength = $minlength;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMinlength() {
		return $this->minlength;
	}
	
	/**
	 * @param int|null $maxlength
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setMaxlength(?int $maxlength) {
		$this->maxlength = $maxlength;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMaxlength() {
		return $this->maxlength;
	}
	
	/**
	 * @param bool $multiline
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setMultiline(bool $multiline) {
		$this->multiline = $multiline;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isMultiline() {
		return $this->multiline;
	}
	
	/**
	 * @param bool $mandatory
	 * @return \rocket\si\content\impl\StringInSiField
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
		return 'string-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'value' => $this->value,
			'minlength' => $this->minlength,
			'maxlength' => $this->maxlength,
			'multiline' => $this->multiline,
			'mandatory' => $this->mandatory,
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
		$this->value = (new DataSet($data))->reqString('value', true);
	}
}

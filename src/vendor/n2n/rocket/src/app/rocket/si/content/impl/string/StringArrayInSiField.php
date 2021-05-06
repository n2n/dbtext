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
namespace rocket\si\content\impl\string;

use n2n\util\type\attrs\DataSet;
use rocket\si\content\impl\InSiFieldAdapter;
use n2n\util\type\ArgUtils;

class StringArrayInSiField extends InSiFieldAdapter {
	/**
	 * @var string[]
	 */
	private $values;
	/**
	 * @var int
	 */
	private $min = 0;
	/**
	 * @var int|null
	 */
	private $max;
	
	function __construct(array $values) {
		$this->setValues($values);
	}
	
	/**
	 * @param string|null $value
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	function setValues(array $values) {
		ArgUtils::valArray($values, 'string', false, 'values');
		$this->values = $values;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	function getValues() {
		return $this->values;
	}
	
	/**
	 * @param int $minlength
	 * @return StringArrayInSiField
	 */
	function setMin(int $min) {
		$this->min = $min;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int|null $maxlength
	 * @return StringArrayInSiField
	 */
	function setMax(?int $max) {
		$this->max = $max;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMax() {
		return $this->max;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'string-array-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'values' => $this->values,
			'min' => $this->min,
			'max' => $this->max,
			'messages' => $this->getMessageStrs()
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$dataSet = new DataSet($data);
		$this->setValues($dataSet->reqArray('values', 'string'));
	}
}

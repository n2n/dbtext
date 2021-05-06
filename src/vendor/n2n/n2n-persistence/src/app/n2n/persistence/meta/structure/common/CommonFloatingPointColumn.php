<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\persistence\meta\structure\common;

use n2n\persistence\meta\structure\Column;

use n2n\persistence\meta\structure\FloatingPointColumn;

class CommonFloatingPointColumn extends ColumnAdapter implements FloatingPointColumn {

	//IEEE 754 - Single Precision
	const SINGLE_PRECISION_BITS_MANTISSA = 23;
	const SINGLE_PRECISION_BITS_EXPONENT = 8;
	const SINGLE_PRECISION_BIAS = 127;
	const SINGLE_PRECISION_SIZE = 32;
	
	//IEEE 754 - Double Precision
	const DOUBLE_PRECISION_BITS_MANTISSA = 52;
	const DOUBLE_PRECISION_BITS_EXPONENT = 11;
	const DOUBLE_PRECISION_BIAS = 1023;
	const DOUBLE_PRECISION_SIZE = 64;

	const SIZE_MAX = self::DOUBLE_PRECISION_SIZE;

	private $size;
	private $maxValue;
	private $minValue;
	private $maxExponent;
	private $minExponent;

	public function __construct($name, $size) {
		parent::__construct($name);
		$this->setSize($size);
	}

	public function getSize() {
		return $this->size;
	}

	private function setSize($size) {
		$this->size = $this->purifySize($size);
			
		// all 1 - bias (cause the because zero is reserved for 0) => last bit is 1
		$this->minExponent = 1 - $this->getMaxExp($size);
		$this->maxExponent = $this->getMaxExp($size);
		// Formula is m * b ^ e ; b = 2; see http://de.wikipedia.org/wiki/IEEE_754
		$this->maxValue = doubleval($this->getMaxMantissa($size) * pow(2, $this->maxExponent));
		$this->minValue = $this->maxValue * (-1);
	}

	public function getMaxValue() {
		return $this->maxValue;
	}

	public function getMinValue() {
		return $this->minValue;
	}

	public function getMaxExponent() {
		return $this->maxExponent;
	}

	public function getMinExponent() {
		return $this->minExponent;
	}
	
	public function copy($newColumnName = null) {
		if (is_null($newColumnName)) {
			$newColumnName = $this->getName();
		}
		$newColumn = new self($newColumnName, $this->getSize());
		$newColumn->applyCommonAttributes($this);
		return $newColumn;
	}
	
	public function equalsType(Column $column, $ignoreNull = false) {
		return parent::equalsType($column, $ignoreNull)
				&& $column instanceof CommonFloatingPointColumn
				&& $column->getSize() === $this->getSize()
				&& $column->getMinValue() === $this->getMinValue()
				&& $column->getMaxValue() === $this->getMaxValue();
	}
	
	
	protected function getBitsMantissa($size) {
		if ($size <= self::SINGLE_PRECISION_SIZE) {
			return self::SINGLE_PRECISION_BITS_MANTISSA;
		}
		return self::DOUBLE_PRECISION_BITS_MANTISSA;
	}
	
	protected function getBitsExponent($size) {
		if ($size <= self::SINGLE_PRECISION_SIZE) {
			return self::SINGLE_PRECISION_BITS_EXPONENT;
		}
		return self::DOUBLE_PRECISION_BITS_EXPONENT;
	}
	
	protected function getBias($size) {
		if ($size <= self::SINGLE_PRECISION_SIZE) {
			return self::SINGLE_PRECISION_BIAS;
		}
		return self::DOUBLE_PRECISION_BIAS;
	}
	
	protected function purifySize($size) {
		if ($size <= self::SINGLE_PRECISION_SIZE) {
			return self::SINGLE_PRECISION_SIZE;
		}
		return self::DOUBLE_PRECISION_SIZE;
	}

	private function getMaxMantissa($size){
		//StartValue is 1, because the Mantissa is 1 + calculated Value
		$value = 1;
		for ($i = 1; $i <= $this->getBitsMantissa($size); $i++) {
			$value += pow(2, $i*-1);
		}
		return $value;
	}

	private function getMaxExp($size) {
		$value = $this->getBias($size) * - 1;
		for ($i = 0; $i < $this->getBitsExponent($size); $i++) {
			$value += pow(2, $i);
		}
		// the highest Value is reserved for NaN and Infinity
		return ($value-1);
	}
}

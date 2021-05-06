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

use n2n\persistence\meta\structure\FixedPointColumn;

class CommonFixedPointColumn extends ColumnAdapter implements FixedPointColumn {
	private $numIntegerDigits;
	private $numDecimalDigits;

	public function __construct($name, $numIntegerDigits, $numDecimalDigits) {
		parent::__construct($name);
		$this->numIntegerDigits = intval($numIntegerDigits);
		$this->numDecimalDigits = intval($numDecimalDigits);
	}

	public function getNumIntegerDigits() {
		return $this->numIntegerDigits;
	}

	public function getNumDecimalDigits() {
		return $this->numDecimalDigits;
	}

	public function equalsType(Column $column, $ignoreNull = false) {
		return parent::equalsType($column)
				&& $column instanceof CommonFixedPointColumn
				&& $column->getNumIntegerDigits() === $this->getNumIntegerDigits()
				&& $column->getNumDecimalDigits() === $this->getNumDecimalDigits();
	}
	
	public function copy($newColumnName = null) {
		if (is_null($newColumnName)) {
			$newColumnName = $this->getName();
		}
		$newColumn = new self($newColumnName, $this->getNumIntegerDigits(), $this->getNumDecimalDigits());
		$newColumn->applyCommonAttributes($this);
		return $newColumn;
	}
}

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
namespace n2n\impl\persistence\meta\oracle;

use n2n\persistence\meta\structure\FloatingPointColumn;

use n2n\persistence\meta\structure\common\ColumnAdapter;

class OracleFloatingPointColumn extends ColumnAdapter implements FloatingPointColumn {
	
	public function __construct($name) {
		parent::__construct($name);
	}
	
	public function getSize() {
		return null;
	}
	
	public function copy($newColumnName = null) {
		if (is_null($newColumnName)) {
			$newColumnName = $this->getName();
		}
		$newColumn = new self($newColumnName);
		$newColumn->applyCommonAttributes($this);
		return $newColumn;
	}
	
	public function getMaxValue() {
		//@see http://docs.oracle.com/cd/B19306_01/server.102/b14237/limits001.htm
		return 99999999999999999999999999999999999999 * pow(10, OracleSize::MAX_NUMBER_SCALE);
	}

	public function getMinValue() {
		return -1 * $this->getMaxValue(); 
	}
	public function getMaxExponent() {
		return OracleSize::MAX_NUMBER_SCALE;
	}

	public function getMinExponent() {
		return -130;
	}
}

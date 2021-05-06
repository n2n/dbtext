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

use n2n\persistence\meta\structure\EnumColumn;

class CommonEnumColumn extends ColumnAdapter implements EnumColumn {
	private $values;

	public function __construct($name, array $values) {
		parent::__construct($name);
		$this->values = $values;
	}

	public function equalsType(Column $column, $ignoreNull = false) {
		return parent::equalsType($column, $ignoreNull)
				&& $column instanceof CommonEnumColumn
				&& (count(array_diff($column->getValues(), $this->getValues())) == 0)
				&& (count(array_diff($this->getValues(), $column->getValues())) == 0);
	}

	public function getValues() {
		return $this->values;
	}

	public function copy($newColumnName = null) {
		if (is_null($newColumnName)) {
			$newColumnName = $this->getName();
		}
		
		$newColumn = new self($newColumnName, $this->getValues());
		$newColumn->applyCommonAttributes($this);
		return $newColumn;
	}
}

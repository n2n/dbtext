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

use n2n\persistence\meta\structure\TextColumn;

class CommonTextColumn extends ColumnAdapter implements TextColumn {
	private $size;
	private $charset;

	public function __construct($name, $size, $charset = null) {
		parent::__construct($name);
		$this->size = doubleval($size);
		$this->charset = $charset;
	}

	public function getSize() {
		return $this->size;
	}

	public function getCharset() {
		return $this->charset;
	}
	
	public function equalsType(Column $column, $ignoreNull = false) {
		return parent::equalsType($column)
				&& $column instanceof CommonTextColumn
				&& $column->getSize() === $this->getSize()
				&& $column->getCharset() === $this->getCharset();
	}

	public function copy($newColumnName = null) {
		if (is_null($newColumnName)) {
			$newColumnName = $this->getName();
		}
		$newColumn = new self($newColumnName, $this->getSize(), $this->getCharset());
		$newColumn->applyCommonAttributes($this);
		return $newColumn;
	}
}

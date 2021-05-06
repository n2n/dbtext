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
namespace n2n\persistence\orm\criteria\compare;

use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\orm\query\QueryItemSelect;

class SelectColumnComparable implements ColumnComparable {
	private $columnComparable;
	private $queryItemSelect;
	private $tableAlias;

	public function __construct(ColumnComparable $columnComparable, QueryItemSelect $queryItemSelect, $tableAlias = null) {
		$this->columnComparable = $columnComparable;
		$this->queryItemSelect = $queryItemSelect;
		$this->tableAlias = $tableAlias;
	}

	public function isSelectable($operator) {
		return $this->columnComparable->isSelectable($operator);
	}

	public function getAvailableOperators() {
		return $this->columnComparable->getAvailableOperators();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\criteria\compare\ColumnComparable::getTypeConstraint()
	*/
	public function getTypeConstraint($operator) {
		return $this->columnComparable->getTypeConstraint($operator);
	}

	public function buildQueryItem($operator) {
		$columnAlias = $this->queryItemSelect->selectQueryItem(
				$this->columnComparable->buildQueryItem($operator));
		return new QueryColumn($columnAlias, $this->tableAlias);
	}

	public function buildCounterpartQueryItemFromValue($operator, $value) {
		return $this->columnComparable->buildCounterpartQueryItemFromValue($operator, $value);
	}
}

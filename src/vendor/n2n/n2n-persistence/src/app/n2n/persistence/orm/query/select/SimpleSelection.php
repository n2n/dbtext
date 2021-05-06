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
namespace n2n\persistence\orm\query\select;

use n2n\persistence\meta\data\QueryItem;
use n2n\persistence\PdoStatement;
use n2n\util\type\ArgUtils;
use n2n\util\type\TypeName;

class SimpleSelection implements Selection {
	private $queryItem;
	private $type;
	private $value;

	public function __construct(QueryItem $queryItem, string $type = null) {
		$this->queryItem = $queryItem;
		ArgUtils::valEnum($type, [TypeName::BOOL, TypeName::INT, TypeName::FLOAT, TypeName::STRING], null, true);
		$this->type = $type;
	}
	
	public function getSelectQueryItems() {
		return array($this->queryItem);
	}

	public function bindColumns(PdoStatement $stmt, array $columnAliases) {
		$stmt->shareBindColumn($columnAliases[0], $this->value);
	}

	public function createValueBuilder() {
		$value = null; 
		if ($this->value !== null) {
			switch ($this->type) {
				case TypeName::BOOL;
					$value = (bool) $this->value;
					break;
				case TypeName::INT:
					$value = (int) $this->value;
					break;
				case TypeName::FLOAT:
					$value = (float) $this->value;
					break;
				case TypeName::STRING:
					$value = (string) $this->value;
					break;
				default:
					$value = $this->value;
			}
		}
		
		return new EagerValueBuilder($value);
	}
}

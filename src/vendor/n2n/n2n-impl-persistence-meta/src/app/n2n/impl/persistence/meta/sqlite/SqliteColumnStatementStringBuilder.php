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
namespace n2n\impl\persistence\meta\sqlite;

use n2n\persistence\meta\structure\UnavailableTypeException;
use n2n\persistence\meta\structure\Column;
use n2n\persistence\Pdo;

class SqliteColumnStatementStringBuilder {
	private $dbh;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	}
	
	public function generateStatementString(Column $column) {
		if (!$type = $this->getTypeForCurrentState($column)) {
			throw new UnavailableTypeException('Sqlite column type for column"' . $column->getName() . '" (' .  get_class($column) . ') in table"'
					. $column->getTable()->getName() . 'could not be determined.');	
		} 
		$statementString = $this->dbh->quoteField($column->getName()) . ' ' . $type;
		$statementString .= $this->generateDefaultStatementStringPart($column);
		return $statementString;
	}
	
	private function generateDefaultStatementStringPart(Column $column) {
		$statementString = '';
		
		if ($column->isNullAllowed()) {
			$statementString .= ' NULL';
		} else {
			$statementString .= ' NOT NULL';
		}
		
		$defaultValue = $column->getDefaultValue();
		if ($column->isDefaultValueAvailable() && (null !== $defaultValue || $column->isNullAllowed())) {
			$statementString .= ' DEFAULT ';
			if (null === $defaultValue) {
				$statementString .= 'NULL';
			} elseif (is_numeric($defaultValue)) {
				$statementString .= $defaultValue;
			} else {
				$statementString .= $this->dbh->quote($defaultValue) ;
			}
		}
		
		return $statementString;
	}
	
	private function getTypeForCurrentState(Column $column) {
		if ($column instanceof SqliteBinaryColumn){
			return 'BLOB';
		}
		if ($column instanceof SqliteFixedPointColumn) {
			return 'NUMERIC';
		}
		if ($column instanceof SqliteFloatingPointColumn) {
			return 'DOUBLE';
		}
		if ($column instanceof SqliteIntegerColumn) {
			return 'INTEGER';
		}
		if ($column instanceof SqliteStringColumn) {
			return 'VARCHAR';
		}
		if ($column instanceof SqliteDateTimeColumn) {
			return SqliteDateTimeColumn::COLUMN_TYPE_NAME;
		}
		return null;
	}
}

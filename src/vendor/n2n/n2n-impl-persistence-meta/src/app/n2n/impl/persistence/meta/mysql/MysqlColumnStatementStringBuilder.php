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
namespace n2n\impl\persistence\meta\mysql;

use n2n\persistence\meta\structure\Size;
use n2n\persistence\meta\structure\FixedPointColumn;
use n2n\persistence\meta\structure\UnavailableTypeException;
use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\TextColumn;
use n2n\persistence\meta\structure\StringColumn;
use n2n\persistence\meta\structure\FloatingPointColumn;
use n2n\persistence\meta\structure\EnumColumn;
use n2n\persistence\meta\structure\DateTimeColumn;
use n2n\persistence\meta\structure\BinaryColumn;
use n2n\persistence\meta\structure\IntegerColumn;
use n2n\persistence\meta\structure\Column;

class MysqlColumnStatementStringBuilder {
	
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	}
	
	public function generateStatementString(Column $column) {
		if (!$type = $this->getTypeForCurrentState($column)) {
			throw new UnavailableTypeException('Mysql column type for column"' . $column->getName() . '" (' .  get_class($column) . ') in table"'
					. $column->getTable()->getName() . 'could not be determined.');	
		} 
		$statementString = $this->dbh->quoteField($column->getName()) . ' ' . $type;
		$statementString .= $this->generateDefaultStatementStringPart($column);
		return $statementString;
	}

	private function generateDefaultStatementStringPart(Column $column) {
		$statementString = '';
		
		if ($column instanceof IntegerColumn) {
			if (!$column->isSigned()) {
				$statementString .= ' UNSIGNED';
			}
		}
		
		if ($column->isNullAllowed()) {
			$statementString .= ' NULL';
		} else {
			$statementString .= ' NOT NULL';
		}
		
		if ($column->isValueGenerated()) {
			$statementString .= ' AUTO_INCREMENT';
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
		if ($column instanceof BinaryColumn) {
			return 'VARBINARY(' . ceil($column->getSize() / 8) . ')';
		}
		if ($column instanceof DateTimeColumn) {
			if ($column->isDateAvailable() && $column->isTimeAvailable()) {
				return 'DATETIME';
			}
			if ($column->isDateAvailable()) {
				return 'DATE';
			}
			if ($column->isTimeAvailable()) {
				return 'TIME';
			}
			return 'YEAR';
		}
		if ($column instanceof EnumColumn) {
			return 'ENUM(' . $this->generateEnumString($column) . ')';
		}
		if ($column instanceof FixedPointColumn) {
			return 'DECIMAL(' . ($column->getNumIntegerDigits() + $column->getNumDecimalDigits()) . ',' . $column->getNumDecimalDigits() . ')';
		}
		if ($column instanceof FloatingPointColumn) {
			if ($column->getSize() <= Size::FLOAT) {
				return 'FLOAT';
			}
			return 'DOUBLE';
		}
		if ($column instanceof IntegerColumn) {
			if ($column->getSize() <= Size::SHORT) {
				return 'TINYINT';
			}
			if ($column->getSize() <= Size::MEDIUM) {
				return 'SMALLINT';
			}
			if ($column->getSize() <= MysqlSize::NUM_BITS_MEDIUMINT) {
				return 'MEDIUMINT';
			}
			if ($column->getSize() <= Size::INTEGER) {
				return 'INT';
			}
			return 'BIGINT';
		}
		if ($column instanceof StringColumn) {
			return 'VARCHAR(' . $column->getLength(). ')';
		}
		if ($column instanceof TextColumn) {
			if ($column->getSize() <= MysqlSize::SIZE_TINY_TEXT) {
				return 'TINYTEXT';
			}
			if ($column->getSize() <= MysqlSize::SIZE_TEXT) {
				return 'TEXT';
			}
			if ($column->getSize() <= MysqlSize::SIZE_MEDIUM_TEXT) {
				return 'MEDIUMTEXT';
			}
			return 'LONGTEXT';
		}
		if (($attrs = $column->getAttrs())
				&& isset($attrs['DATA_TYPE']) ) {
			return $attrs['DATA_TYPE'];
		}
		return null;
	}
	
	private function generateEnumString(EnumColumn $column) {
		$enumString = '';
		$isFirst = true;
		foreach ($column->getValues() as $value) {
			if (!$isFirst) {
				$enumString .= ',';
			} else {
				$isFirst = false;
			}
			$enumString .= '\'' . $value .  '\'';
		}
		return $enumString;
	}
}

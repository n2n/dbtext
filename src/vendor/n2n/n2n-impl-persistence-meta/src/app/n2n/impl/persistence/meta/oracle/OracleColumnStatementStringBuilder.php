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

use n2n\persistence\meta\structure\Size;

use n2n\persistence\meta\structure\FixedPointColumn;
use n2n\persistence\meta\structure\UnavailableTypeException;
use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\TextColumn;
use n2n\persistence\meta\structure\StringColumn;
use n2n\persistence\meta\structure\FloatingPointColumn;
use n2n\persistence\meta\structure\BinaryColumn;
use n2n\persistence\meta\structure\IntegerColumn;
use n2n\persistence\meta\structure\Column;

class OracleColumnStatementStringBuilder {
	
	const NATIONAL_CHARACTER_SET = 'NCHAR_CS';
	
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	}
	
	public function generateStatementString(Column $column) {
		if (!$type = $this->getTypeForCurrentState($column)) {
			throw new UnavailableTypeException('Oracle column type for column"' . $column->getName() . '" (' .  get_class($column) . ') in table"'
					. $column->getTable()->getName() . 'could not be determined.');	
		} 
		$statementString = $this->dbh->quoteField($column->getName()) . ' ' . $type;
		$statementString .= $this->generateDefaultStatementStringPart($column);
		return $statementString;
	}

	private function generateDefaultStatementStringPart(Column $column) {
		$statementString = '';
		
		if (!$column->isNullAllowed()) {
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
		if ($column instanceof BinaryColumn) {
			return 'BLOB';
		}
		if ($column instanceof OracleDateTimeColumn) {
			if ($column->isTimeAvailable()) {
				if ($column->isTimeZoneAvailable()) {
					return 'TIMESTAMP(' . $column->getFractionalSeconds() .') WITH TIME ZONE';
				} elseif ($column->isLocalTimeZoneAvailable()) {
					return 'TIMESTAMP(' . $column->getFractionalSeconds() .') WITH LOCAL TIME ZONE';
				}
				return 'TIMESTAMP(' . $column->getFractionalSeconds() .')';
			}
			return 'DATE';
		}
		if ($column instanceof FixedPointColumn) {
			return 'NUMBER(' . ($column->getNumIntegerDigits() + $column->getNumDecimalDigits()) . ',' . $column->getNumDecimalDigits() . ')';
		}
		if ($column instanceof FloatingPointColumn) {
			if ($column instanceof OracleFloatingPointColumn) {
				return 'NUMBER';
			}
			if ($column->getSize() <= Size::FLOAT) {
				return 'BINARY_FLOAT';
			}
			return 'BINARY_DOUBLE';
		}
		if ($column instanceof IntegerColumn) {
			return 'NUMBER(*,0)';
		}
		if ($column instanceof StringColumn) {
			if ($column->getCharset() == self::NATIONAL_CHARACTER_SET) {
				return 'NVARCHAR2(' . $column->getLength(). ')';
			}
			return 'VARCHAR2(' . $column->getLength(). ')';
		}
		if ($column instanceof TextColumn) {
			if ($column->getCharset() == self::NATIONAL_CHARACTER_SET) {
				if ($column->getSize() <= OracleSize::MAX_SIZE_VARCHAR) {
					return 'NVARCHAR2(' . ceil($column->getSize() / 8). ' BYTE)';
				}
				return 'NCLOB';
			}
			if ($column->getSize() <= OracleSize::MAX_SIZE_VARCHAR) {
				return 'VARCHAR2(' . ceil($column->getSize() / 8). ' BYTE)';
			}
			return 'CLOB';
		}
		if (($attrs = $column->getAttrs())
				&& isset($attrs['DATA_TYPE']) ) {
			return $attrs['DATA_TYPE'];
		}
		return null;
	}
}

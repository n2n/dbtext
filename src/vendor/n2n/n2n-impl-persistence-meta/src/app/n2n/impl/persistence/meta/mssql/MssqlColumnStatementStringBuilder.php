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
namespace n2n\impl\persistence\meta\mssql;

use n2n\persistence\meta\structure\InvalidColumnAttributesException;

use n2n\persistence\meta\structure\FixedPointColumn;
use n2n\persistence\meta\structure\UnavailableTypeException;
use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\TextColumn;
use n2n\persistence\meta\structure\StringColumn;
use n2n\persistence\meta\structure\common\CommonFloatingPointColumn;
use n2n\persistence\meta\structure\FloatingPointColumn;
use n2n\persistence\meta\structure\BinaryColumn;
use n2n\persistence\meta\structure\IntegerColumn;
use n2n\persistence\meta\structure\Column;
use n2n\persistence\meta\structure\Size;

class MssqlColumnStatementStringBuilder {
	
	const ATTR_NAME_COMPUTED_VALUE = 'COMPUTING_DEFINITION';
	
	private $dbh;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	}
	
	public function generateStatementString(Column $column) {
		if (!$type = $this->getTypeForCurrentState($column)) {
			throw new UnavailableTypeException('Mssql column type for column"' . $column->getName() . '" (' .  get_class($column) . ') in table"' 
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
		if ($column->isValueGenerated()) {
			//@todo isGeneratedIdentifier to Dialect
			if ($column instanceof MssqlIntegerColumn && $column->isGeneratedIdentifier()) {
				$statementString .= ' IDENTITY(1,1)';
			} else {
				$attrs = $column->getAttrs();
				if (!isset($attrs[self::ATTR_NAME_COMPUTED_VALUE])) {
					$tableStr = null !== ($table = $column->getTable()) ? ' in table "' . $table->getName() . '"' : '';
					throw new InvalidColumnAttributesException('No computing definition for generated value given. Column "' 
							. $column->getName() . $tableStr);
				}
				$statementString .= ' AS ' .  $attrs[self::ATTR_NAME_COMPUTED_VALUE];
			}
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
			return 'varbinary(' . ceil($column->getSize() / 8) . ')';
		}
		if ($column instanceof MssqlDateTimeColumn) {
			if ($column->getDateTimeOffset()) {
				return 'datetimeoffset(' . $column->getDateTimePrecision() . ')';
			} elseif ($column->isDateAvailable()) {
				if ($column->isTimeAvailable()) {
					return 'datetime2(' . $column->getDateTimePrecision() . ')';
				} else {
					return 'date(' . $column->getDateTimePrecision() . ')';
				}
			} 
			return 'time(' . $column->getDateTimePrecision() . ')';
		}
		if ($column instanceof FixedPointColumn) {
			return 'numeric(' . ($column->getNumIntegerDigits() + $column->getNumDecimalDigits()) . ',' . $column->getNumDecimalDigits() . ')';
		}
		if ($column instanceof FloatingPointColumn) {
			if ($column->getSize() <= CommonFloatingPointColumn::SINGLE_PRECISION_SIZE) {
				return 'float';
			} 
			return 'real';
		}
		if ($column instanceof IntegerColumn) {
			if ($column->getSize() <= Size::SHORT) {
				return 'tinyint';
			}
			if ($column->getSize() <= SIZE::MEDIUM) { 
				return 'smallint';
			}
			if ($column->getSize() <= SIZE::INTEGER) {
				return 'int';
			}
			return 'bigint';
		}
		if ($column instanceof StringColumn) {
			if ($column->getLength() > MssqlSize::MAX_STRING_SETTABLE_LENGTH ) {
				return 'nvarchar(max)';
			}
			return 'nvarchar(' . $column->getLength() . ')';
		}
		if ($column instanceof TextColumn) {
			if ($column->getSize() > MssqlSize::MAX_TEXT_SETTABLE_SIZE) {
				return 'varchar(max)';
			}
			return 'varchar(' . ceil($column->getSize()/8) . ')';
		}
		if (($attrs = $column->getAttrs())
				&& isset($attrs['DATA_TYPE'])) {
			return $attrs['DATA_TYPE'];
		}
		return null;
	}
}

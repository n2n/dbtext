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

use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\UnavailableTypeException;
use n2n\persistence\meta\structure\ColumnFactory;
use n2n\persistence\meta\structure\IntegerColumn;
use n2n\persistence\meta\structure\StringColumn;
use n2n\persistence\meta\structure\TextColumn;
use n2n\persistence\meta\structure\BinaryColumn;
use n2n\persistence\meta\structure\DateTimeColumn;
use n2n\persistence\meta\structure\EnumColumn;
use n2n\persistence\meta\structure\FixedPointColumn;
use n2n\persistence\meta\structure\FloatingPointColumn;

class SqliteColumnFactory implements ColumnFactory {
	
	/**
	 * @var Table
	 */
	private $table;
	
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	
	public function __construct(Table $table) {
		$this->table = $table;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::getTable()
	 */
	public function getTable(): Table {
		return $this->table;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createIntegerColumn()
	 * @return IntegerColumn
	 */
	public function createIntegerColumn(string $name, int $size, bool $signed = true): IntegerColumn {
		$column = new SqliteIntegerColumn($name);
		$this->table->addColumn($column);
		return $column;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createStringColumn()
	 * @return StringColumn
	 */
	public function createStringColumn(string $name, int $length, string $charset = null): StringColumn {
		$column = new SqliteStringColumn($name);
		$this->table->addColumn($column);
		return $column;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createTextColumn()
	 * @return TextColumn
	 */
	public function createTextColumn(string $name, int $size, string $charset = null): TextColumn {
		throw new UnavailableTypeException('Sqlite does not support Text columns');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createBinaryColumn()
	 * @return BinaryColumn
	 */
	public function createBinaryColumn(string $name, int $size): BinaryColumn {
		$column = new SqliteBinaryColumn($name);
		$this->table->addColumn($column);
		return $column;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createDateTimeColumn()
	 * @return DateTimeColumn
	 */
	public function createDateTimeColumn(string $name, bool $dateAvailable = true, bool $timeAvailable = true): DateTimeColumn {
		$column = new SqliteDateTimeColumn($name);
		$this->table->addColumn($column);
		return $column;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createEnumColumn()
	 * @return EnumColumn
	 */
	public function createEnumColumn(string $name, array $values): EnumColumn {
		throw new UnavailableTypeException('Sqlite does not support Enum columns');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createFixedPointColumn()
	 * @return FixedPointColumn
	 */
	public function createFixedPointColumn(string $name, int $numIntegerDigits, int $numDecimalDigits): FixedPointColumn {
		$column = new SqliteFixedPointColumn($name);
		$this->table->addColumn($column);
		return $column;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createFloatingPointColumn()
	 * @return FloatingPointColumn
	 */
	public function createFloatingPointColumn(string $name, int $size): FloatingPointColumn {
		$column = new SqliteFloatingPointColumn($name);
		$this->table->addColumn($column);
		return $column;
	}
}
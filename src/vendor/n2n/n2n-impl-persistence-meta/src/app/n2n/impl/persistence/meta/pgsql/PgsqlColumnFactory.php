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
namespace n2n\impl\persistence\meta\pgsql;

use n2n\persistence\meta\structure\ColumnFactory;
use n2n\persistence\meta\structure\common\CommonBinaryColumn;
use n2n\persistence\meta\structure\common\CommonFixedPointColumn;
use n2n\persistence\meta\structure\common\CommonFloatingPointColumn;
use n2n\persistence\meta\structure\common\CommonEnumColumn;
use n2n\persistence\meta\structure\common\CommonStringColumn;
use n2n\persistence\meta\structure\common\CommonTextColumn;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\BinaryColumn;
use n2n\persistence\meta\structure\IntegerColumn;
use n2n\persistence\meta\structure\FixedPointColumn;
use n2n\persistence\meta\structure\FloatingPointColumn;
use n2n\persistence\meta\structure\DateTimeColumn;
use n2n\persistence\meta\structure\EnumColumn;
use n2n\persistence\meta\structure\StringColumn;
use n2n\persistence\meta\structure\TextColumn;

class PgsqlColumnFactory implements ColumnFactory {
	/**
	 * @var PgsqlTable
	 */
	private $table;

	/**
	 * @param PgsqlTable $table
	 */
	public function __construct(PgsqlTable $table) {
		$this->setTable($table);
	}

	/**
	 * @param PgsqlTable $table
	 */
	public function setTable(PgsqlTable $table) {
		$this->table = $table;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::getTable()
	 * @return Table
	 */
	public function getTable(): Table {
		return $this->table;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createBinaryColumn()
	 * @return BinaryColumn
	 */
	public function createBinaryColumn(string $name, int $size): BinaryColumn {
		$binaryColumn = new CommonBinaryColumn($name, $size);
		$this->table->addColumn($binaryColumn);
		return $binaryColumn;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createIntegerColumn()
	 * @return IntegerColumn
	 */
	public function createIntegerColumn(string $name, int $size, bool $signed = true): IntegerColumn {
		$integerColumn = new PgsqlIntegerColumn($name, $size, $signed);
		$this->table->addColumn($integerColumn);
		return $integerColumn;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createFixedPointColumn()
	 * @return FixedPointColumn
	 */
	public function createFixedPointColumn(string $name, int $numIntegerDigits, int $numDecimalDigits): FixedPointColumn {
		$fixedPointColumn = new CommonFixedPointColumn($name, $numIntegerDigits, $numDecimalDigits);
		$this->table->addColumn($fixedPointColumn);
		return $fixedPointColumn;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createFloatingPointColumn()
	 * @return FloatingPointColumn
	 */
	public function createFloatingPointColumn(string $name, int $size): FloatingPointColumn {
		$floatingPointColumn = new CommonFloatingPointColumn($name, $size);
		$this->table->addColumn($floatingPointColumn);
		return $floatingPointColumn;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createDateTimeColumn()
	 * @return DateTimeColumn
	 */
	public function createDateTimeColumn(string $name, bool $dateAvailable = true, bool $timeAvailable = true): DateTimeColumn {
		$dateTimeColumn = new PgsqlDateTimeColumn($name, $dateAvailable, $timeAvailable);
		$this->table->addColumn($dateTimeColumn);
		return $dateTimeColumn;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createEnumColumn()
	 * @return EnumColumn
	 */
	public function createEnumColumn(string $name, array $values): EnumColumn {
		$enumColumn = new CommonEnumColumn($name, $values);
		$this->table->addColumn($enumColumn);
		return $enumColumn;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createStringColumn()
	 * @return StringColumn
	 */
	public function createStringColumn(string $name, int $length, string $charset = null): StringColumn {
		$stringColumn = new CommonStringColumn($name, $length, $charset);
		$this->table->addColumn($stringColumn);
		return $stringColumn;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\ColumnFactory::createTextColumn()
	 * @return TextColumn
	 */
	public function createTextColumn(string $name, int $size, string $charset = null): TextColumn {
		$textColumn = new CommonTextColumn($name, $size, $charset);
		$this->table->addColumn($textColumn);
		return $textColumn;
	}
}

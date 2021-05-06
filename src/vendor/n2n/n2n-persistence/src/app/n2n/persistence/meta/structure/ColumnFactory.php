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
namespace n2n\persistence\meta\structure;

interface ColumnFactory {
	
	/**
	 * Returns current database
	 * @return Table
	 */
	public function getTable(): Table;
	
	/**
	 * Creates a new IntegerColumn and applies it to the current table.
	 * @param string $name
	 * @param int $size number of bits (e.g. 32 for 32bit integer)
	 * @param bool $signed
	 * @return IntegerColumn
	 * @return IntegerColumn
	 */
	public function createIntegerColumn(string $name, int $size, bool $signed = true): IntegerColumn;
	
	/**
	 * Creates a new StringColumn and applies it to the current table.
	 * @param string $name
	 * @param int $length
	 * @param string $charset
	 * @return StringColumn
	 */
	public function createStringColumn(string $name, int $length, string $charset = null): StringColumn;
	
	/**
	 * Creates a new TextColumn and applies it to the current table.
	 * @param string $name
	 * @param int $size
	 * @param string $charset
	 * @return TextColumn
	 */
	public function createTextColumn(string $name, int $size, string $charset = null): TextColumn;
	
	/**
	 * Creates a new BinaryColumn and applies it to the current table.
	 * @param string $name
	 * @param int $size
	 * @return BinaryColumn
	 */
	public function createBinaryColumn(string $name, int $size): BinaryColumn;
	
	/**
	 * Creates a new DataTimeColumn and applies it to the current table.
	 * @param string $name
	 * @param bool $dateAvailable
	 * @param bool $timeAvailable
	 * @return \n2n\persistence\meta\structure\DateTimeColumn
	 */
	public function createDateTimeColumn(string $name, bool $dateAvailable = true, bool $timeAvailable = true): DateTimeColumn;
	
	/**
	 * Creates a new EnumColumn and applies it to the current table.
	 * @param string $name
	 * @param array $values
	 * @return EnumColumn
	 */
	public function createEnumColumn(string $name, array $values): EnumColumn;
	
	/**
	 * Creates a new FixedPointColumn and applies it to the current table.
	 * @param string $name
	 * @param int $numIntegerDigits
	 * @param int $numDecimalDigits
	 * @return FixedPointColumn
	 */
	public function createFixedPointColumn(string $name, int $numIntegerDigits, int $numDecimalDigits): FixedPointColumn;
	
	/**
	 * Creates a new FloatingPointColumn and applies it to the current table.
	 * @param string $name
	 * @param int $size
	 * @return FloatingPointColumn
	 */
	public function createFloatingPointColumn(string $name, int $size): FloatingPointColumn;
}

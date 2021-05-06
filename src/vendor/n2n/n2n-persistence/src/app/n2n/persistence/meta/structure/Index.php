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

interface Index {
	/**
	 * @return string one of {@link IndexType::PRIMARY}, {@link IndexType::UNIQUE}, {@link IndexType::INDEX}, {@link IndexType::FOREIGN} 
	 */
	public function getType(): string;
	
	/**
	 * @return string
	 */
	public function getName(): string;
	
	/**
	 * @return Table
	 */
	public function getTable(): Table;
	
	/**
	 * @return Column[]
	 */
	public function getColumns(): array;
	
	/**
	 * @return Column
	 * @throws UnknownColumnException
	 */
	public function getColumnByName(string $name): Column;
	
	/**
	 * @return bool
	 */
	public function containsColumnName(string $name): bool;
	
	/**
	 * Reference columns for foreign key indexes 
	 *  @return Column[]
	 */
	public function getRefColumns(): array;
	
	/**
	 * @return Column
	 * @throws UnknownColumnException
	 */
	public function getRefColumnByName(string $name): Column;
	
	/**
	 * @return bool
	 */
	public function containsRefColumnName(string $name): bool;
	
	/**
	 * Reference table for foreign key indexex
	 * @return Table|null
	 */
	public function getRefTable(): ?Table;
	
	/**
	 * @return string []
	 */
	public function getAttrs();
	
	/**
	 * @param Index $index
	 */
	public function equals(Index $index);
}

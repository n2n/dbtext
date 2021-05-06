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

use n2n\persistence\meta\structure\common\TableAdapter;
use n2n\persistence\meta\structure\ColumnFactory;
use n2n\persistence\meta\structure\Table;

class MysqlTable extends TableAdapter {
	
	const KEY_NAME_PRIMARY = 'PRIMARY';
	const INDEX_TYPE_FULLTEXT = 'FULLTEXT';
	const ATTRS_DEFAULT_CHARSET = 'DEFAULT_CHARSET';
	const ATTRS_TABLE_COLLATION = 'TABLE_COLLATION';
	const ATTRS_ENGINE = 'ENGINE';
	
	/**
	 * @var MysqlColumnFactory
	 */
	private $columnFactory;
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\Table::copy()
	 * @return Table
	 */
	public function copy(string $newTableName = null): Table {
		if (null === $newTableName) {
			$newTableName = $this->getName();
		}
		
		$newTable = new MysqlTable($newTableName);
		
		$newTable->applyColumnsFrom($this);
		$newTable->applyIndexesFrom($this);
		return $newTable;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\Table::createColumnFactory()
	 * @return ColumnFactory
	 */
	public function createColumnFactory(): ColumnFactory {
		if (!($this->columnFactory)) {
			$this->columnFactory = new MysqlColumnFactory($this);
		}
		return $this->columnFactory;
	}
	
	public function generatePrimaryKeyName() {
		return self::KEY_NAME_PRIMARY;
	}

}
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
namespace n2n\persistence\meta\data\common;

use n2n\persistence\meta\data\QueryComparator;

use n2n\persistence\meta\data\QueryItem;

use n2n\persistence\meta\data\QueryColumn;

use n2n\persistence\Pdo;
use n2n\persistence\meta\data\UpdateStatementBuilder;

class CommonUpdateStatementBuilder implements UpdateStatementBuilder {
	
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	private $setColumns;
	private $whereSelector;
	/**
	 * @var \n2n\persistence\meta\data\common\QueryFragmentBuilderFactory
	 */
	private $fragmentBuilderFactory;
	private $tableName;
	
	public function __construct(Pdo $dbh, QueryFragmentBuilderFactory $fragmentBuilderFactory) {
		$this->dbh = $dbh;
		$this->setColumns = array();
		$this->whereSelector = new QueryComparator();
		$this->fragmentBuilderFactory = $fragmentBuilderFactory;
	}
	
	public function setTable($tableName) {
		$this->tableName = $tableName;
	}
	
	public function addColumn(QueryColumn $column, QueryItem $value) {
		$this->setColumns[] = array('column' => $column, 'value' => $value);
	}
	
	public function getWhereComparator() {
		return $this->whereSelector;
	}
	
	public function toSqlString() {
		return $this->buildUpdateSql() . $this->buildSetSql() . $this->buildWhereSql();
	}
	
	private function buildUpdateSql() {
		return 'UPDATE ' . $this->dbh->quoteField($this->tableName);;
	}
	
	private function buildSetSql() {
		$itemSqlArr = array();
		foreach ($this->setColumns as $setColumn) {
			$fragmentBuilder = $this->fragmentBuilderFactory->create();
			$setColumn['column']->buildItem($fragmentBuilder);
			$fragmentBuilder->addOperator('=');
			$setColumn['value']->buildItem($fragmentBuilder);
	
			$itemSqlArr[] = $fragmentBuilder->toSql();
		}
	
		return ' SET ' . implode(', ', $itemSqlArr);
	}
	
	private function buildWhereSql() {
		if (is_null($this->whereSelector) || $this->whereSelector->isEmpty()) {
			return '';
		}
		$fragmentBuilder = $this->fragmentBuilderFactory->create();
		$this->whereSelector->buildQueryFragment($fragmentBuilder);
		return ' WHERE' . $fragmentBuilder->toSql();
	}
}

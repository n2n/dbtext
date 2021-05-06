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

use n2n\persistence\meta\data\InsertValueGroup;
use n2n\persistence\meta\data\InsertStatementBuilder;
use n2n\persistence\meta\data\QueryItem;
use n2n\persistence\Pdo;
use n2n\persistence\meta\data\QueryColumn;

class CommonInsertStatementBuilder implements InsertStatementBuilder {
	
	/**
	 * @var \n2n\persistence\meta\data\common\QueryFragmentBuilderFactory
	 */
	private $fragmentBuilderFactory;
	
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	
	private $tableName;
	private $columns;
	private $whereSelector;
	private $additionalValueGroups;

	public function __construct(Pdo $dbh, QueryFragmentBuilderFactory $fragmentBuilderFactory) {
		$this->dbh = $dbh;
		$this->columns = array();
		$this->additionalValueGroups = array();
		$this->fragmentBuilderFactory = $fragmentBuilderFactory;
	}

	public function setTable($tableName) {
		$this->tableName = $tableName;
	}

	public function addColumn(QueryColumn $column, QueryItem $value) {
		$this->columns[] = array('column' => $column, 'value' => $value);
	}

	public function toSqlString() {
		return $this->buildInsertIntoSql() . $this->buildColumnSql();
	}
	
	public function createAdditionalValueGroup() {
		$valueGroup = new InsertValueGroup();
		$this->additionalValueGroups[] = $valueGroup;
		return $valueGroup;
	}

	private function buildInsertIntoSql() {
		return 'INSERT INTO ' . $this->dbh->quoteField($this->tableName);
	}

	private function buildColumnSql() {
		$namesSqlArr = array();
		$valuesSqlArr = array();
		foreach ($this->columns as $column) {
			$namesSqlArr[] = $this->dbh->quoteField($column['column']->getColumnName());
			$queryFragmentBuilder = $this->fragmentBuilderFactory->create();
			$column['value']->buildItem($queryFragmentBuilder);
			$valuesSqlArr[] = $queryFragmentBuilder->toSql();
		}

		$sqlString = ' (' . implode(', ', $namesSqlArr) . ') ' . PHP_EOL;
		 
		return $sqlString . 'VALUES (' . implode(', ', $valuesSqlArr) . ')' . $this->buildAdditionalValueGroupSql();
	}

	private function buildWhereSql() {
		if (is_null($this->whereSelector) || $this->whereSelector->isEmpty()) {
			return '';
		}

		$fragmentBuilder = $this->fragmentBuilderFactory->create();
		$this->whereSelector->buildQueryFragment($fragmentBuilder);
		return ' WHERE' . $fragmentBuilder->toSql();
	}
	
	private function buildAdditionalValueGroupSql() {
		if (sizeof($this->additionalValueGroups) == 0) {
			return '';
		}
		
		$sqlString = '';
		foreach ($this->additionalValueGroups as $valueGroup) {
			$sqlString .= ',' . PHP_EOL . '(';
			$values = array();
			foreach ($valueGroup->getValues() as $value) {
				$fragmentBuilder = $this->fragmentBuilderFactory->create();
				$value->buildItem($fragmentBuilder);
				$values[] = $fragmentBuilder->toSql();
			}
			$sqlString .= implode(',', $values);
			$sqlString .= ')';
		}
		return $sqlString;
	}
}

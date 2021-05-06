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

use n2n\persistence\meta\data\JoinType;

use n2n\util\type\ArgUtils;

use n2n\persistence\meta\data\QueryComparator;

use n2n\persistence\meta\data\QueryResult;

use n2n\persistence\meta\data\QueryItem;

use n2n\persistence\Pdo;

use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\meta\data\OrderDirection;
use n2n\persistence\meta\data\StatementQueryResult;

class CommonSelectStatementBuilder implements SelectStatementBuilder {
	const COLUMN_SEPARATOR = ',';
	
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	
	/**
	 * @var \n2n\persistence\meta\data\common\QueryFragmentBuilderFactory
	 */
	private $fragmentBuilderFactory;
	
	private $selectColumns = array();
	private $fromQueryResults = array();
	private $joins = array();
	private $whereComparator;
	private $orders = array();
	private $groupColumns = array();
	private $havingComparator;
	private $limit;
	private $num;
	private $distinct;
	
	public function __construct(Pdo $dbh, QueryFragmentBuilderFactory $fragmentBuilderFactory) {
		$this->dbh = $dbh;
		$this->whereComparator = new QueryComparator();
		$this->havingComparator = new QueryComparator();
		$this->fragmentBuilderFactory = $fragmentBuilderFactory;
	}
	
	public function setDistinct($distinct) {
		$this->distinct = $distinct;
	}
	
	public function addSelectColumn(QueryItem $queryItem, $asName = null) {
		$this->selectColumns[] = array('queryItem' => $queryItem, 'asName' => $asName);
	}
	
	public function addFrom(QueryResult $queryResult, $alias = null) {
		$this->fromQueryResults[] = array('queryResult' => $queryResult, 'alias' => $alias);
	}
	
	public function addJoin($joinType, QueryResult $queryResult, $alias = null, QueryComparator $onComparator = null) {
		ArgUtils::valEnum($joinType, JoinType::getValues());
		if ($onComparator === null) {
			$onComparator = new QueryComparator();
		}
		$this->joins[] = array('type' => $joinType, 'queryResult' => $queryResult, 'alias' => $alias, 'onSelector' => $onComparator);
		return $onComparator;
	}
	
	public function getWhereComparator() {
		return $this->whereComparator;
	}
	
	public function addGroup(QueryItem $queryColumn) {
		$this->groupColumns[] = $queryColumn;
	}
	
	public function getHavingComparator() {
		return $this->havingComparator;
	}
	
	public function setHaving(QueryComparator $queryComparator) {
		$this->havingComparator = $queryComparator;
	}
	
	public function addOrderBy(QueryItem $queryItem, $direction) {
		ArgUtils::valEnum($direction, OrderDirection::getValues());
		$this->orders[] = array('queryItem' => $queryItem, 'direction' => $direction);
	}
	
	public function setLimit($limit, $num = null) {
		$this->limit = $limit;
		$this->num = $num;
	}

	public function toQueryResult() {
		return new StatementQueryResult($this->toSqlString());
	}
	
	public function toFromQueryResult(): QueryResult {
		return new StatementQueryResult($this->buildFromSql(false) . $this->buildJoinSql());
	}
	
	public function toSqlString() {
		return $this->buildSelectSql() . $this->buildFromSql() . $this->buildJoinSql() . $this->buildWhereSql()
				. $this->buildGroupSql() .	$this->buildHavingSql() . $this->buildOrderSql() . $this->buildLimitSql();
	}
	
	private function buildSelectSql() {
		$sql = 'SELECT';
	
		if (!sizeof($this->selectColumns)) {
			$sql .= ' *';
			return $sql;
		}
		
		if ($this->distinct) {
			$sql .= ' DISTINCT';
		}
	
		$itemSqlArr = array();
		foreach ($this->selectColumns as $selectColumn) {
			$fragmentBuilder = $this->fragmentBuilderFactory->create();
			$selectColumn['queryItem']->buildItem($fragmentBuilder);
			if (isset($selectColumn['asName'])) {
				$fragmentBuilder->addFieldAlias($selectColumn['asName']);
			}
				
			$itemSqlArr[] = $fragmentBuilder->toSql();
		}
	
		return $sql . implode(self::COLUMN_SEPARATOR, $itemSqlArr);
	}
	
	private function buildFromSql(bool $includeFromKeyword = true) {
		if (!sizeof($this->fromQueryResults)) return '';
	
		$sqlArr = array();
		foreach ($this->fromQueryResults as $fromQueryResult) {
			$fragmentBuilder = $this->fragmentBuilderFactory->create();
			$fromQueryResult['queryResult']->buildItem($fragmentBuilder);
			$sqlArr[] =  $fragmentBuilder->toSql() . (isset($fromQueryResult['alias']) ? ' ' 
					. $this->dbh->quoteField($fromQueryResult['alias']) : '');	
		}
		
		return ($includeFromKeyword ? ' FROM ' : ''). implode(self::COLUMN_SEPARATOR . ' ', $sqlArr);
	}
	
	private function buildJoinSql() {
		$sqlArr = array();
		foreach ($this->joins as $join) {
			$fragmentBuilder = $this->fragmentBuilderFactory->create();
			$join['queryResult']->buildItem($fragmentBuilder);
			$sqlPart = $join['type'] . ' JOIN ' . $fragmentBuilder->toSql();
			if (isset($join['alias'])) {
				$sqlPart .= ' ' . $this->dbh->quoteField($join['alias']);
			}
				
			if (!$join['onSelector']->isEmpty()) {
				$fragmentBuilder = $this->fragmentBuilderFactory->create();
				$join['onSelector']->buildQueryFragment($fragmentBuilder);
				$sqlPart .= ' ON' . $fragmentBuilder->toSql();
			}
				
			$sqlArr[] = $sqlPart;
		}
		return ' ' . implode(' ', $sqlArr);
	}
	
	private function buildWhereSql() {
		if (is_null($this->whereComparator) || $this->whereComparator->isEmpty()) {
			return '';
		}
		$fragmentBuilder = $this->fragmentBuilderFactory->create();
		$this->whereComparator->buildQueryFragment($fragmentBuilder);
		return ' WHERE' . $fragmentBuilder->toSql();
	}
	
	private function buildGroupSql() {
		if (!sizeof($this->groupColumns)) {
			return '';
		}
	
		$sqlArr = array();
		foreach ($this->groupColumns as $groupColumn) {
			$fragmentBuilder = $this->fragmentBuilderFactory->create();
			$groupColumn->buildItem($fragmentBuilder);
			$sqlArr[] = $fragmentBuilder->toSql();
		}
		return ' GROUP BY' . implode(self::COLUMN_SEPARATOR, $sqlArr);
	}
	
	private function buildHavingSql() {
		if (is_null($this->havingComparator) || $this->havingComparator->isEmpty()) {
			return '';
		}
		$fragmentBuilder = $this->fragmentBuilderFactory->create();
		$this->havingComparator->buildQueryFragment($fragmentBuilder);
		return ' HAVING' . $fragmentBuilder->toSql();
	}
	
	private function buildOrderSql() {
		if (!sizeof($this->orders)) {
			return '';
		}
	
		$sqlArr = array();
		foreach ($this->orders as $order) {
			$fragementBuilder = $this->fragmentBuilderFactory->create();
			$order['queryItem']->buildItem($fragementBuilder);
			$fragementBuilder->addOperator($order['direction']);
			$sqlArr[] = $fragementBuilder->toSql();
		}
	
		return ' ORDER BY ' . implode(self::COLUMN_SEPARATOR, $sqlArr);
	}
	
	private function buildLimitSql() {
		if (is_null($this->limit)) return '';
	
		$sql = ' LIMIT ' . intval($this->limit);
		if (isset($this->num)) {
			$sql .= self::COLUMN_SEPARATOR . ' ' . intval($this->num);
		}
		return $sql;
	}
}

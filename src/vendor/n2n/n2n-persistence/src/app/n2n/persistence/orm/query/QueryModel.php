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
namespace n2n\persistence\orm\query;

use n2n\persistence\orm\query\from\Tree;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\meta\data\QueryItem;

class QueryModel {
	private $distinct;
	
	private $unnamedSelectQueryPoints = array();
	private $namedSelectQueryPoints = array();
	private $hiddenSelectQueryPoints = array();
	
	private $tree;
	
	private $queryItemSelect;
	private $whereQueryComparator;
	private $groupQueryItems = array();
	private $orderDefs = array();
	private $havingQueryComparator;
	private $limit;
	private $num;
	
	public function __construct(Tree $tree, QueryItemSelect $queryItemSelect) {
		$this->tree = $tree;
		$this->queryItemSelect = $queryItemSelect;;	
	}
	
	public function setDistinct($distinct) {
		$this->distinct = $distinct;
	}
	
	public function addNamedSelectQueryPoint(QueryPoint $queryPoint, $alias) {
		$this->namedSelectQueryPoints[$alias] = $queryPoint;
	}
	/**
	 * @return QueryPoint[]
	 */
	public function getNamedSelectQueryPoints() {
		return $this->namedSelectQueryPoints;
	}
	
	public function addUnnamedSelectQueryPoint(QueryPoint $queryPoint) {
		$this->unnamedSelectQueryPoints[] = $queryPoint;
	}
	
	public function getUnnamedSelectQueryPoints() {
		return $this->unnamedSelectQueryPoints;
	}
	
	public function addHiddenSelectQueryPoint(QueryPoint $queryPoint) {
		$this->hiddenSelectQueryPoints[] = $queryPoint;
	}
	
	public function getHiddenSelectQueryPoints() {
		return $this->hiddenSelectQueryPoints;
	}
	/**
	 * @return Tree
	 */
	public function getTree() {
		return $this->tree;
	}
	/**
	 * @return \n2n\persistence\orm\query\QueryItemSelect
	 */
	public function getQueryItemSelect() {
		return $this->queryItemSelect;
	}
	/**
	 * @param QueryComparator $queryComparator
	 */
	public function setWhereQueryComparator(QueryComparator $queryComparator = null) {
		$this->whereQueryComparator = $queryComparator;	
	}
	/**
	 * @param QueryItem $queryItem
	 * @param string $direction
	 */
	public function addOrderQueryPoint(QueryItem $queryItem, $direction) {
		$this->orderDefs[] = array('queryItem' => $queryItem, 'direction' => $direction);
	}
	
	public function addGroupQueryPoint(QueryItem $queryItem) {
		$this->groupQueryItems[] = $queryItem;
	}

	public function setHavingQueryComparator(QueryComparator $queryComparator = null) {
		$this->havingQueryComparator = $queryComparator;
	}
	
	public function setLimit($limit) {
		$this->limit = $limit;
	}
	
	public function setNum($num) {
		$this->num = $num;
	}
	
	public function apply(SelectStatementBuilder $selectBuilder) {
		$selectBuilder->setDistinct($this->distinct);
		
		$this->queryItemSelect->apply($selectBuilder);
		$this->tree->apply($selectBuilder);
		
		$selectBuilder->getWhereComparator()->andGroup($this->whereQueryComparator);
		
		foreach ($this->groupQueryItems as $queryItem) {
			$selectBuilder->addGroup($queryItem);
		}
		
		foreach ($this->orderDefs as $orderDef) {
			$selectBuilder->addOrderBy($orderDef['queryItem'], $orderDef['direction']);
		}
		
		$selectBuilder->getHavingComparator()->andGroup($this->havingQueryComparator);
		
		$selectBuilder->setLimit($this->limit, $this->num);
	}
}

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
namespace n2n\persistence\orm\query\from;

use n2n\persistence\orm\criteria\compare\ColumnComparable;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\orm\criteria\CriteriaConflictException;
use n2n\persistence\orm\query\select\Selection;
use n2n\util\ex\UnsupportedOperationException;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;
use n2n\persistence\PdoStatement;
use n2n\persistence\orm\query\QueryModel;
use n2n\persistence\orm\query\QueryConflictException;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\QueryItemSelect;
use n2n\persistence\meta\data\QueryItem;

abstract class SubCriteriaTreePoint implements TreePoint {
	protected $queryModel;
	protected $queryState;
	
	protected $tableAlias;
	
	public function __construct(QueryModel $queryModel, QueryState $queryState) {
		$this->queryModel = $queryModel;
		$this->queryState = $queryState;
		
		$this->tableAlias = $queryState->createTableAlias();
	}
	
	public function getQueryState() {
		return $this->queryState;
	}
	
	public function createPropertyJoinedTreePoint(string $propertyName, $joinType): JoinedTreePoint {
		throw new CriteriaConflictException('Sub criteria cannot be joined.');
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\from\TreePoint::requestPropertyJoinedTreePoint()
	 */
	public function requestPropertyJoinedTreePoint(string $propertyName, bool $innerJoinRequired): JoinedTreePoint {
		throw new UnsupportedOperationException();
	}
	
	public function requestComparisonStrategy(): ComparisonStrategy {
		throw new CriteriaConflictException('Sub criteria cannot be compared.');
	}
	/**
	 * @param TreePath $treePath
	 * @throws QueryConflictException
	 * @return \n2n\persistence\orm\query\QueryPoint
	 */
	private function findSelectedCriteriaItem(TreePath $treePath) {
		$alias = $treePath->next();
		
		$namedSelectQueryPoints = $this->queryModel->getNamedSelectQueryPoints();
		if (isset($namedSelectQueryPoints[$alias])) {
			return $namedSelectQueryPoints[$alias];
		}
		
		throw new QueryConflictException('Unknown column alias \'' . $alias 
				. '\' in subcriteria with path \'' 
				. TreePath::prettyPropertyStr($treePath->getDones(0, -1)) . '\'');
	}
	
	public function requestPropertyComparisonStrategy(TreePath $treePath) {
		if (!$treePath->hasNext()) {
			return $this->requestComparisonStrategy();
		}
		
		$queryPoint = $this->findSelectedCriteriaItem($treePath);
		
		$comparisonStrategy = null;
		if (!$treePath->hasNext()) {
			$comparisonStrategy = $queryPoint->requestComparisonStrategy();
		} else {
			$comparisonStrategy = $queryPoint->requestPropertyComparisonStrategy($treePath);	
		}
		
// 		throw new QueryConflictException('Unresolvable path \''
// 				. TreePath::prettyPropertyStr($treePath->getNexts()) . '\' in subcriteria with path \''
// 				. TreePath::prettyPropertyStr($treePath->getDone())) . '\'';
		
		if ($comparisonStrategy->getType() != ComparisonStrategy::TYPE_COLUMN) {
			throw new QueryConflictException('Criteria item \'' . $queryPoint->__toString() 
					. ' not comparable through sub criteria.');
		}
		
		return new ComparisonStrategy(new DecoratedColumnComparable($comparisonStrategy->getColumnComparable(), 
						$this->queryModel->getQueryItemSelect(), $this->tableAlias));
	}
	
	public function requestSelection(): Selection {
		throw new CriteriaConflictException('Sub criteria cannot be selected.');
	}
	
	public function requestPropertySelection(TreePath $treePath) {
		if (!$treePath->hasNext()) {
			return $this->requestSelection();
		}

		$queryPoint = $this->findSelectedCriteriaItem($treePath);
		$selection = null;
		if (!$treePath->hasNext()) {
			$selection = $queryPoint->requestSelection();
		} else {
			$selection = $queryPoint->requestPropertySelection($treePath);
		}
		
		$subQueryItems = array();
		$queryItemSelect = $this->queryModel->getQueryItemSelect();
		foreach ($selection->getSelectQueryItems() as $key => $queryItem) {
			$columnAlias = $queryItemSelect->selectQueryItem($queryItem);
			$subQueryItems[$key] = new QueryColumn($columnAlias, $this->tableAlias);
		}
		
		return new DecoratedSubSelection($selection, $subQueryItems);
	}

	public function requestRepresentableQueryItem(): QueryItem {
		throw new QueryConflictException('Sub criteria not representable by query item.');
	}

	public function requestPropertyRepresentableQueryItem(TreePath $treePath) {
		if (!$treePath->hasNext()) {
			return $this->requestRepresentableQueryItem();
		}
		
		$queryPoint = $this->findSelectedCriteriaItem($treePath);
		$queryItem = null;
		if (!$treePath->hasNext()) {
			$queryItem = $queryPoint->requestRepresentableQueryItem();
		} else {
			$queryItem = $queryPoint->requestPropertyRepresentableQueryItem($treePath);
		}
		
		$columnAlias = $this->queryModel->getQueryItemSelect()->selectQueryItem($queryItem);
		return new QueryColumn($columnAlias, $this->tableAlias);
	}
	
	protected function buildQueryResult() {
		$subSelectBuilder = $this->queryState->getPdo()->getMetaData()->createSelectStatementBuilder();
		
		$this->queryModel->apply($subSelectBuilder);
		
		return $subSelectBuilder->toQueryResult();
	}
}

class DecoratedSubSelection implements Selection {
	private $selection;
	private $queryItems;
	
	public function __construct(Selection $selection, array $queryColumns) {
		$this->selection = $selection;
		$this->queryItems = $queryColumns;
	}
	
	public function getSelectQueryItems() {
		return $this->queryItems;
	}
	
	public function bindColumns(PdoStatement $stmt, array $columnAliases) {
		$this->selection->bindColumns($stmt, $columnAliases);
	}
	
	public function createValueBuilder() {
		return $this->selection->createValueBuilder();
	}
}

class DecoratedColumnComparable implements ColumnComparable {
	private $columnComparable;
	private $queryItemSelect;
	private $tableAlias;
	
	public function __construct(ColumnComparable $columnComparable, QueryItemSelect $queryItemSelect, $tableAlias) {
		$this->columnComparable = $columnComparable;
		$this->queryItemSelect = $queryItemSelect;
		$this->tableAlias = $tableAlias;
	}
	
	public function getAvailableOperators() {
		return $this->columnComparable->getAvailableOperators();
	}
	
	public function isSelectable($operator) {
		return true;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\criteria\compare\ColumnComparable::getTypeConstraint()
	 */
	public function getTypeConstraint($operator) {
		return $this->columnComparable->getTypeConstraint($operator);
	}
	
	public function buildQueryItem($operator) {
		$columnAlias = $this->queryItemSelect->selectQueryItem($this->columnComparable->buildQueryItem($operator));
		return new QueryColumn($columnAlias, $this->tableAlias);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\criteria\compare\ColumnComparable::buildCounterpartQueryItemFromValue()
	 */
	public function buildCounterpartQueryItemFromValue($operator, $value) {
		return $this->columnComparable->buildCounterpartQueryItemFromValue($operator, $value);	
	}
}

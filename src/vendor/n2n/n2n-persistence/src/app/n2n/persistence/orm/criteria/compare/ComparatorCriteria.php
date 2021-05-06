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
namespace n2n\persistence\orm\criteria\compare;

use n2n\persistence\orm\criteria\item\CriteriaItem;
use n2n\persistence\orm\query\QueryState;
use n2n\util\ex\UnsupportedOperationException;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\CriteriaConflictException;
use n2n\util\type\TypeConstraint;
use n2n\persistence\orm\query\QueryPointResolver;
use n2n\persistence\orm\query\QueryPoint;
use n2n\persistence\orm\query\QueryModel;
use n2n\persistence\meta\data\QueryItem;
use n2n\util\type\CastUtils;
use n2n\persistence\orm\query\select\Selection;

class ComparatorCriteria extends Criteria implements CriteriaItem {

	public function createSelection(QueryState $queryState) {
		throw new UnsupportedOperationException();
	}
	
	public function createQueryPoint(QueryState $queryState, QueryPointResolver $queryPointResolver): QueryPoint {
		return new ComparatorCriteriaQueryPoint(
				$this->createQueryModel($queryState, $queryPointResolver), $queryState);
	}
	
	public function toQuery() {
		throw new UnsupportedOperationException(
				'ComparatorCriteria must be used in CriteriaComparator and can not be converted to Query');
	}
	
	public function __toString(): string {
		return '<SubCriteria>';
	}
}

class ComparatorCriteriaQueryPoint implements QueryPoint {
	private $queryModel;
	private $queryState;
	
	private $comparisonStrategy = null;
	
	public function __construct(QueryModel $queryModel, QueryState $queryState) {
		$this->queryModel = $queryModel;
		$this->queryState = $queryState;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPoint::requestComparisonStrategy()
	 */
	public function requestComparisonStrategy(): ComparisonStrategy {
		if ($this->comparisonStrategy !== null) {
			return $this->comparisonStrategy;
		}
		
		$selectedQueryPoints = array_merge($this->queryModel->getNamedSelectQueryPoints(),
				$this->queryModel->getUnnamedSelectQueryPoints());
		if (1 != count($selectedQueryPoints)) {
			throw new CriteriaConflictException('Subselect not comparable.');
		}
		
		$comparisonStrategy = current($selectedQueryPoints)->requestComparisonStrategy();
		CastUtils::assertTrue($comparisonStrategy instanceof ComparisonStrategy);
		if ($comparisonStrategy->getType() != ComparisonStrategy::TYPE_COLUMN) {
			throw new CriteriaConflictException();
		}
		
		$columnComparable = $comparisonStrategy->getColumnComparable();
		
		$this->queryModel->getQueryItemSelect()->selectQueryItem($columnComparable
				->buildQueryItem(CriteriaComparator::OPERATOR_EQUAL));
		
		$selectBuilder  = $this->queryState->getPdo()->getMetaData()->createSelectStatementBuilder();
		$this->queryModel->apply($selectBuilder);
				
		return $this->comparisonStrategy = new ComparisonStrategy(new CriteriaColumnComparable(
				$selectBuilder->toQueryResult(), $columnComparable));
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPoint::requestSelection()
	 */
	public function requestSelection(): Selection {
		throw new UnsupportedOperationException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPoint::requestRepresentableQueryItem()
	 */
	public function requestRepresentableQueryItem(): QueryItem {
		$selectBuilder  = $this->queryState->getPdo()->getMetaData()->createSelectStatementBuilder();
		$this->queryModel->apply($selectBuilder);
		
		return $selectBuilder->toQueryResult();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPointResolver::requestPropertyComparisonStrategy()
	 */
	public function requestPropertyComparisonStrategy(\n2n\persistence\orm\query\from\TreePath $treePath) {
		throw new UnsupportedOperationException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPointResolver::requestPropertySelection()
	 */
	public function requestPropertySelection(\n2n\persistence\orm\query\from\TreePath $treePath) {
		throw new UnsupportedOperationException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPointResolver::requestPropertyRepresentableQueryItem()
	 */
	public function requestPropertyRepresentableQueryItem(\n2n\persistence\orm\query\from\TreePath $treePath) {
		throw new UnsupportedOperationException();
	}
}

class CriteriaColumnComparable implements ColumnComparable {
	private $queryItem;
	private $columnComparable;
	
	public function __construct(QueryItem $queryItem, ColumnComparable $columnComparable) {
		$this->queryItem = $queryItem;
		$this->columnComparable = $columnComparable;
	}
	
	public function getTypeConstraint($operator) {
		if ($operator == CriteriaComparator::OPERATOR_CONTAINS || $operator == CriteriaComparator::OPERATOR_CONTAINS_NOT) {
			return TypeConstraint::createArrayLike(null, false, $this->columnComparable->getTypeConstraint(CriteriaComparator::OPERATOR_EQUAL));
		}
		
		return $this->columnComparable->getTypeConstraint($operator);
	}
	
	public function getAvailableOperators() {
		return CriteriaComparator::getOperators(true, false);
	}
	
	public function isSelectable($operator) {
		return false;
	}
	
	public function buildQueryItem($operator) {
		return $this->queryItem;
	}

	public function buildCounterpartQueryItemFromValue($operator, $value) {
		return $this->columnComparable->buildCounterpartQueryItemFromValue($operator, $value);
	}
	
// 	public function parseComparableValue($operator, $value) {
// 		return $this->columnComparable->parseComparableValue($operator, $value);
// 	}
}

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
namespace n2n\persistence\orm\criteria;

use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\query\from\TreePath;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\query\from\Tree;
use n2n\persistence\orm\query\QueryModel;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\query\QueryPointResolver;
use n2n\util\ex\UnsupportedOperationException;
use n2n\persistence\orm\query\QueryConflictException;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\orm\query\QueryItemSelect;
use n2n\persistence\meta\data\JoinType;
use n2n\persistence\orm\query\Query;
use n2n\util\type\ArgUtils;
use n2n\persistence\meta\data\OrderDirection;
use n2n\persistence\orm\criteria\compare\ComparatorCriteria;
use n2n\persistence\orm\criteria\compare\SelectColumnComparable;
use n2n\util\type\TypeUtils;

class Criteria {
	const ORDER_DIRECTION_ASC = OrderDirection::ASC;
	const ORDER_DIRECTION_DESC = OrderDirection::DESC;
	
	private $persistenceContext; 

	private $distinct;
	
	private $unnamedSelectCriteriaItems = array();
	private $namedSelectCriteriaItems = array();
	
	private $treeModClosures = array();
	
	private $whereComparator;

	private $orderDefs = array();
	private $groupCriteriaItems = array();
	
	private $havingComparator;
	
	private $limit;
	private $num;
	/**
	 * 
	 */
	public function __construct() {
		$this->whereComparator = new CriteriaComparator($this);
		$this->havingComparator = new CriteriaComparator($this);
	}
	/**
	 * @param string $distinct
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function distinct($distinct = true) {
		$this->distinct = $distinct;
		return $this;
	}
	
	private function validateAlias($alias) {
		if (!is_scalar($alias)) {
			throw new CriteriaConflictException('Invalid criteria alias type: ' . TypeUtils::getTypeInfo($alias));
		}
		
		if (0 == mb_strlen($alias)) {
			throw new CriteriaConflictException('Empty string passed as criteria alias.');
		}
	}
	
	/**
	 * @param mixed $item Arg for {@see CrIt::pfLenient()}
	 * @param string $alias
	 * @throws CriteriaConflictException
	 * @return Criteria
	 */
	public function select($item, $alias = null) {
		$criteriaItem = CrIt::pfLenient($item);
		
		if ($alias === null) {
			$this->unnamedSelectCriteriaItems[] = $criteriaItem;
			return $this;
		}
		
		$this->validateAlias($alias);
		$alias = (string) $alias;
		if (isset($this->namedSelectCriteriaItems[$alias])) {
			throw new CriteriaConflictException('Column alias ambiguous: ' . $alias);
		}
		
		$this->namedSelectCriteriaItems[$alias] = $criteriaItem;
		
		return $this;
	}
	/**
	 * @param \ReflectionClass $entityClass
	 * @param string $alias
	 * @param string $fetch
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function from(\ReflectionClass $entityClass, string $alias, bool $fetch = false) {
		$this->validateAlias($alias);
		$this->treeModClosures[] = function (QueryModel $queryModel, QueryState $queryState) 
				use ($entityClass, $alias, $fetch) {
			$entityModel = $queryState->getEntityModelManager()->getEntityModelByClass($entityClass);
			$treePoint = $queryModel->getTree()->createBaseTreePoint($entityModel, $alias);
			
			if ($fetch) {
				$queryModel->addHiddenSelectQueryPoint($treePoint);
			}
		};
		
		return $this;
	}
	/**
	 * @param Criteria $criteria
	 * @param string $alias
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function fromCriteria(Criteria $criteria, string $alias) {
		$this->validateAlias($alias);
		$this->treeModClosures[] = function (QueryModel $queryModel, QueryState $queryState) 
				use ($criteria, $alias) {
			$queryModel->getTree()->createBaseCriteriaTreePoint($criteria->createQueryModel($queryState), $alias);
		};
		
		return $this;
	}
	/**
	 * @param Criteria $criteria
	 * @param string $alias
	 * @param string $joinType
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function joinCriteria(Criteria $criteria, string $alias, $joinType = JoinType::INNER) {
		$this->validateAlias($alias);
		$onCriteriaComparator = new CriteriaComparator($this, null, false, false);
		
		$this->treeModClosures[] = function (QueryModel $queryModel, QueryState $queryState) 
				use ($criteria, $alias, $joinType, $onCriteriaComparator) {
			$tree = $queryModel->getTree();
			$treePoint = $tree->createJoinedCriteriaTreePoint($joinType, 
					$criteria->createQueryModel($queryState, $tree), $alias);
			$onCriteriaComparator->apply($treePoint->getOnQueryComparator(), $queryState, $tree);
		};
		
		return $onCriteriaComparator;
	}
	/**
	 * @param mixed $propertyExpression Arg for {@see CrIt::p()}
	 * @param string $alias
	 * @param string $joinType
	 * @param bool $fetch
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function joinProperty($propertyExpression, string $alias, string $joinType = JoinType::INNER, 
			bool $fetch = false) {
		$this->preparePropertyJoin($propertyExpression, $alias, $joinType, $fetch);
		return $this;
	}
	
	/**
	 * @param mixed $propertyExpression Arg for {@see CrIt::p()}
	 * @param string $alias
	 * @param string $joinType
	 * @param bool $fetch
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function joinPropertyOn($propertyExpression, string $alias, string $joinType = JoinType::INNER, 
			bool $fetch = false) {
		$onCriteriaComparator = new CriteriaComparator($this, null, false, false);
				
		$this->preparePropertyJoin($propertyExpression, $alias, $joinType, $fetch, 
				$onCriteriaComparator);
		
		return $onCriteriaComparator;
	}
	
	/**
	 * @param mixed $propertyExpression Arg for {@see CrIt::p()}
	 * @param string $alias
	 * @param string $joinType
	 * @param boolean $fetch
	 * @param CriteriaComparator $onCriteriaComparator
	 * @throws CriteriaConflictException
	 */
	private function preparePropertyJoin($propertyExpression, string $alias, $joinType = JoinType::INNER, bool $fetch = false, 
			CriteriaComparator $onCriteriaComparator = null) {
		$criteriaProperty = CrIt::p($propertyExpression);
		$this->validateAlias($alias);
		if ($joinType === null) {
			$joinType = JoinType::INNER;
		}
		
		$this->treeModClosures[] = function (QueryModel $queryModel, QueryState $queryState)
				use ($joinType, $criteriaProperty, $alias, $fetch, $onCriteriaComparator) {
		
			try {
				$treePoint = $queryModel->getTree()->createPropertyJoinedTreePoint($joinType,
						new TreePath($criteriaProperty->getPropertyNames()), $alias);
			} catch (QueryConflictException $e) {
				throw new CriteriaConflictException('Unable to perform ' . $joinType . ' JOIN ' . $criteriaProperty, 0, $e);
			}
			
			if ($onCriteriaComparator !== null) {
				$onCriteriaComparator->apply($treePoint->getOnQueryComparator(), $queryState, $queryModel->getTree());
			}
				
			if ($fetch) {
				$queryModel->addHiddenSelectQueryPoint($treePoint);
			}
		};
	}
	
	/**
	 * @param \ReflectionClass $entityClass
	 * @param string $alias
	 * @param string $joinType
	 * @param string $fetch
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function join(\ReflectionClass $entityClass, string $alias, $joinType = JoinType::INNER, bool $fetch = false) {
		$this->validateAlias($alias);
		
		$onCriteriaComparator = new CriteriaComparator($this, null, false, false);
		
		$this->treeModClosures[] = function (QueryModel $queryModel, QueryState $queryState)
				use ($alias, $joinType, $onCriteriaComparator, $fetch, $entityClass) {

			$tree = $queryModel->getTree();
			$entityModel = $queryState->getEntityModelManager()->getEntityModelByClass($entityClass);
			$treePoint = $tree->createJoinedEntityTreePoint($joinType, $entityModel, $alias);
			$onCriteriaComparator->apply($treePoint->getOnQueryComparator(), $queryState, $tree);
			
			if ($fetch) {
				$queryModel->addHiddenSelectQueryPoint($treePoint);
			}
		};
		
		return $onCriteriaComparator;
	}
	/**
	 * @param array $matches
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function where(array $matches = array()) {
		foreach ($matches as $key => $value) {
			$this->whereComparator->andMatch($key, CriteriaComparator::OPERATOR_EQUAL, $value);
		}
		
		return $this->whereComparator;
	}
	/**
	 * @param mixed $expression
	 * @param string $direction
	 * @return Criteria
	 */
	public function order($expression, $direction = self::ORDER_DIRECTION_ASC) {
		ArgUtils::valEnum($direction, self::getOrderDirections());
		$this->orderDefs[] = array('criteriaItem' => CrIt::pf($expression),
				'direction' => $direction);
		return $this;
	}
	/**
	 * @param mixed $expression
	 * @return Criteria
	 */
	public function group($expression) {
		$this->groupCriteriaItems[] = CrIt::pf($expression);
		return $this;
	}
	/**
	 * @param array $matches
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function having(array $matches = array()) {
		foreach ($matches as $key => $value) {
			$this->havingComparator->andMatch($key, CriteriaComparator::OPERATOR_EQUAL, $value);
		}
	
		return $this->havingComparator;
	}
	/**
	 * @param int $limit
	 * @param int $num
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function limit(int $limit = null, int $num = null) {
		$this->limit = $limit;
		$this->num = $num;
		return $this;
	}
	
	public function subCriteria() {
		return new ComparatorCriteria();
	}
	
	const UNIQUE_ALIAS_PREFIX = '_tmpuniqal';
	private $uai = 1;
	
	public function uniqueAlias(): string {
		return self::UNIQUE_ALIAS_PREFIX . $this->uai++;
	}
	
	public static function getOrderDirections() {
		return array(self::ORDER_DIRECTION_ASC, self::ORDER_DIRECTION_DESC);
	}
	
	protected function createQueryModel(QueryState $queryState, QueryPointResolver $inheritedQueryPointResolver = null) {
		$tree = new Tree($queryState);
		$tree->setInheritedQueryPointResolver($inheritedQueryPointResolver);
		
		$queryModel = new QueryModel($tree, new QueryItemSelect($queryState));
		
		$queryModel->setDistinct($this->distinct);
		
		foreach ($this->treeModClosures as $treeModClosure) {
			$treeModClosure($queryModel, $queryState);
		}
		
		foreach ($this->namedSelectCriteriaItems as $alias => $criteriaItem) {
			$queryModel->addNamedSelectQueryPoint($criteriaItem->createQueryPoint(
					$queryState, $tree), $alias);
		}
		
		foreach ($this->unnamedSelectCriteriaItems as $criteriaItem) {
			$queryModel->addUnnamedSelectQueryPoint($criteriaItem->createQueryPoint(
					$queryState, $tree));
		}
				
		if (!$this->whereComparator->isEmpty()) {
			$queryComparator = new QueryComparator();
			$this->whereComparator->apply($queryComparator, $queryState, $tree);
			$queryModel->setWhereQueryComparator($queryComparator);
		}
		
		$selectQueryPointResolver = new SelectQueryPointResolver($queryModel, $tree);
		
		foreach ($this->groupCriteriaItems as $criteriaItem) {
			$queryModel->addGroupQueryPoint($criteriaItem
					->createQueryPoint($queryState, $selectQueryPointResolver)
							->requestRepresentableQueryItem());
		}
		
		foreach ($this->orderDefs as $orderDef) {
			$queryModel->addOrderQueryPoint($orderDef['criteriaItem']
							->createQueryPoint($queryState, $selectQueryPointResolver)
									->requestRepresentableQueryItem(), 
					$orderDef['direction']);
		}
		
		if (!$this->havingComparator->isEmpty()) {
			$queryComparator = new QueryComparator();
			$this->havingComparator->apply($queryComparator, $queryState, new HavingQueryPointResolver($queryModel));
			$queryModel->setHavingQueryComparator($queryComparator);
		}
		
		$queryModel->setLimit($this->limit);
		$queryModel->setNum($this->num);
		
		return $queryModel;
	}
	/**
	 * @return Query
	 */
	public function toQuery() {
		throw new UnsupportedOperationException('Only base criterias can be converted to Query.');
	}
}


class SelectQueryPointResolver implements QueryPointResolver {
	private $queryModel;
	private $queryPointResolver;
	
	public function __construct(QueryModel $queryModel, QueryPointResolver $queryPointResolver) {
		$this->queryModel = $queryModel;
		$this->queryPointResolver = $queryPointResolver;
	}
	
	private function findSelectQueryPoint(TreePath $treePath) {
		$namedSelectQueryPoints = $this->queryModel->getNamedSelectQueryPoints();
		if (isset($namedSelectQueryPoints[$treePath->getNext()])) {
			return $namedSelectQueryPoints[$treePath->next()];
		}
		
		return null;
	}

	public function requestPropertyComparisonStrategy(TreePath $treePath) {
		if (null !== ($selectQueryPoint = $this->findSelectQueryPoint($treePath))) {
			if ($treePath->hasNext()) {
				return $selectQueryPoint->requestPropertyComparisonStrategy($treePath);
			}
			
			return $selectQueryPoint->requestComparisonStrategy();
		}
		
		return $this->queryPointResolver->requestPropertyComparisonStrategy($treePath);
	}

	public function requestPropertySelection(TreePath $treePath) {
		if (null !== ($selectQueryPoint = $this->findSelectQueryPoint($treePath))) {
			if ($treePath->hasNext()) {
				return $selectQueryPoint->requestPropertySelection($treePath);
			}
			
			return $selectQueryPoint->requestSelection();
		}
		
		return $this->queryPointResolver->requestPropertySelection($treePath);
	}

	public function requestPropertyRepresentableQueryItem(TreePath $treePath) {
		if (null !== ($selectQueryPoint = $this->findSelectQueryPoint($treePath))) {
			if ($treePath->hasNext()) {
				return $selectQueryPoint->requestPropertyRepresentableQueryItem($treePath);
			}
			
			return $selectQueryPoint->requestRepresentableQueryItem();
		}
		
		return $this->queryPointResolver->requestPropertyRepresentableQueryItem($treePath);
	}
}

class HavingQueryPointResolver implements QueryPointResolver {
	private $queryModel;
	
	public function __construct(QueryModel $queryModel) {
		$this->queryModel = $queryModel;
	}
	
	private function findSelectQueryPoint(TreePath $treePath) {
		$alias = $treePath->next();
		$namedSelectQueryPoints = $this->queryModel->getNamedSelectQueryPoints();
		if (isset($namedSelectQueryPoints[$alias])) {
			return $namedSelectQueryPoints[$alias];
		}
		
		$suqueryPropertyNames = $treePath->getDones(0, $treePath->getNumDones() - 1);
		
		if (count($suqueryPropertyNames) == 0) {
			throw new QueryConflictException('Unknown column alias \'' . $alias
					. '\' in selection');
		} 
		
		throw new QueryConflictException('Unknown column alias \'' . $alias
				. '\' in selection of subquery with path \''
				. TreePath::prettyPropertyStr($suqueryPropertyNames) . '\'');	
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPointResolver::requestPropertyComparisonStrategy()
	 */
	public function requestPropertyComparisonStrategy(TreePath $treePath) {
		$queryPoint = $this->findSelectQueryPoint($treePath);
		$comparisonStrategy = null;
		if ($treePath->hasNext()) {
			$comparisonStrategy = $queryPoint->requestPropertyComparisonStrategy($treePath);
		} else {
			$comparisonStrategy = $queryPoint->requestComparisonStrategy();
		}
		
		if ($comparisonStrategy->getType() != ComparisonStrategy::TYPE_COLUMN) {
			throw new QueryConflictException('Property can not be compared in having clause: '
					. TreePath::prettyPropertyStr($treePath->getDones()));
		}
		
		return new ComparisonStrategy(new SelectColumnComparable(
				$comparisonStrategy->getColumnComparable(),
				$this->queryModel->getQueryItemSelect()));
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPointResolver::requestPropertySelection()
	 */
	public function requestPropertySelection(TreePath $treePath) {
		throw new UnsupportedOperationException('Method not used. If I\'m wrong, notice me.');
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPointResolver::requestPropertyRepresentableQueryItem()
	 */
	public function requestPropertyRepresentableQueryItem(TreePath $treePath) {
		$queryPoint = $this->findSelectQueryPoint($treePath);
		$queryItem = null;
		if ($treePath->hasNext()) {
			$queryItem = $queryPoint->requestPropertyRepresentableQueryItem($treePath);
		} else {
			$queryItem = $queryPoint->requestRepresentableQueryItem();
		}
		
		$columnAlias = $this->queryModel->getQueryItemSelect()->selectQueryItem($queryItem);
		return new QueryColumn($columnAlias);
	}	
}

// private function buildCriteriaSelections() {
// 	foreach ($this->selectCriteriaItems as $alias => $criteriaItem) {
// 		if (isset($this->criteriaSelections[$alias])) continue;
			
// 		$this->criteriaSelections[$alias] = $criteriaItem->createCriteriaSelection($this->queryState);
// 	}

// 	if (!sizeof($this->criteriaSelections)) {
// 		$baseItem = new CriteriaProperty(array($this->baseEntityAlias));
// 		$this->criteriaSelections[] = $baseItem->createCriteriaSelection($this->queryState);
// 	}
// }

// private function buildHiddenEntityObjSelections() {
// 	$selectStmtBuilder = $this->queryState->getSelectStatementBuilder();
// 	foreach ($this->queryState->getHiddenEntityObjSelectionTableAliases() as $tableAlias) {
// 		if (isset($this->hiddenEntityObjSelections[$tableAlias])) continue;
// 		throw new NotYetImplementedException();
// 		// 			$entityModel = $this->queryState->getEntityModelByTableAlias($tableAlias);
// 		// 			$hiddenEntityObjSelection = new EntityCriteriaSelection($queryState, $queryPoint);
// 		// 			$hiddenEntityObjSelection->applyColumnsToSelectStatementBuilder($selectStmtBuilder, $tableAlias, $this->queryState);
// 		// 			$this->hiddenEntityObjSelections[$tableAlias]= $hiddenEntityObjSelection;
// 	}
// }

// private function createStmt() {
// 	$this->buildCriteriaSelections();
// 	$this->buildHiddenEntityObjSelections();

// 	$dbh = $this->queryState->getEntityManager()->getPdo();
// 	$stmt = $dbh->prepare($this->queryState->getSelectStatementBuilder()->toSqlString());
// 	$stmt->execute($this->queryState->getPlaceholderValues());
// 	return $stmt;
// }

// public function meta() {
// 	if (is_null($this->meta)) {
// 		$this->meta = new CriteriaMeta($this->queryState);
// 	}
// 	return $this->meta;
// }
// class CriteriaMeta {
// 	private $queryState;
	
// 	public function __construct(QueryState $queryState) {
// 		$this->queryState = $queryState;
// 	}
	
// 	public function getQueryState() {
// 		return $this->queryState;
// 	}
// }

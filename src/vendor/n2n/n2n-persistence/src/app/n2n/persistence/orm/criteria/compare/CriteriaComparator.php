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

use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\util\type\ArgUtils;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\QueryPointResolver;
use n2n\persistence\orm\criteria\Criteria;
/**
 * 
 * @copyright HNM, Winterthur
 * @license http://www.n2n.ch/license
 * @author Andreas von Burg <exenberger@hnm.ch>
 */
class CriteriaComparator {
	const OPERATOR_EQUAL = QueryComparator::OPERATOR_EQUAL;
	const OPERATOR_NOT_EQUAL = QueryComparator::OPERATOR_NOT_EQUAL;
	const OPERATOR_LARGER_THAN = QueryComparator::OPERATOR_LARGER_THAN;
	const OPERATOR_LARGER_THAN_OR_EQUAL_TO = QueryComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO;
	const OPERATOR_SMALLER_THAN = QueryComparator::OPERATOR_SMALLER_THAN;
	const OPERATOR_SMALLER_THAN_OR_EQUAL_TO = QueryComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO;
	const OPERATOR_LIKE = QueryComparator::OPERATOR_LIKE;
	const OPERATOR_NOT_LIKE = QueryComparator::OPERATOR_NOT_LIKE;
	const OPERATOR_IN = QueryComparator::OPERATOR_IN;
	const OPERATOR_NOT_IN = QueryComparator::OPERATOR_NOT_IN;
	const OPERATOR_CONTAINS = 'CONTAINS';
	const OPERATOR_CONTAINS_NOT = 'CONTAINS NOT';
	const OPERATOR_CONTAINS_ANY = 'CONTAINS ANY';
	const OPERATOR_CONTAINS_NONE = 'CONTAINS NONE';
	
	const OPERATOR_EXISTS = QueryComparator::OPERATOR_EXISTS;
	const OPERATOR_NOT_EXISTS = QueryComparator::OPERATOR_NOT_EXISTS;
	
	private $criteria;
	private $parentComparator;
	private $expectConstForArg1;
	private $expectConstForArg2;

	private $comparisonDefs = array();
	/**
	 * @param Criteria $criteria
	 * @param CriteriaComparator $parentComparator
	 * @param bool $expectConstForArg1
	 * @param bool $expectConstForArg2
	 */
	public function __construct(Criteria $criteria = null, CriteriaComparator $parentComparator = null, 
			$expectConstForArg1 = false, $expectConstForArg2 = true) {
		$this->criteria = $criteria;
		$this->parentComparator = $parentComparator;
		$this->expectConstForArg1 = (boolean) $expectConstForArg1;
		$this->expectConstForArg2 = (boolean) $expectConstForArg2;
	}
	/**
	 * @param mixed $arg1
	 * @param string $operator
	 * @param mixed $arg2
	 * @param bool $useAnd
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function match($arg1, $operator, $arg2, $useAnd = true) {
		ArgUtils::valEnum($operator, self::getOperators(true, true, true));
		
		$this->comparisonDefs[] = array(
				'criteriaItem1' => $this->parseCriteriaItem($arg1, $this->expectConstForArg1), 
				'operator' => $operator, 
				'criteriaItem2' => $this->parseCriteriaItem($arg2, $this->expectConstForArg2),
				'useAnd' => (boolean) $useAnd);
		return $this;
	}
	
	private function parseCriteriaItem($arg, $expectConst) {
		if ($expectConst) {
			return CrIt::cLenient($arg);
		}
	
		return CrIt::pfLenient($arg);
	}
	/**
	 * @param mixed $arg1
	 * @param string $operator
	 * @param mixed $arg2
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function andMatch($arg1, $operator, $arg2) {
		return $this->match($arg1, $operator, $arg2, true);
	}
	/**
	 * @param mixed $arg1
	 * @param string $operator
	 * @param mixed $arg2
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function orMatch($arg1, $operator, $arg2) {
		return $this->match($arg1, $operator, $arg2, false);
	}
	/**
	 * @param string $operator
	 * @param ComparatorCriteria $comparatorCriteria
	 * @param bool $useAnd
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function test($operator, ComparatorCriteria $comparatorCriteria, $useAnd = true) {
		ArgUtils::valEnum($operator, self::getTestOperators());
		
		$this->comparisonDefs[] = array(
				'operator' => $operator,
				'testCriteria' => $comparatorCriteria,
				'useAnd' => (boolean) $useAnd);
		
		return $this;
	}
	/**
	 * @param string $operator
	 * @param ComparatorCriteria $comparatorCriteria
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function andTest($operator, ComparatorCriteria $comparatorCriteria) {
		return $this->test($operator, $comparatorCriteria, true);
	}
	/**
	 * @param string $operator
	 * @param ComparatorCriteria $comparatorCriteria
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function orTest($operator, ComparatorCriteria $comparatorCriteria) {
		return $this->test($operator, $comparatorCriteria, false);
	}
	/**
	 * @param bool $useAnd
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function group($useAnd = true) {
		$groupCriteriaComparator = new CriteriaComparator($this->criteria, $this, $this->expectConstForArg1, 
				$this->expectConstForArg2);
		$this->comparisonDefs[] = array(
				'groupCriteriaComparator' => $groupCriteriaComparator,
				'useAnd' => (boolean) $useAnd);
		return $groupCriteriaComparator;
	}
	/**
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function andGroup() {
		return $this->group(true);
	}
	/**
	 * @return \n2n\persistence\orm\criteria\compare\CriteriaComparator
	 */
	public function orGroup() {
		return $this->group(false);
	}
	/**
	 * @return CriteriaComparator
	 */
	public function endGroup() {
		return $this->parentComparator;
	}
	/**
	 * @return Criteria
	 * @deprecated use {@link CriteriaComparator::endClause()}
	 */
	public function endWhere() {
		return $this->criteria;
	}
	/**
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function endClause() {
		return $this->criteria;
	}
	
	
// 	public function meta() {
// 		if (is_null($this->criteriaSelectorMeta)) {
// 			$this->criteriaSelectorMeta = new QueryComparatorMeta($this->queryState, $this->queryComparator);
// 		}
// 		return $this->criteriaSelectorMeta;
// 	}
	
	public function apply(QueryComparator $queryComparator, QueryState $queryState, 
			QueryPointResolver $queryPointResolver) {
		$comparatorBuilder = new QueryComparatorBuilder($queryState, $queryPointResolver, $queryComparator);
		
		foreach ($this->comparisonDefs as $comparisonDef) {
			if (isset($comparisonDef['groupCriteriaComparator'])) {
				$comparisonDef['groupCriteriaComparator']->apply(
						($comparisonDef['useAnd'] ? $queryComparator->andGroup() : $queryComparator->orGroup()),
						$queryState, $queryPointResolver);
				continue;
			}
			
			if (isset($comparisonDef['testCriteria'])) {
				$comparatorBuilder->applyTest($comparisonDef['operator'],
						$comparisonDef['testCriteria'], $comparisonDef['useAnd']);
				continue;
			}
			
			$comparatorBuilder->applyMatch($comparisonDef['criteriaItem1'], $comparisonDef['operator'],
					$comparisonDef['criteriaItem2'], $comparisonDef['useAnd']);
		}
	}
	
	
	public static function getOperators($includeContains = true, $includeIn = true, $includeContainsAny = false) {
		$operators = array(self::OPERATOR_EQUAL, self::OPERATOR_NOT_EQUAL, self::OPERATOR_LARGER_THAN, 
				self::OPERATOR_LARGER_THAN_OR_EQUAL_TO, self::OPERATOR_SMALLER_THAN, 
				self::OPERATOR_SMALLER_THAN_OR_EQUAL_TO, self::OPERATOR_LIKE, 
				self::OPERATOR_NOT_LIKE);
		
		if ($includeIn) {
			$operators[] = self::OPERATOR_IN;
			$operators[] = self::OPERATOR_NOT_IN;
		}
		
		if ($includeContains) {
			$operators[] = self::OPERATOR_CONTAINS;
			$operators[] = self::OPERATOR_CONTAINS_NOT;
		}
		
		if ($includeContainsAny) {
			$operators[] = self::OPERATOR_CONTAINS_ANY;
		}
		
		return $operators;
	}
	
	public static function getTestOperators() {
		return array(self::OPERATOR_EXISTS, self::OPERATOR_NOT_EXISTS);
	}
	
	public function isEmpty() {
		return empty($this->comparisonDefs);
	}
}

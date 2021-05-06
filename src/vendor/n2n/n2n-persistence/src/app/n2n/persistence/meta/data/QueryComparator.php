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
namespace n2n\persistence\meta\data;

class QueryComparator {
	const OPERATOR_EQUAL = '=';
	const OPERATOR_NOT_EQUAL = '!=';
	const OPERATOR_LARGER_THAN = '>';
	const OPERATOR_LARGER_THAN_OR_EQUAL_TO = '>=';
	const OPERATOR_SMALLER_THAN = '<';
	const OPERATOR_SMALLER_THAN_OR_EQUAL_TO = '<=';
	const OPERATOR_LIKE = 'LIKE';
	const OPERATOR_NOT_LIKE = 'NOT LIKE';
	const OPERATOR_IS = 'IS';
	const OPERATOR_IS_NOT = 'IS NOT';
	const OPERATOR_IN = 'IN';
	const OPERATOR_NOT_IN = 'NOT IN';
	
	const OPERATOR_EXISTS = 'EXISTS';
	const OPERATOR_NOT_EXISTS = 'NOT EXISTS';
	
	const LIKE_WILDCARD_ONE_CHAR = '_';
	const LIKE_WILDCARD_MANY_CHARS = '%';
	
	const SEQ_OPERATOR_AND = 'AND';
	const SEQ_OPERATOR_OR = 'OR';
	
	private $parentSelector = null;
	private $firstComparison = null;
	private $lastComparison = null;
	
	public function __construct(QueryComparator $parentSelector = null) {
		$this->parentSelector = $parentSelector;
	}
	
	public function isEmpty() {
		if (is_null($this->firstComparison)) {
			return true;
		}
		
		$currentComparison = $this->firstComparison;
		while (true) {
			if (!$currentComparison->isToSkip()) {
				return false;
			}
			
			$sequenceOperator = $currentComparison->getSequenceOperator();
			if (is_null($sequenceOperator)) return true;
			$currentComparison = $sequenceOperator->getNext();
		}
		
		return true;
	}
	
	public function match(QueryItem $queryItem1, $operator, QueryItem $queryItem2, $useAnd = true) { 
		$this->addComparison($useAnd, new ItemComparison($queryItem1, $operator, $queryItem2));
		return $this;
	}
	
	public function andMatch(QueryItem $queryItem1, $operator, QueryItem $queryItem2) {
		$this->match($queryItem1, $operator, $queryItem2, true);
		return $this;
	}
	
	public function orMatch(QueryItem $queryItem1, $operator, QueryItem $queryItem2) { 
		$this->match($queryItem1, $operator, $queryItem2, false);
		return $this;
	}
	
	public function test($operator, QueryResult $queryResult, $useAnd = true) {
		$this->addComparison($useAnd, new TestComparison($operator, $queryResult));
		return $this;
	}
	
	public function andTest($operator, QueryResult $queryResult) {
		$this->test($operator, $queryResult, true);
		return $this;
	}
	
	public function orTest($operator, QueryResult $queryResult) {
		$this->test($operator, $queryResult, false);
		return $this;
	}
	
	public function group(QueryComparator $queryComparator = null) {
		return $this->andGroup($queryComparator);
	}
	
	public function andGroup(QueryComparator $queryComparator = null) {
		if (is_null($queryComparator)) $queryComparator = new QueryComparator();
		$this->addComparison(true, new ComparisonGroup($queryComparator));
		return $queryComparator;
	}
	
	public function orGroup(QueryComparator $queryComparator = null) {
		if (is_null($queryComparator)) $queryComparator = new QueryComparator();
		$this->addComparison(false, new ComparisonGroup($queryComparator));
		return $queryComparator;
	}
	
	private function addComparison($useAnd, Comparison $comparison) {
		if (is_null($this->firstComparison)) {
			$this->firstComparison = $comparison;
			$this->lastComparison = $comparison;
			return;
		}
		
		$this->lastComparison->setSequenceOperator(new SequenceOperator(
				($useAnd ? self::SEQ_OPERATOR_AND : self::SEQ_OPERATOR_OR), $comparison));
		$this->lastComparison = $comparison;
	}
	
	public function endGroup() {
		return $this->parentSelector;
	}
	
	public function getFirstComparison() {
		return $this->firstComparison;
	}
	
	public function buildQueryFragment(QueryFragmentBuilder $fragmentBuilder) {
		if ($this->firstComparison === null) return;

		$this->buildQueryComparison($this->firstComparison, $fragmentBuilder);	
	}
	
	private function buildQueryComparison(Comparison $comparison, QueryFragmentBuilder $fragmentBuilder) {
		if (!$comparison->isToSkip()) {
			$comparison->buildQueryComparison($fragmentBuilder);
		}
		
		$sequenceOperator = $comparison->getSequenceOperator();
		if (is_null($sequenceOperator)) return;

		$nextComparison = $sequenceOperator->getNext();
		if (($this->firstComparison !== $comparison || !$comparison->isToSkip()) 
				&& !$nextComparison->isToSkip()) {
			$fragmentBuilder->addOperator($sequenceOperator->getOperator());
		}
		
		$this->buildQueryComparison($nextComparison, $fragmentBuilder);
	}
	/**
	 * @return array
	 */
	public static function getOperators() {
		return array(self::OPERATOR_EQUAL, self::OPERATOR_NOT_EQUAL, self::OPERATOR_LARGER_THAN,
				self::OPERATOR_LARGER_THAN_OR_EQUAL_TO, self::OPERATOR_SMALLER_THAN,
				self::OPERATOR_SMALLER_THAN_OR_EQUAL_TO, self::OPERATOR_LIKE, self::OPERATOR_IS,
				self::OPERATOR_IS_NOT, self::OPERATOR_IN, self::OPERATOR_NOT_IN);
	}
	
	public static function getTestOperators() {
		return array(self::OPERATOR_EXISTS, self::OPERATOR_NOT_EXISTS);
	}
}

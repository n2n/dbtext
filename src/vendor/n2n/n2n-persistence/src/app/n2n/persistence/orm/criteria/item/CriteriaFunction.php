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
namespace n2n\persistence\orm\criteria\item;

use n2n\persistence\meta\data\QueryItemSequence;
use n2n\util\type\ArgUtils;
use n2n\persistence\meta\data\QueryFunction;
use n2n\persistence\orm\criteria\compare\ScalarColumnComparable;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\QueryPointResolver;
use n2n\persistence\orm\query\QueryPoint;
use n2n\persistence\orm\query\from\TreePath;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;
use n2n\persistence\orm\query\select\SimpleSelection;
use n2n\persistence\orm\criteria\CriteriaConflictException;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\query\select\Selection;
use n2n\persistence\meta\data\QueryItem;

class CriteriaFunction implements CriteriaItem {
	const COUNT = QueryFunction::COUNT;
	const SUM = QueryFunction::SUM;
	const MAX = QueryFunction::MAX;
	const MIN = QueryFunction::MIN;
	const RAND = QueryFunction::RAND;
	const AVG = QueryFunction::AVG;
	
	const ABS = QueryFunction::ABS;
	const COALESCE = QueryFunction::COALESCE;
	const LOWER = QueryFunction::LOWER;
	const LTRIM = QueryFunction::LTRIM;
	const NULLIF = QueryFunction::NULLIF;
	const REPLACE = QueryFunction::REPLACE;
	const ROUND = QueryFunction::ROUND;
	const RTRIM = QueryFunction::RTRIM;
	const SOUNDEX = QueryFunction::SOUNDEX;
	const TRIM = QueryFunction::TRIM;
	const UPPER = QueryFunction::UPPER;
	
	private $name;
	private $parameters;
	
	public function __construct($name, array $parameters) {
		ArgUtils::valEnum($name, self::getNames());
		ArgUtils::valArray($parameters, 'n2n\persistence\orm\criteria\item\CriteriaItem');
		
		$this->name = $name;
		$this->parameters = $parameters;
	}	
	
	public function getName() {
		return $this->name;
	}
	/**
	 * @return \n2n\persistence\orm\criteria\item\CriteriaItem[]
	 */
	public function getParameters() {
		return $this->parameters;
	}
	
	public function createQueryPoint(QueryState $queryState, QueryPointResolver $queryPointResolver): QueryPoint {
		$parameterQueryPoints = array();
		foreach ($this->parameters as $parameter) {
			$parameterQueryPoints[] = $parameter->createQueryPoint($queryState, $queryPointResolver);
		}
		
		return new FunctionQueryPoint($this->name, $parameterQueryPoints, $queryState);
	}
	
// 	public function createSelection(QueryState $queryState) {
// 		return new ConstantCriteriaSelection($queryState, $this->toQueryItem($queryState, null));
// 	}
	
	public static function isGroupFunction($name) {
		return in_array($name, self::getGroupNames());
	}
	
	public function __toString(): string {
		return $this->getName() . '(' . implode(', ', $this->getParameters()) . ')';
	}
	
	public static function getNames() {
		return array(self::COUNT, self::SUM, self::MAX, self::MIN, self::RAND, self::AVG, 
				self::ABS, self::COALESCE, self::LOWER, self::LTRIM, self::NULLIF, self::REPLACE, 
				self::ROUND, self::RTRIM, self::SOUNDEX, self::TRIM, self::TRIM, self::UPPER);
	}
	
	public static function getGroupNames() {
		return array(self::COUNT, self::SUM, self::MAX, self::MIN, self::RAND, self::AVG);
	}
}

class FunctionQueryPoint implements QueryPoint {
	private $name;
	private $parameterQueryPoints;
	private $queryState;
	
	public function __construct($name, array $parameterQueryPoints, QueryState $queryState) {
		$this->name = $name;
		$this->parameterQueryPoints = $parameterQueryPoints;
		$this->queryState = $queryState;
	}
	
	public function requestComparisonStrategy(): ComparisonStrategy {
// 		if (CriteriaFunction::isGroupFunction($this->name)) {
// 			throw new CriteriaConflictException('Illegal use of group function: ' . $this->name);
// 		}
		
		return new ComparisonStrategy(new ScalarColumnComparable(
				new QueryFunction($this->name, $this->createParameterSequence()), $this->queryState));
	}
	
	public function requestPropertyComparisonStrategy(TreePath $treePath) {
		throw $this->createException($treePath);
	}
	
	public function requestSelection(): Selection {
		return new SimpleSelection(new QueryFunction($this->name, $this->createParameterSequence()));
	}
	
	public function requestPropertySelection(TreePath $treePath) {
		throw $this->createException($treePath);
	}
	
	public function requestRepresentableQueryItem(): QueryItem {
		return new QueryFunction($this->name, $this->createParameterSequence());
	}
	
	public function requestPropertyRepresentableQueryItem(TreePath $treePath) {
		throw $this->createException($treePath);
	}
	
	private function createParameterSequence() {
		if (empty($this->parameterQueryPoints)) {
			return null;
		}
		
		$parameterQueryPoints = $this->parameterQueryPoints;
		$parameterSequence = new QueryItemSequence(array_shift($parameterQueryPoints)
				->requestRepresentableQueryItem());
		
		foreach ($parameterQueryPoints as $parameterQueryPoint) {
			$parameterSequence->add(QueryItemSequence::OPERATOR_SEQ,
					$parameterQueryPoint->requestRepresentableQueryItem());
		}
		
		return $parameterSequence;
	}
	
	private function createException(TreePath $treePath) {
		IllegalStateException::assertTrue($treePath->hasNext());
		
		return new CriteriaConflictException('Property \'' .  TreePath::prettyPropertyStr($treePath->getAll()) 
				. '\' is unreachable because \'' . TreePath::prettyPropertyStr($treePath->getDones()) 
				. '\' points to a function.');
	}
}

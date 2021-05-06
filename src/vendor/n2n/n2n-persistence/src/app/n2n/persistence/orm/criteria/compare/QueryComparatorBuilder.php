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

use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\QueryPointResolver;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\criteria\item\CriteriaItem;
use n2n\persistence\orm\criteria\item\CriteriaConstant;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\item\CriteriaPlaceholder;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use n2n\persistence\orm\criteria\CriteriaConflictException;
use n2n\util\type\TypeConstraint;
use n2n\persistence\orm\query\Placeholder;
use n2n\persistence\PdoStatement;
use n2n\persistence\orm\query\QueryConflictException;
use n2n\util\ex\NotYetImplementedException;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\meta\data\QueryResult;

class QueryComparatorBuilder {
	private $queryState;
	private $queryPointResolver;
	private $queryComparator;
	
	public function __construct(QueryState $queryState, QueryPointResolver $queryPointResolver, 
			QueryComparator $queryComparator) {
		$this->queryState = $queryState;
		$this->queryPointResolver = $queryPointResolver;
		$this->queryComparator = $queryComparator;
	}
	
	public function applyTest($operator, ComparatorCriteria $criteria, $useAnd) {
		$queryItem = $criteria->createQueryPoint($this->queryState, $this->queryPointResolver)
				->requestRepresentableQueryItem();
		IllegalStateException::assertTrue($queryItem instanceof QueryResult);
		
		$this->queryComparator->test($operator, $queryItem, $useAnd);
				
	}
	
	public function applyMatch(CriteriaItem $criteriaItem1, $operator, CriteriaItem $criteriaItem2, $useAnd) {
		try {
			if ($criteriaItem1 instanceof CriteriaProperty && $criteriaItem2 instanceof CriteriaConstant) {
				$this->buildPropertyValueComparison($criteriaItem1, $operator, $criteriaItem2, $useAnd);
				return;
			}
		
			if ($criteriaItem1 instanceof CriteriaConstant && $criteriaItem2 instanceof CriteriaProperty) {
				$this->buildPropertyValueComparison($criteriaItem2,
						self::oppositeOperator($operator), $criteriaItem1, $useAnd);
				return;
			}
			
			if ($criteriaItem1 instanceof CriteriaProperty && $criteriaItem2 instanceof CriteriaPlaceholder) {
				$this->buildPropertyPlaceholderComparison($criteriaItem1, $operator, $criteriaItem2, $useAnd);
				return;
			}
			
			if ($criteriaItem1 instanceof CriteriaPlaceholder && $criteriaItem2 instanceof CriteriaProperty) {
				$this->buildPropertyPlaceholderComparison($criteriaItem2, 
						self::oppositeOperator($operator), $criteriaItem1, $useAnd);
				return;
			}
		
			$this->buildComparison($criteriaItem1, $operator, $criteriaItem2, $useAnd);
		} catch (QueryConflictException $e) {
			throw new CriteriaConflictException('Comparison failed: ' . $criteriaItem1 . ' ' 
					. $operator . ' ' . $criteriaItem2, 0, $e);
		}
	}

	private function buildPropertyValueComparison(CriteriaProperty $criteriaProperty, $operator,
			CriteriaConstant $criteriaContstant, $useAnd) {
		$comparisonStrategy = $criteriaProperty->createQueryPoint($this->queryState, $this->queryPointResolver)
				->requestComparisonStrategy();
		$value = $criteriaContstant->getValue();
	
		try {
			self::applyPropertyValueComparison($this->queryComparator, $comparisonStrategy, $operator, $value, $useAnd);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw $this->createIncompatibleCriteriaItemsException($criteriaProperty,
					$operator, $criteriaContstant, $e);
		} catch (CriteriaConflictException $e) {
			throw $this->createIncompatibleCriteriaItemsException($criteriaProperty,
					$operator, $criteriaContstant, $e);
		}
	}
	
	/**
	 * @param QueryComparator $queryComparator
	 * @param ComparisonStrategy $comparisonStrategy
	 * @param string $operator
	 * @param mixed $value
	 * @throws CriteriaConflictException
	 * @throws ValueIncompatibleWithConstraintsException
	 */
	public static function applyPropertyValueComparison(QueryComparator $queryComparator, ComparisonStrategy $comparisonStrategy, $operator, $value, $useAnd) {
		if ($comparisonStrategy->getType() == ComparisonStrategy::TYPE_COLUMN) {
			$columnComparable = $comparisonStrategy->getColumnComparable();
				
			self::validateOperators($columnComparable, $operator);
			$typeConstraint = self::oppositeTypeConstraint($columnComparable, $operator);
		
			$typeConstraint->validate($value);
				
			$queryItem1 = $columnComparable->buildQueryItem($operator);
			$queryItem2 = $columnComparable->buildCounterpartQueryItemFromValue($operator, $value);
			
			if ($value === null) {
				if ($operator == CriteriaComparator::OPERATOR_EQUAL) {
					$operator = QueryComparator::OPERATOR_IS;
				} else if ($operator == CriteriaComparator::OPERATOR_NOT_EQUAL) {
					$operator = QueryComparator::OPERATOR_IS_NOT;
				}
			}			
				
			if ($operator == CriteriaComparator::OPERATOR_CONTAINS
					|| $operator == CriteriaComparator::OPERATOR_CONTAINS_NOT) {
				$queryComparator->match($queryItem2, self::oppositeOperator($operator), $queryItem1, $useAnd);
			} else {
				$queryComparator->match($queryItem1, $operator, $queryItem2, $useAnd);
			}
			return;
		}
		
		if ($comparisonStrategy->getType() == ComparisonStrategy::TYPE_CUSTOM) {
			$comparisonStrategy->getCustomComparable()->compareWithValue(
					self::groupQueryComparator($queryComparator, $useAnd), $operator, $value);
			return;
		}
	}
	
	private static function groupQueryComparator(QueryComparator $queryComparator, $useAnd) {
		if ($useAnd) {
			return $queryComparator->andGroup();
		}
		return $queryComparator->orGroup();
	}
	
	private function buildPropertyPlaceholderComparison(CriteriaProperty $criteriaProperty, $operator,
			CriteriaPlaceholder $criteriaPlaceholder, $useAnd) {
		throw new NotYetImplementedException('todo');
// 		$comparisonStrategy = $criteriaProperty->createQueryPoint($this->queryState, $this->queryPointResolver)
// 				->requestComparisonStrategy();
// 		$value = $criteriaContstant->getValue();
// 		if ($comparisonStrategy->getType() == ComparisonStrategy::TYPE_COLUMN) {
// 			$columnComparable = $columnComparable->getAvailableOperators();
				
// 			$this->validateOperators($columnComparable, $operator);
// 			$typeConstraint = self::oppositeTypeConstraint($columnComparable, $operator);
// 			$placeholderName = $this->queryState->createPlaceholderName();
// 			$placeholder = new ColumnComparablePlaceholder($placeholderName,
// 					$columnComparable, $operator, $typeConstraint);
// 			$this->queryState->registerPlaceholder($criteriaPlaceholder->getName(),
// 					$placeholder);
			
// 			$queryPlaceMarker = new QueryPlaceMarker($placeholderName);
	
// 			$this->queryComparator->match($comparisonStrategy->buildQueryItem($operator), $operator,
// 					$queryPlaceMarker);
// 			return;
// 		}
	
// 		if ($comparisonStrategy instanceof CustomComparable && $comparisonStrategy->isComparableWithValue($operator, $value)) {
// 			$comparisonStrategy->compareWithValue(self::groupQueryComparator($queryComparator, $useAnd), $operator, $value);
// 			return;
// 		}
	
// 		throw $this->createIncompatibleCriteriaItemsException($criteriaProperty, $operator, $criteriaContstant);
	}

	private function buildComparison(CriteriaItem $criteriaItem1,
			$operator, CriteriaItem $criteriaItem2, $and) {
		$comparisonStrategy1 = $criteriaItem1->createQueryPoint($this->queryState, $this->queryPointResolver)
				->requestComparisonStrategy();
		$comparisonStrategy2 = $criteriaItem2->createQueryPoint($this->queryState, $this->queryPointResolver)
				->requestComparisonStrategy();
	
		try {
			self::applyComparison($this->queryComparator, $comparisonStrategy1, $operator, $comparisonStrategy2, $and);
		} catch (CriteriaConflictException $e) {
			throw $this->createIncompatibleCriteriaItemsException($criteriaItem1, $operator, $criteriaItem2, $e);
		}
	}
	
	public static function applyComparison(QueryComparator $queryComparator, ComparisonStrategy $comparisonStrategy1, 
			$operator, ComparisonStrategy $comparisonStrategy2, $and) {
		
		if ($comparisonStrategy1->getType() == ComparisonStrategy::TYPE_COLUMN
				&& $comparisonStrategy2->getType() == ComparisonStrategy::TYPE_COLUMN) {
			$columnComparable1 = $comparisonStrategy1->getColumnComparable();
			$columnComparable2 = $comparisonStrategy2->getColumnComparable();
				
			$typeConstraint1 = $columnComparable1->getTypeConstraint($operator);
			$oppositeOperator = self::oppositeOperator($operator);
			$typeConstraint2 = self::oppositeTypeConstraint($columnComparable2, $oppositeOperator);
				
			if (!$typeConstraint1->isPassableBy($typeConstraint2)
					&& !$typeConstraint1->isPassableTo($typeConstraint2)) {
				throw new CriteriaConflictException();
			}

			$queryComparator->match($columnComparable1->buildQueryItem($operator), $operator,
					$columnComparable2->buildQueryItem($oppositeOperator), $and);
			return;
		}
		
		if ($comparisonStrategy1->getType() == ComparisonStrategy::TYPE_CUSTOM) {
			$comparisonStrategy1->getCustomComparable()->compareWith(
					self::groupQueryComparator($queryComparator, $and), $operator, $comparisonStrategy2);
			return;			
		} else if ($comparisonStrategy2->getType() == ComparisonStrategy::TYPE_CUSTOM) {
			$comparisonStrategy2->getCustomComparable()->compareWith(self::groupQueryComparator($queryComparator, $and),
					self::oppositeOperator($operator), $comparisonStrategy1);
			return;
		}
	}
	
	private static function validateOperators(ColumnComparable $columnComparable, $operator) {
		if (in_array($operator, $columnComparable->getAvailableOperators())) return;
		
		throw new CriteriaConflictException('Invalid operator \'' . $operator 
				. '\'. Following are allowed: '
				. implode(', ', $columnComparable->getAvailableOperators()));
	}
	
	public static function oppositeTypeConstraint(ColumnComparable $columnComparable, $operator) {
		$typeConstraint = $columnComparable->getTypeConstraint($operator);
		
		switch ($operator) {
			case CriteriaComparator::OPERATOR_IN:
			case CriteriaComparator::OPERATOR_NOT_IN:
				return TypeConstraint::createArrayLike(null, false, $typeConstraint);
			case CriteriaComparator::OPERATOR_CONTAINS:
			case CriteriaComparator::OPERATOR_CONTAINS_NOT:
				$arrayFieldConstraint = $typeConstraint->getArrayFieldTypeConstraint();
				if ($arrayFieldConstraint !== null) return $arrayFieldConstraint;
				
				throw new \InvalidArgumentException(get_class($columnComparable) 
						. '::getTypeConstraint() returns non collection TypeConstraint for operator \''
						. $operator . '\'');
			default:
				return $typeConstraint;	
		}
	}
	
	public static function oppositeOperator($operator) {
		switch ($operator) {
			case CriteriaComparator::OPERATOR_EQUAL:
			case CriteriaComparator::OPERATOR_NOT_EQUAL:
			case CriteriaComparator::OPERATOR_LIKE:
			case CriteriaComparator::OPERATOR_NOT_LIKE:
				return $operator;
			case CriteriaComparator::OPERATOR_LARGER_THAN:
				return CriteriaComparator::OPERATOR_SMALLER_THAN;
			case CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO:
				return CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO;
			case CriteriaComparator::OPERATOR_SMALLER_THAN:
				return CriteriaComparator::OPERATOR_LARGER_THAN;
			case CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO:
				return CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO;
			case CriteriaComparator::OPERATOR_IN:
				return CriteriaComparator::OPERATOR_CONTAINS;
			case CriteriaComparator::OPERATOR_NOT_IN:
				return CriteriaComparator::OPERATOR_CONTAINS_NOT;
			case CriteriaComparator::OPERATOR_CONTAINS:
				return CriteriaComparator::OPERATOR_IN;
			case CriteriaComparator::OPERATOR_CONTAINS_NOT:
				return CriteriaComparator::OPERATOR_NOT_IN;
			default:
				throw new \InvalidArgumentException();
		}
	}
	
	private function createIncompatibleCriteriaItemsException(CriteriaItem $item1, $operator,
			CriteriaItem $item2, \Exception $previous = null) {
		return new CriteriaConflictException('Invalid comparison: ' . $item1->__toString() . ' ' 
				. $operator . ' ' . $item2->__toString(), 0, $previous);
	}
}

class ColumnComparablePlaceholder implements Placeholder {
	private $placeholderName;
	private $columnComparable;
	private $operator;
	private $typeConstraint;
	
	public function __construct($placeholderName, ColumnComparable $columnComparable, $operator, TypeConstraint $typeConstraint) {
		$this->placeholderName;
		$this->columnComparable = $columnComparable;
		$this->operator = $operator;
		$this->typeConstraint = $typeConstraint;
	}
	
	public function apply(PdoStatement $stmt, $value) {
		try {
			$this->typeConstraint->validate($value);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw new \InvalidArgumentException('Invalid placeholder value.', 0, $e);
		}
		
		return $stmt->autoBindValue($this->placeholderName, 
				$this->columnComparable->parseComparableValue($this->operator, $value));
	}
}

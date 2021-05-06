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
namespace n2n\persistence\orm\query\from\meta;



use n2n\util\type\ArgUtils;
use n2n\persistence\meta\data\QueryPlaceMarker;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\util\ex\NotYetImplementedException;
use n2n\persistence\meta\data\QueryPartGroup;
use n2n\persistence\meta\data\QueryItem;
use n2n\util\type\TypeConstraint;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\criteria\compare\ColumnComparableAdapter;

class SingleTableDiscriminatorColumnComparable extends ColumnComparableAdapter {
	private $discriminatedEntityModels;
	private $queryState;
	
	public function __construct(QueryItem $comparableQueryItem, array $discriminatedEntityModels, QueryState $queryState) {
		parent::__construct(CriteriaComparator::getOperators(false), TypeConstraint::createSimple('ReflectionClass', false),
				$comparableQueryItem);
		
		$this->discriminatedEntityModels = $discriminatedEntityModels;
		$this->queryState = $queryState;
	}
	
	public function buildCounterpartQueryItemFromValue($operator, $value) {
		if ($operator != CriteriaComparator::OPERATOR_IN && $operator != CriteriaComparator::OPERATOR_NOT_IN) {
			ArgUtils::valType($value, 'ReflectionClass');
			return new QueryPlaceMarker($this->queryState->registerPlaceholderValue(
					$this->findValue($value)));
		} 
		
		ArgUtils::valArray($value, 'ReflectionClass');
		
		$queryPartGroup = new QueryPartGroup();
		foreach ($value as $fieldValue) {
			$queryPartGroup->addQueryPart(new QueryPlaceMarker($this->queryState
					->registerPlaceholderValue($this->findValue($fieldValue))));
		}
		return $queryPartGroup;
	}
	
	public function buildCounterpartPlaceholder($operator, $value) {
		throw new NotYetImplementedException();
	}
	
	const IMPOSSIBLE_TYPE_VALUE_BASE = 'impossible_type';
	
	private function findValue(\ReflectionClass $class) {
		foreach ($this->discriminatedEntityModels as $discrValue => $entityModel) {
			if ($entityModel->getClass() == $class) {
				return $discrValue;
			}
		}
		
		$discrValue = self::IMPOSSIBLE_TYPE_VALUE_BASE;
		for ($i = 0; isset($this->discriminatedEntityModels[$discrValue]); $i++) {
			$discrValue = self::IMPOSSIBLE_TYPE_VALUE_BASE . $i;
		}
		return $discrValue;
	}
}

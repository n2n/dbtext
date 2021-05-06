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

use n2n\util\type\ArgUtils;
use n2n\persistence\meta\data\QueryItem;
use n2n\util\type\TypeConstraint;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\meta\data\QueryPlaceMarker;
use n2n\persistence\meta\data\QueryPartGroup;
use n2n\util\ex\NotYetImplementedException;

class N2nLocaleColumnComparable extends ColumnComparableAdapter {
	private $queryState;
	
	public function __construct(QueryItem $comparableQueryItem, QueryState $queryState) {
		parent::__construct(CriteriaComparator::getOperators(false), 
				TypeConstraint::createSimple('n2n\l10n\N2nLocale', true), $comparableQueryItem);
		
		$this->queryState = $queryState;
	}
	
	private function buildN2nLocaleRawValue($value) {
		if ($value === null) return null;
		return $value->getId();
	}
	
	public function buildCounterpartQueryItemFromValue($operator, $value) {
		if ($operator != CriteriaComparator::OPERATOR_IN  && $operator != CriteriaComparator::OPERATOR_NOT_IN) {
			ArgUtils::valType($value, 'n2n\l10n\N2nLocale', true);
			return new QueryPlaceMarker($this->queryState->registerPlaceholderValue(
					$this->buildN2nLocaleRawValue($value)));
		} 
		
		ArgUtils::valArray($value, 'n2n\l10n\N2nLocale');
		
		$queryPartGroup = new QueryPartGroup();
		foreach ($value as $fieldValue) {
			$queryPartGroup->addQueryPart(
					new QueryPlaceMarker($this->queryState->registerPlaceholderValue(
							$this->buildN2nLocaleRawValue($fieldValue))));
		}
		return $queryPartGroup;
	}
	
	public function buildCounterpartPlaceholder($operator, $value) {
		throw new NotYetImplementedException();
	}
	
}

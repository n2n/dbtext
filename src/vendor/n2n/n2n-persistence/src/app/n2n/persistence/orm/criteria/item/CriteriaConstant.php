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

use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\QueryPointResolver;
use n2n\persistence\orm\query\QueryPoint;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;
use n2n\persistence\meta\data\QueryPlaceMarker;
use n2n\persistence\orm\criteria\compare\ScalarColumnComparable;
use n2n\persistence\orm\query\from\TreePath;
use n2n\persistence\meta\data\QueryConstant;
use n2n\persistence\orm\query\select\SimpleSelection;
use n2n\persistence\orm\query\QueryConflictException;
use n2n\persistence\orm\criteria\CriteriaConflictException;
use n2n\persistence\orm\query\select\Selection;
use n2n\persistence\meta\data\QueryItem;
use n2n\util\type\TypeUtils;

class CriteriaConstant implements CriteriaItem {
	private $value;
	
	public function __construct($value) {
		$this->value = $value;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function createQueryPoint(QueryState $queryState, QueryPointResolver $queryPointResolver): QueryPoint {
		if ($this->value !== null && !is_scalar($this->value)) {
			throw new CriteriaConflictException('CirteriaConstant is not scalar.');
		}
		
		return new ConstantQueryPoint($this->value, $queryState, $queryPointResolver);
	}
	
	public function __toString(): string {
		return '<' . TypeUtils::getTypeInfo($this->getValue()) . '>';
	}
}

class ConstantQueryPoint implements QueryPoint {
	private $value;
	private $queryState;
		
	public function __construct($value, QueryState $queryState) {
		$this->value = $value;
		$this->queryState = $queryState;
	}

	public function requestComparisonStrategy(): ComparisonStrategy {
		return new ComparisonStrategy(new ScalarColumnComparable(
				new QueryPlaceMarker($this->queryState->registerPlaceholderValue($this->value)),
				$this->queryState));
	}
	
	public function requestPropertyComparisonStrategy(TreePath $treePath) {
		throw $this->createException($treePath);
	}
	
	public function requestSelection(): Selection {
		return new SimpleSelection(new QueryConstant($this->value));
	}
	
	public function requestPropertySelection(TreePath $treePath) {
		throw $this->createException($treePath);
	}
	
	public function requestRepresentableQueryItem(): QueryItem {
		return new QueryConstant($this->value);
	}
	
	public function requestPropertyRepresentableQueryItem(TreePath $treePath) {
		throw $this->createException($treePath);
	}
	
	private function createException(TreePath $treePath) {
		return new QueryConflictException('Property path points to constant: '
				. TreePath::prettyPropertyStr($treePath->getDones()));
	}
}

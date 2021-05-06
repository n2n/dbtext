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

use n2n\persistence\orm\query\from\TreePath;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\QueryPointResolver;
use n2n\persistence\orm\query\QueryPoint;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;
use n2n\persistence\orm\query\select\Selection;
use n2n\persistence\meta\data\QueryItem;

class CriteriaProperty implements CriteriaItem {
	private $propertyNames;

	public function __construct(array $propertyNames) {
		if (empty($propertyNames)) {
			throw new \InvalidArgumentException('CriteriaProperty must not be empty');
		}
		$this->propertyNames = $propertyNames;
	}
		
	public function hasMultipleLevels() {
		return count($this->propertyNames) > 1;
	}

	public function getPropertyNames() {
		return $this->propertyNames;
	}

	public function createQueryPoint(QueryState $queryState, QueryPointResolver $queryPointResolver): QueryPoint {
		return new PropertyQueryPoint($this->propertyNames, $queryState, $queryPointResolver);
	}
	
	public function ext($propertyExpression) {
		return new CriteriaProperty(array_merge($this->propertyNames, 
				CrIt::p($propertyExpression)->getPropertyNames()));
	}
	
	public function prep($propertyExpression) {
		return new CriteriaProperty(array_merge(
				CrIt::p($propertyExpression)->getPropertyNames(), 
				$this->propertyNames));
	}
	
	public function __toString(): string {
		return TreePath::prettyPropertyStr($this->propertyNames);
	}
}


class PropertyQueryPoint implements QueryPoint {
	private $propertyNames;
	private $queryState;
	private $queryPointResolver;
	
	public function __construct(array $propertyNames, QueryState $queryState, 
			QueryPointResolver $queryPointResolver) {
		$this->propertyNames = $propertyNames;
		$this->queryState = $queryState;
		$this->queryPointResolver = $queryPointResolver;
	}
	
	public function requestComparisonStrategy(): ComparisonStrategy {
		return $this->queryPointResolver->requestPropertyComparisonStrategy(new TreePath($this->propertyNames));
	}
	
	public function requestPropertyComparisonStrategy(TreePath $treePath) {
		return $this->queryPointResolver->requestPropertyComparisonStrategy(new TreePath(
				array_merge($this->propertyNames, $treePath->getNexts())));
	}
	
	public function requestSelection(): Selection {
		return $this->queryPointResolver->requestPropertySelection(new TreePath($this->propertyNames));
	}
	
	public function requestPropertySelection(TreePath $treePath) {
		return $this->queryPointResolver->requestPropertyComparisonStrategy(new TreePath(
				array_merge($this->propertyNames, $treePath->getNexts())));
	}
	
	public function requestRepresentableQueryItem(): QueryItem {
		return $this->queryPointResolver->requestPropertyRepresentableQueryItem(
				new TreePath($this->propertyNames));
	}
					
	public function requestPropertyRepresentableQueryItem(TreePath $treePath) {
		return $this->queryPointResolver->requestPropertyRepresentableQueryItem(new TreePath(
				array_merge($this->propertyNames, $treePath->getNexts())));
	}

}

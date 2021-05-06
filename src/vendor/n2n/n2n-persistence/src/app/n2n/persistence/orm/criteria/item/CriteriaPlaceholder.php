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
use n2n\util\ex\NotYetImplementedException;
use n2n\persistence\meta\data\QueryPlaceMarker;
use n2n\persistence\orm\query\QueryConflictException;
use n2n\persistence\orm\query\from\TreePath;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;
use n2n\persistence\orm\query\select\Selection;
use n2n\persistence\meta\data\QueryItem;

class CriteriaPlaceholder implements CriteriaItem {
	private $name;
	
	public function __construct($name) {
		return $this->name = $name;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\criteria\item\CriteriaItem::createQueryPoint()
	 */
	public function createQueryPoint(QueryState $queryState, QueryPointResolver $queryPointResolver): QueryPoint {
		throw new NotYetImplementedException();
// 		return new PlaceholderQueryPoint($queryState->registerPlaceholder($this->name, new ScalarPlaceholder(
// 				$queryState->createPlaceholderName())));
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\criteria\item\CriteriaItem::__toString()
	 */
	public function __toString(): string {
		return $this->name;
	}

}

class PlaceholderQueryPoint implements QueryPoint {
	private $placeholderName;
	/**
	 * @param string $placeholderName
	 */
	public function __construct($placeholderName) {
		$this->placeholderName = $placeholderName;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPoint::requestComparisonStrategy()
	 */
	public function requestComparisonStrategy(): ComparisonStrategy {
		throw new NotYetImplementedException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPoint::requestSelection()
	 */
	public function requestSelection(): Selection {
		throw new NotYetImplementedException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPoint::requestRepresentableQueryItem()
	 */
	public function requestRepresentableQueryItem(): QueryItem {
		return new QueryPlaceMarker($this->placeholderName);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPointResolver::requestPropertyComparisonStrategy()
	 */
	public function requestPropertyComparisonStrategy(TreePath $treePath) {
		throw $this->createException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPointResolver::requestPropertySelection()
	 */
	public function requestPropertySelection(TreePath $treePath) {
		throw $this->createException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\QueryPointResolver::requestPropertyRepresentableQueryItem()
	 */
	public function requestPropertyRepresentableQueryItem(TreePath $treePath) {
		throw $this->createException();
	}
	
	private function createException() {
		return new QueryConflictException('Property path points to placeholder: '
				. TreePath::prettyPropertyStr($treePath->getDones()));
	}

	
}

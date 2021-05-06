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

// use n2n\persistence\orm\query\from\ComponentResolver;
// use n2n\persistence\orm\query\from\TreePath;
// use n2n\util\ex\UnsupportedOperationException;
// use n2n\persistence\orm\query\QueryModel;
// use n2n\persistence\orm\query\from\DecoratedColumnComparable;

// class HavingComponentResolver implements ComponentResolver {
// 	private $queryState;
// 	private $queryModel;
	
// 	public function __construct(QueryState $queryState, QueryModel $queryModel) {
// 		$this->queryState = $queryState;
// 		$this->queryModel = $queryModel;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\query\from\ComponentResolver::requestComparisonStrategy()
// 	 */
// 	public function requestComparisonStrategy(TreePath $treePath) {
// 		$comparisonStrategy = $this->findCriteriaItem($treePath)->createComparableStrategy($this->queryState, $this->queryState->getTree());
// 		if ($comparisonStrategy->getType() != ComparisonStrategy::TYPE_COLUMN) {
// 			throw new CriteriaConflictException('cannot be compared in having');
// 		}
	
// 		$columnComparable = $comparisonStrategy->getColumnComparable();
// 		$columnAlias = $this->queryState->selectQueryItem($columnComparable->getQueryItem());
	
// 		return new DecoratedColumnComparable($columnComparable, new QueryColumn($columnAlias));
// 	}
	
// 	private function findCriteriaItem(TreePath $treePath) {
// 		$propertyNames = $treePath->nexts();
	
// 		$selectedCriteriaItems = $this->queryState->getSelectedCriteriaItems();
	
// 		if (count($propertyNames) == 1) {
// 			$propertyName = current($propertyNames);
				
// 			if (isset($selectedCriteriaItems[$propertyName])) {
// 				return $selectedCriteriaItems[$propertyName];
// 			}
// 		}
	
// 		foreach ($selectedCriteriaItems as $selectedCriteriaItem) {
// 			if ($selectedCriteriaItem instanceof CriteriaProperty
// 					&& $selectedCriteriaItem->getPropertyNames() === $propertyNames) {
// 				return $selectedCriteriaItem;
// 			}
// 		}
	
// 		throw new CriteriaConflictException();
// 	}
	

// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\query\from\ComponentResolver::createSelection()
// 	 */
// 	public function createSelection(TreePath $treePath) {
// 		throw new UnsupportedOperationException();
// 	}

// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\query\from\ComponentResolver::createRepresentableQueryItem()
// 	 */
// 	public function createRepresentableQueryItem(TreePath $treePath) {
// 		throw new UnsupportedOperationException();
// 	}

	
	
	
// }

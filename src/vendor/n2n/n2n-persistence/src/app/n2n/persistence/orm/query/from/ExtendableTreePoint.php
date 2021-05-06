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
namespace n2n\persistence\orm\query\from;

use n2n\persistence\orm\model\EntityPropertyCollection;
use n2n\persistence\orm\criteria\JoinType;
use n2n\util\type\ArgUtils;
use n2n\persistence\orm\query\from\meta\TreePointMeta;
use n2n\persistence\orm\criteria\CriteriaConflictException;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\util\ex\UnsupportedOperationException;
use n2n\persistence\orm\property\QueryItemRepresentableEntityProperty;
use n2n\persistence\orm\property\ColumnComparableEntityProperty;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;
use n2n\persistence\orm\property\CustomComparableEntityProperty;
use n2n\persistence\orm\property\JoinableEntityProperty;
use n2n\persistence\orm\property\EntityProperty;

abstract class ExtendableTreePoint extends MetaTreePointAdapter {
	const CLASS_COMPARISON_PROPERTY = 'class';
	
	protected $queryState;
	protected $entityPropertyCollection;
	protected $propertyJoinTreePoints = array();
	protected $propertyComparationStrategies = array();
	protected $propertySelections = array();
	
	public function __construct(QueryState $queryState, EntityPropertyCollection $entityPropertyCollection, 
			TreePointMeta $treePoint) {
		parent::__construct($treePoint);
		$this->queryState = $queryState;
		$this->entityPropertyCollection = $entityPropertyCollection;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\from\ExtendableTreePoint::createJoinTreePoint()
	 */
	public function createPropertyJoinedTreePoint(string $propertyName, $joinType): JoinedTreePoint {
		return $this->createCustomPropertyJoinTreePoint(
				$this->entityPropertyCollection->getEntityPropertyByName($propertyName), $joinType);
	}
	
	public function createCustomPropertyJoinTreePoint(EntityProperty $entityProperty, string $joinType): JoinedTreePoint {
		$previousE = null;
		if ($entityProperty instanceof JoinableEntityProperty) {
			try {
				$joinedTreePoint = $entityProperty->createJoinTreePoint($this->treePointMeta, $this->queryState);
				ArgUtils::valTypeReturn($joinedTreePoint, JoinedTreePoint::class, $entityProperty, 
						'createJoinTreePoint');
				$joinedTreePoint->setJoinType($joinType);
				return $joinedTreePoint;
			} catch (UnsupportedOperationException $e) {
				$previousE = $e;
			}
		}
		
		throw new CriteriaConflictException('EntityProperty not joinable: '
				. $entityProperty->toPropertyString(), 0, $previousE);
	}

	public function requestPropertyJoinedTreePoint(string $propertyName, bool $innerJoinRequired): JoinedTreePoint {
		$entityProperty = $this->entityPropertyCollection->getEntityPropertyByName($propertyName);
		
		return $this->requestCustomPropertyJoinTreePoint($entityProperty, $innerJoinRequired);
	}

	public function requestCustomPropertyJoinTreePoint(EntityProperty $entityProperty, bool $innerJoinRequired): JoinedTreePoint {
		$propertyStr = $entityProperty->toPropertyString();
		
		if (!isset($this->propertyJoinTreePoints[$propertyStr])) {
			$joinType = null;
			if ($innerJoinRequired
					|| ($entityProperty instanceof JoinableEntityProperty && !in_array(JoinType::LEFT, $entityProperty->getAvailableJoinTypes()))) {
				$joinType = JoinType::INNER;
			} else {
				$joinType = JoinType::LEFT; 
			}
			
			$this->propertyJoinTreePoints[$propertyStr] = $this->createCustomPropertyJoinTreePoint($entityProperty, $joinType);
		}
		
		if ($innerJoinRequired) {
			$this->propertyJoinTreePoints[$propertyStr]->setJoinType(JoinType::INNER);
		}
		
		return $this->propertyJoinTreePoints[$propertyStr];
	}
	
	public function requestPropertyComparisonStrategy(TreePath $treePath) {
		$propertyName = $treePath->next();
		
		if ($treePath->hasNext()) {
			return $this->requestPropertyJoinedTreePoint($propertyName, false)
					->requestPropertyComparisonStrategy($treePath);
		}
		
		if (isset($this->propertyComparationStrategies[$propertyName])) {
			return $this->propertyComparationStrategies[$propertyName];
		}
		

		if ($propertyName == self::CLASS_COMPARISON_PROPERTY) {
			return $this->propertyComparationStrategies[$propertyName] = $this->treePointMeta
					->createDiscriminatorComparisonStrategy($this->queryState);
		}
		
		$previousE = null;
		$entityProperty = $this->entityPropertyCollection->getEntityPropertyByName($propertyName);

		if ($entityProperty instanceof ColumnComparableEntityProperty) {
			try {
				$columnComparable = $entityProperty
						->createColumnComparable($this, $this->queryState);
				ArgUtils::valTypeReturn($columnComparable,
						'n2n\persistence\orm\criteria\compare\ColumnComparable',
						$entityProperty, 'createColumnComparable');
				return $this->propertyComparationStrategies[$propertyName] = new ComparisonStrategy($columnComparable);
			} catch (UnsupportedOperationException $e) {
				$previousE = $e;
			}
		}

		if ($entityProperty instanceof CustomComparableEntityProperty) {
			try {
				$customComparable = $entityProperty
						->createCustomComparable($this, $this->queryState);
				ArgUtils::valTypeReturn($customComparable,
						'n2n\persistence\orm\criteria\compare\CustomComparable',
						$entityProperty, 'createCustomComparable');
				return $this->propertyComparationStrategies[$propertyName] = new ComparisonStrategy(null, $customComparable);
			} catch (UnsupportedOperationException $e) {
				$previousE = $e;
			}
		}
		
		throw new CriteriaConflictException('EntityProperty not comparable: '
				. $entityProperty->toPropertyString(), 0, $previousE);
	}
		
	private $customPropertySelections = array();
	
	public function requestPropertySelection(TreePath $treePath) {
		$propertyName = $treePath->next();
		
		if ($treePath->hasNext()) {
			return $this->requestPropertyJoinedTreePoint($propertyName, false)
					->requestPropertySelection($treePath);
		}
		
		if (isset($this->propertySelections[$propertyName])) {
			return $this->propertySelections[$propertyName];
		}
		
		if ($propertyName == self::CLASS_COMPARISON_PROPERTY) {
			return $this->propertySelections[$propertyName] = $this->treePointMeta
					->createDiscriminatorSelection();
		}
		
		$entityProperty = $this->entityPropertyCollection->getEntityPropertyByName($propertyName);
		
		$selection = $entityProperty->createSelection($this, $this->queryState);
		ArgUtils::valTypeReturn($selection, 'n2n\persistence\orm\query\select\Selection',
					$entityProperty, 'createSelection');
		return $this->customPropertySelections[$entityProperty->toPropertyString()] 
				= $this->propertySelections[$propertyName] = $selection;
	}
	
	public function requestCustomPropertySelection(EntityProperty $entityProperty) {
		$propertyString = $entityProperty->toPropertyString();
		
		if (isset($this->customPropertySelections[$propertyString])) {
			return $this->customPropertySelections[$propertyString];
		}
		
		$selection = $entityProperty->createSelection($this, $this->queryState);
		ArgUtils::valTypeReturn($selection, 'n2n\persistence\orm\query\select\Selection',
					$entityProperty, 'createSelection');
		return $this->customPropertySelections[$entityProperty->toPropertyString()] = $selection;
	}
		
	public function requestPropertyRepresentableQueryItem(TreePath $treePath) {
		$propertyName = $treePath->next();
		
		if ($treePath->hasNext()) {
			return $this->requestPropertyJoinedTreePoint($propertyName, false)
					->requestPropertyRepresentableQueryItem($treePath);
		}
		
		if ($propertyName == self::CLASS_COMPARISON_PROPERTY) {
			throw new CriteriaConflictException('Class property not representable by a QueryItem.');
		}
		
		$entityProperty = $this->entityPropertyCollection->getEntityPropertyByName($propertyName);
		$previousE = null;
		
		if ($entityProperty instanceof QueryItemRepresentableEntityProperty) {
			try {
				$queryItem = $entityProperty->createRepresentingQueryItem($this, $this->queryState);
				ArgUtils::valTypeReturn($queryItem, 'n2n\persistence\meta\data\QueryItem',
						$entityProperty, 'createRepresentingQueryItem');
				return $queryItem;
			} catch (UnsupportedOperationException $e) {
				$previousE = $e;
			}
		}
		
		throw new CriteriaConflictException('EntityProperty not representable by a QueryItem: ' 
				. $entityProperty->toPropertyString(), 0, $previousE);
	}
	
	public function apply(SelectStatementBuilder $selectBuilder) {
		foreach ($this->propertyJoinTreePoints as $joinTreePoint) {
			$joinTreePoint->apply($selectBuilder);
		}
	}
}

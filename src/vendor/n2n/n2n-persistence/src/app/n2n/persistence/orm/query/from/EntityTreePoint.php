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

use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\from\meta\TreePointMeta;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;
use n2n\persistence\orm\query\select\EntityObjSelection;
use n2n\impl\persistence\orm\property\relation\compare\IdColumnComparableDecorator;
use n2n\persistence\orm\query\select\Selection;
use n2n\persistence\meta\data\QueryItem;

abstract class EntityTreePoint extends ExtendableTreePoint {
	protected $entityModel;
	
	private $entitySelection;
	
	public function __construct(QueryState $queryState, TreePointMeta $treePointMeta) {
		parent::__construct($queryState, $treePointMeta->getEntityModel(), $treePointMeta);
		
		$this->entityModel = $treePointMeta->getEntityModel();
	}
	
	public function requestComparisonStrategy(): ComparisonStrategy {
		$idEntityProperty = $this->entityModel->getIdDef()->getEntityProperty();
		$idColumnComparable = $idEntityProperty->createColumnComparable($this, $this->queryState);
		
		return new ComparisonStrategy(new IdColumnComparableDecorator($idColumnComparable, $this->entityModel));
	}
	
	public function requestSelection(): Selection {
		if ($this->entitySelection !== null) return $this->entitySelection;
		return $this->entityselection = new EntityObjSelection($this->entityModel, $this->queryState, $this);
	}
	
	public function requestRepresentableQueryItem(): QueryItem {
		$idEntityProperty = $this->entityModel->getIdDef()->getEntityProperty();
		return $idEntityProperty->createQueryColumn($this->getMeta(), $this->queryState);
	}
}

// class EntityColumnComparable implements ColumnComparable {
// 	private $typeConstraint;
// 	private $idComparable;
// 	private $idEntityProperty;
// 	private $queryState;
	
// 	public function __construct(TypeConstraint $typeConstraint, ColumnComparable $idComparable,
// 			EntityProperty $idEntityProperty, QueryState $queryState) {
// 		$this->typeConstraint = $typeConstraint;
// 		$this->idComparable = $idComparable;
// 		$this->idEntityProperty = $idEntityProperty;
// 		$this->queryState = $queryState;
// 	}
	
// 	public function getAvailableOperators() {
// 		return $this->idComparable->getAvailableOperators();
// 	}
	
// 	public function getTypeConstraint($operator) {
// 		return $this->typeConstraint;
// 	}
	
// 	public function isSelectable($operator) {
// 		return $this->idComparable->isSelectable($operator);
// 	}
	
// 	public function buildQueryItem($operator) {
// 		return $this->idComparable->buildQueryItem($operator);
// 	}
	
// 	public function buildCounterpartQueryItemFromValue($operator, $value) {
// 		return $this->idComparable->buildCounterpartQueryItemFromValue($operator, 
// 				$this->parseComparableValue($operator, $value));	
// 	}
	
// 	private function parseComparableValue($operator, $value) {
// 		if ($operator !== CriteriaComparator::OPERATOR_IN
// 				&& $operator !== CriteriaComparator::OPERATOR_NOT_IN) {
// 			return $this->parseFieldValue($value);
// 		}
		
// 		ArgUtils::valArrayLike($value, 'object');
// 		$idValues = array();
// 		foreach ($value as $key => $fieldValue) {
// 			$idValues[$key] = $this->parseFieldValue($fieldValue);
// 		}
// 		return $idValues;
// 	}
	
// 	private function parseFieldValue($value) {
// 		if ($value === null) return null;
		
// 		ArgUtils::assertTrue(is_object($value));
// 		return $this->idEntityProperty->readValue($value);
// 	}
// }

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
namespace n2n\persistence\orm\query\select;

use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\from\MetaTreePoint;
use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\PdoStatement;
use n2n\persistence\orm\store\EntityInfo;
use n2n\persistence\orm\EntityCreationFailedException;
use n2n\persistence\orm\CorruptedDataException;
use n2n\persistence\orm\proxy\EntityProxy;

class EntityObjSelection implements Selection {
	private $em;
	private $selectionGroup;
	
	public function __construct(EntityModel $entityModel, QueryState $queryState, 
			MetaTreePoint $metaTreePoint) {
		$this->em = $queryState->getEntityManager();
		
		$this->selectionGroup = new SelectionGroup();
		$this->selectionGroup->addSelection(null, $metaTreePoint->getMeta()->createDiscriminatorSelection());
		foreach ($entityModel->getAllEntityProperties() as $entityProperty) {			
			$this->selectionGroup->addSelection($entityProperty->toPropertyString(), 
			 		$metaTreePoint->requestCustomPropertySelection($entityProperty));
		}
	}
	
// 	private function buildKey(EntityProperty $entityProperty) {
// 		return $entityProperty->getEntityModel()->getClass()->getName() 
// 				. '::$' . $entityProperty->getName();
// 	}
	/**
	 * @return \n2n\persistence\meta\data\QueryItem[]
	 */
	public function getSelectQueryItems() {
		return $this->selectionGroup->getSelectQueryItems();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\select\Selection::bindColumns()
	 */
	public function bindColumns(PdoStatement $stmt, array $columnAliases) {
		$this->selectionGroup->bindColumns($stmt, $columnAliases);
	}
	
	protected function assembleValueBuilders(EntityModel &$entityModel = null) {
		$discrSelection = $this->selectionGroup->getSelectionByKey(null);
		$entityModel = $discrSelection->determineEntityModel();
		
		if ($entityModel === null) {
			return null;
		}
		
		$valueBuilders = array();
		foreach ($entityModel->getEntityProperties() as $entityProperty) {
			$propertyString = $entityProperty->toPropertyString();
			$selection = $this->selectionGroup->getSelectionByKey($propertyString);
			$valueBuilders[$propertyString] = $selection->createValueBuilder();
		}
		return $valueBuilders;
	}
	
	public function createValueBuilder() {
		$entityModel = null;
		$valueBuilders = $this->assembleValueBuilders($entityModel);
		
		if ($valueBuilders === null) {
			return new EagerValueBuilder(null);
		} 
				
		$persistenceContext = $this->em->getPersistenceContext();
		$id = $valueBuilders[$entityModel->getIdDef()->getEntityProperty()->toPropertyString()]->buildValue();
		
		if ($id === null) {
			return new EagerValueBuilder(null);
		}
		
		$entityObj = $persistenceContext->getEntityById($entityModel, $id);
		
		if (null !== $entityObj) {
			if (!($entityObj instanceof EntityProxy) 
					|| $persistenceContext->getEntityProxyManager()->isProxyInitialized($entityObj)) {
				return new EagerValueBuilder($entityObj);
			}
		} else {
			try {
				$entityObj = $persistenceContext->createManagedEntityObj($entityModel, $id);
			} catch(EntityCreationFailedException $e) {
				throw new CorruptedDataException('Data in database incompatible with entity: ' 
						. EntityInfo::buildEntityString($entityModel, $id), 0, $e);
			}
		}
		
		return new LazyValueBuilder(function () use ($entityObj, $id, $valueBuilders) {
// 			if (!$this->persistenceContext->containsValueHashCol($entity)) {
			$values = array();
			foreach ($valueBuilders as $key => $valueBuilder) {
				$values[$key] = $valueBuilder->buildValue();
			}
			
			$this->em->getLoadingQueue()->mapValues($entityObj, $id, $values);
// 			$persistenceContext = $this->em->getPersistenceContext();
// 			$persistenceContext->mapValues($entity, $values);
// 			$persistenceContext->updateValueHashes($entity, $values, array(), $this->em);
			return $entityObj;
		});
	}
}

class SelectionGroup {
	private $columnCounter = 0;

	private $selections = array();
	private $queryItems = array();
	private $selectionCaims = array();

	public function addSelection($key, Selection $selection) {
		$this->selections[$key] = $selection;

		$caims = array();
		foreach ($selection->getSelectQueryItems() as $queryItemIndex => $queryItem) {
			$columnIndex = $this->columnCounter++;
			$this->queryItems[$columnIndex] = $queryItem;
			$caims[$queryItemIndex] = $columnIndex;
		}
		$this->selectionCaims[$key] = $caims;
	}

	public function getSelectQueryItems() {
		return $this->queryItems;
	}

	public function bindColumns(PdoStatement $stmt, array $columnAliases) {
		foreach ($this->selections as $key => $selection) {
			$selectionColumnAliases = array();
			
			foreach ($this->selectionCaims[$key] as $queryItemIndex => $columnIndex) {
				$selectionColumnAliases[$queryItemIndex] = $columnAliases[$columnIndex];
			}
				
			$selection->bindColumns($stmt, $selectionColumnAliases);
		}
	}
	
	public function getSelectionByKey($key) {
		if (isset($this->selections[$key])) {
			return $this->selections[$key];
		}
		
		throw new \InvalidArgumentException();
	}
}

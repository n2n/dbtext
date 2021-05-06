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

use n2n\persistence\meta\data\QueryTable;
use n2n\persistence\meta\data\SelectStatementSequence;
use n2n\persistence\meta\data\QueryConstant;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\orm\model\EntityModel;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;

class TablePerClassTreePointMeta extends TreePointMetaAdapter {
	private $metaData;
	private $tableAlias;

	private $discriminatorAlias;
	private $discriminatedEntityModels = array();
	private $discriminatedColumnAliases = array();
	
	private $queryColumns = array();

	public function __construct(QueryState $queryState, EntityModel $entityModel) {
		parent::__construct($queryState, $entityModel);
		
		$this->metaData = $this->queryState->getEntityManager()->getPdo()->getMetaData();
		$this->tableAlias = $queryState->createTableAlias();

		$this->registerEntityModel($entityModel);
		foreach ($entityModel->getAllSubEntityModels() as $entityModel) {
			$this->registerEntityModel($entityModel);
		}
	}
	
	public function getEntityModel(): EntityModel {
		return $this->entityModel;
	}

	private function registerEntityModel(EntityModel $entityModel) {
		$this->discriminatedEntityModels[] = $entityModel;
		$this->discriminatedColumnAliases[$entityModel->getClass()->getName()] = array();
	}

	public function registerColumn(EntityModel $entityModel, $columnName) {
		$className = $entityModel->getClass()->getName();
		
		if (!isset($this->discriminatedColumnAliases[$className][$columnName])) {
			$columnAlias = null;
			if ($this->entityModel->hasSubEntityModels()) {
				$columnAlias = $this->queryState->createColumnAlias($columnName);
			} else {
				$columnAlias = $columnName;
			} 
			
			$this->queryColumns[$columnAlias] = new QueryColumn($columnAlias, $this->tableAlias);
			$this->registerDiscriminatedColumnAlias($entityModel, $columnName, $columnAlias);
		}
		
		return $this->queryColumns[$this->discriminatedColumnAliases[$className][$columnName]];
	}

	public function getQueryColumnByName(EntityModel $entityModel, $columnName) {
		$className = $entityModel->getClass()->getName();
		
		if (!isset($this->discriminatedColumnAliases[$className][$columnName])) {
			throw IllegalStateException::createDefault();
		}

		return $this->discriminatedColumnAliases[$className][$columnName];
	}

	private function registerDiscriminatedColumnAlias(EntityModel $entityModel, $columnName, $columnAlias) {
		$className = $entityModel->getClass()->getName();
		
		if (!isset($this->discriminatedColumnAliases[$className])) {
			$this->discriminatedColumnAliases[$className] = array();
		}

		$this->discriminatedColumnAliases[$className][$columnName] = $columnAlias;

		foreach ($entityModel->getSubEntityModels() as $subEntityModel) {
			$this->registerDiscriminatedColumnAlias($subEntityModel, $columnName, $columnAlias);
		}
	}

	public function applyAsJoin(SelectStatementBuilder $selectBuilder, $joinType, QueryComparator $onComparator = null) {
		if (!$this->entityModel->hasSubEntityModels()) {
			return $selectBuilder->addJoin($joinType, new QueryTable($this->generateTableName($this->entityModel)), 
					$this->tableAlias, $onComparator);
		}

		return $selectBuilder->addJoin($joinType, $this->createSelectStatementSequence(), $this->tableAlias);
	}

	public function applyAsFrom(SelectStatementBuilder $selectBuilder) {
		if (!$this->entityModel->hasSubEntityModels()) {
			return $selectBuilder->addFrom(new QueryTable($this->generateTableName($this->entityModel)), $this->tableAlias);
		}
		
		$selectBuilder->addFrom($this->createSelectStatementSequence(), $this->tableAlias);
	}

	private function createSelectStatementSequence() {
		$sequence = null;
		foreach ($this->discriminatedEntityModels as $discriminatorValue => $entityModel) {
			$selectStatement = $this->createSelectStatement($discriminatorValue, $entityModel);
			if (is_null($sequence)) {
				$sequence = new SelectStatementSequence($selectStatement);
				continue;
			}
				
			$sequence->add(SelectStatementSequence::OPERATOR_UNION_ALL, $selectStatement);
		}
			
		return $sequence->toQueryResult();
	}

	private function createSelectStatement($discriminatorValue, EntityModel $entityModel) {
		$selectStatements = array();

		$selectBuilder = $this->metaData->createSelectStatementBuilder();
		$selectBuilder->addFrom(new QueryTable($this->generateTableName($entityModel)), null);

		if ($this->discrColumnAlias !== null) {
			$selectBuilder->addSelectColumn(new QueryConstant($discriminatorValue), $this->discrColumnAlias);
		}
		
		$availableColumnAliases = $this->discriminatedColumnAliases[$entityModel->getClass()->getName()];
		$availableColumnNames = array_flip($availableColumnAliases);
		foreach ($this->queryColumns as $columnAlias => $queryColumn) {
			if (isset($availableColumnNames[$columnAlias])) {
				$selectBuilder->addSelectColumn(new QueryColumn($availableColumnNames[$columnAlias]), $columnAlias);
				continue;
			}
			$selectBuilder->addSelectColumn(new QueryConstant(null), $columnAlias);
		}
		
		return $selectBuilder;
	}
	
	private $discrColumnAlias = null;
	
	public function createDiscriminatorSelection() {
		if (!$this->entityModel->hasSubEntityModels()) {
			return new SimpleDiscriminatorSelection($this->registerColumn($this->entityModel, 
					$this->getIdColumnName()), $this->entityModel);
		}

		if ($this->discrColumnAlias === null) {
			$this->discrColumnAlias = $this->queryState->createColumnAlias('discr');
		}
		
		
		return new SingleTableDiscriminatorSelection(new QueryColumn($this->discrColumnAlias, $this->tableAlias), 
				$this->discriminatedEntityModels);
	}
		
	public function createDiscriminatorComparisonStrategy(QueryState $queryState) {
		if (!$this->entityModel->hasSubEntityModels()) {
			return new ComparisonStrategy(new SimpleDiscriminatorColumnComparable(
					$this->entityModel->getClass(), $this->entityModel));
		}
		
		if ($this->discrColumnAlias === null) {
			$this->discrColumnAlias = $this->queryState->createColumnAlias('discr');
		}
		
		return new ComparisonStrategy(new SingleTableDiscriminatorColumnComparable(
				new QueryColumn($this->discrColumnAlias, $this->tableAlias), $this->discriminatedEntityModels));
	}
	
}

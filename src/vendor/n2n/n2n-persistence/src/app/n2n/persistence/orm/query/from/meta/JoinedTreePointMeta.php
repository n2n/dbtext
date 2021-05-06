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
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\criteria\JoinType;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\model\EntityModel;
use n2n\util\ex\IllegalStateException;
use n2n\util\ex\NotYetImplementedException;

class JoinedTreePointMeta extends TreePointMetaAdapter {
	private $superEntityModels = array(); 
	private $registeredEntityModels = array();
	private $tableAliases = array();
			 
	private $identifiable = false;
	private $discriminatedEntityModels = array();
	private $discriminatorQueryColumns = array();

	private $queryColumns = array();

	public function __construct(QueryState $queryState, EntityModel $entityModel) {
		parent::__construct($queryState, $entityModel);
		
		foreach ($entityModel->getAllSuperEntityModels() as $superEntityModel) {
			$this->superEntityModels[$superEntityModel->getClass()->getName()] = $superEntityModel;
		}

		$this->registerEntityModel($entityModel);
	}

	public function getEntityModel(): EntityModel {
		return $this->entityModel;
	}
		
	private function registerEntityModel(EntityModel $entityModel) {
		$className = $entityModel->getClass()->getName();

		if (isset($this->tableAliases[$className])) {
			return $this->tableAliases[$className];
		}

		$tableAlias = $this->queryState->createTableAlias($entityModel->getTableName());
		$this->tableAliases[$className] = $tableAlias;
		$this->registeredEntityModels[$className] = $entityModel;

		return $tableAlias;
	}
	/**
	 *
	 * @param EntityModel $entityModel
	 * @param string $columnName
	 * @param bool $select
	 * @return QueryColumn
	 */
	public function registerColumn(EntityModel $entityModel, $columnName) {
		$tableAlias = $this->registerEntityModel($entityModel);
		
		if (!isset($this->queryColumns[$tableAlias])) {
			$this->queryColumns[$tableAlias] = array();
		}
		
		if (!isset($this->queryColumns[$tableAlias][$columnName])) {
			$this->queryColumns[$tableAlias][$columnName] = new QueryColumn($columnName, $tableAlias);
		}

		return $this->queryColumns[$tableAlias][$columnName];
	}

	public function getQueryColumnByName(EntityModel $entityModel, $columnName) {
		$tableAlias = $this->registerEntityModel($entityModel);
		
		if (!isset($this->queryColumns[$tableAlias][$columnName])) {
			throw IllegalStateException::createDefault();
		}

		return $this->queryColumns[$tableAlias][$columnName];
	}

	public function applyAsFrom(SelectStatementBuilder $selectBuilder) {
// 		$this->applySelection($selectBuilder);
		
		$baseTableAlias = null;
		foreach ($this->tableAliases as $className => $tableAlias) {
			$tableName = $this->generateTableName($this->registeredEntityModels[$className]);
			
			if ($baseTableAlias === null) {
				$baseTableAlias = $tableAlias;
				$selectBuilder->addFrom(new QueryTable($tableName), $tableAlias);
				continue;
			}
			
			$this->applyJoin($selectBuilder, $className, $baseTableAlias, $tableName, $tableAlias);
		}
	}

	public function applyAsJoin(SelectStatementBuilder $selectBuilder, $joinType, QueryComparator $onComparator = null) {
// 		$this->applySelection($selectBuilder);
		
		if (count($this->tableAliases) == 1) {
			foreach ($this->tableAliases as $className => $tableAlias) {
				$tableName = $this->generateTableName($this->registeredEntityModels[$className]);
				return $selectBuilder->addJoin($joinType, new QueryTable($tableName), $tableAlias, $onComparator);
			}
		}
		
		$baseTableAlias = null;
		$joinBuilder = $this->queryState->getPdo()->getMetaData()->createSelectStatementBuilder();
				
		foreach ($this->tableAliases as $className => $tableAlias) {
			$tableName = $this->generateTableName($this->registeredEntityModels[$className]);
			
			if ($baseTableAlias === null) {
				$baseTableAlias = $tableAlias;
				$joinBuilder->addFrom(new QueryTable($tableName), $tableAlias);
				continue;
			}
				
			$this->applyJoin($joinBuilder, $className, $baseTableAlias, $tableName, $tableAlias);
		}

		return $selectBuilder->addJoin($joinType, $joinBuilder->toFromQueryResult(), null, $onComparator);
	}
	
	
	
	
	private function buildFormQueryResult() {
		
	}

	private function applyJoin(SelectStatementBuilder $selectStatementBuilder, $className, $baseTableAlias, $tableName, $tableAlias) {
		$joinType = isset($this->superEntityModels[$className]) ? JoinType::INNER : JoinType::LEFT;
		
		$selectStatementBuilder->addJoin($joinType, new QueryTable($tableName), $tableAlias)
				->match(new QueryColumn($this->getIdColumnName(), $baseTableAlias), QueryComparator::OPERATOR_EQUAL,
						new QueryColumn($this->getIdColumnName(), $tableAlias));
	}	
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\from\meta\TreePointMeta::createDiscriminatorSelection()
	 */
	public function createDiscriminatorSelection() {
		$idQueryItems = array(new QueryColumn($this->getIdColumnName(), $this->registerEntityModel($this->entityModel)));
		$entityModels = array($this->entityModel);
		
		foreach ($this->entityModel->getAllSubEntityModels() as $subEntityModel) {
			$idQueryItems[] = new QueryColumn($this->getIdColumnName(), $this->registerEntityModel($subEntityModel));
			$entityModels[] = $subEntityModel;
		}
		
		return new JoinedDiscriminatorSelection($idQueryItems, $entityModels);
	}
	
	public function createDiscriminatorComparisonStrategy(QueryState $queryState) {
		throw new NotYetImplementedException('Wenn das würklich mal öper sötti bruche, chan er sich gern melde.'
				. ' Ich bin mir aber ziemlich sicher, dass das niä de Fall si wird und de Text au nie öper läse wird.');
	}
}

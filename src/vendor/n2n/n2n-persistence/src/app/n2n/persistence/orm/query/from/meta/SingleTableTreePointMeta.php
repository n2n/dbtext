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
use n2n\persistence\meta\data\QueryConstant;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;

class SingleTableTreePointMeta extends TreePointMetaAdapter {
	private $tableAlias;

	private $discriminatorColumnName;
	private $discriminatorAlias;
	private $discriminatedEntityModels = [];
	
	public function __construct(QueryState $queryState, EntityModel $entityModel) {
		parent::__construct($queryState, $entityModel);
		
		$this->tableAlias = $queryState->createTableAlias();
		$this->discriminatorColumnName = $this->entityModel->getDiscriminatorColumnName();
		
		$this->resolveDiscriminatedEntityModels($entityModel);
	}
	
	public function getEntityModel(): EntityModel {
		return $this->entityModel;
	}
	
	public function setIdColumnName(string $idColumnname) {
	}
	
	public function getMetaColumnAliases() {
		if (isset($this->discriminatorAlias)) {
			return array($this->discriminatorColumnName => $this->discriminatorAlias);
		} 
		
		return array();
	}
	
	public function setMetaGenerator(MetaGenerator $metaGenerator = null) {
		parent::setMetaGenerator($metaGenerator);
		$this->discriminatorColumnName = $this->generateColumnName(
				$this->entityModel, $this->entityModel->getDiscriminatorColumnName());
	}

	private function resolveDiscriminatedEntityModels(EntityModel $entityModel) {
		if (!$entityModel->isAbstract()) {
			$this->discriminatedEntityModels[$entityModel->getDiscriminatorValue()] = $entityModel;
		}
		
		foreach ($entityModel->getSubEntityModels() as $entityModel) {
			$this->resolveDiscriminatedEntityModels($entityModel);
		}
	}

	public function registerColumn(EntityModel $entityModel, $columnName) {
		if (!isset($this->queryColumns[$columnName])) {
			$this->queryColumns[$columnName] = new QueryColumn($columnName, $this->tableAlias);
		}
		
		return $this->queryColumns[$columnName];
	}

	public function getQueryColumnByName(EntityModel $entityModel, $columnName) {
		if (!isset($this->queryColumns[$columnName])) {
			throw $this->queryState->createIllegalStateException();
		}

		return $this->queryColumns[$columnName];
	}

	private function applySelection(SelectStatementBuilder $selectBuilder) {
		if (is_null($this->discriminatorAlias)) return;
		
		$selectBuilder->addSelectColumn(new QueryColumn($this->discriminatorColumnName, $this->tableAlias),
 				$this->discriminatorAlias);
// 		$selectBuilder->addSelectColumn($this->getQueryColumnByName($this->discriminatorColumnName),
// 				$this->discriminatorAlias);
	}

	public function applyAsFrom(SelectStatementBuilder $selectBuilder) {
		$this->applySelection($selectBuilder);
		$selectBuilder->addFrom(new QueryTable($this->generateTableName($this->entityModel)), $this->tableAlias);

		if ($this->entityModel->hasSuperEntityModel()) {
			$this->assembleDiscrComparator($selectBuilder->getWhereComparator()->andGroup());
		}
	}

	public function applyAsJoin(SelectStatementBuilder $selectBuilder, $joinType, QueryComparator $onComparator = null) {
		$this->applySelection($selectBuilder);
		$onQueryComparator = $selectBuilder->addJoin($joinType, new QueryTable($this->generateTableName($this->entityModel)), 
				$this->tableAlias, $onComparator);

		if ($this->entityModel->hasSuperEntityModel()) {
			$this->assembleDiscrComparator($onQueryComparator->andGroup());

		}
		return $onQueryComparator;
	}

	private function assembleDiscrComparator(QueryComparator $comparator) {
		foreach ($this->discriminatedEntityModels as $discriminatorValue => $entityModel) {
			$comparator->orMatch(new QueryColumn($this->discriminatorColumnName, $this->tableAlias), '=',
					new QueryConstant($discriminatorValue));
		}
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\from\meta\TreePointMeta::createDiscriminatorSelection()
	 */
	public function createDiscriminatorSelection() {
		return new SingleTableDiscriminatorSelection(
				$this->registerColumn($this->entityModel, $this->discriminatorColumnName),
				$this->discriminatedEntityModels);
	}
	
	public function createDiscriminatorComparisonStrategy(QueryState $queryState) {
		return new ComparisonStrategy(new SingleTableDiscriminatorColumnComparable(
				$this->registerColumn($this->entityModel, $this->discriminatorColumnName), 
				$this->discriminatedEntityModels, $queryState));
	}

}

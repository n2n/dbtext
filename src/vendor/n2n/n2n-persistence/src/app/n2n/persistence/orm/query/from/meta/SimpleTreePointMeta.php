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

use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\meta\data\SelectStatementBuilder;
use n2n\persistence\orm\model\EntityModel;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\criteria\compare\ComparisonStrategy;

class SimpleTreePointMeta extends TreePointMetaAdapter {
	private $tableAlias;

	private $discriminatorQueryColumn = null;
	private $discriminatorAlias = null;

	private $queryColumns = array();

	public function __construct(QueryState $queryState, EntityModel $entityModel) {
		parent::__construct($queryState, $entityModel);
		
		$this->tableAlias = $queryState->createTableAlias($entityModel->getTableName());
	}

	public function getEntityModel(): EntityModel {
		return $this->entityModel;
	}
	
	public function setIdColumnName(string $idColumnname) {		
	}
	
	public function registerColumn(EntityModel $entityModel, $columnName) {		
		if (!isset($this->queryColumns[$columnName])) {
			$this->queryColumns[$columnName] = new QueryColumn($columnName, $this->tableAlias);
		}

		return $this->queryColumns[$columnName];
	}

	public function getQueryColumnByName(EntityModel $entityModel, $columnName) {		
		if (!isset($this->queryColumns[$columnName])) {
			throw IllegalStateException::createDefault();
		}

		return $this->queryColumns[$columnName];
	}

	private function applySelection(SelectStatementBuilder $selectBuilder) {
		if (!isset($this->discriminatorQueryColumn)) return;
			
		$selectBuilder->addSelectColumn($this->discriminatorQueryColumn, 
				$this->discriminatorAlias);
	}

	public function applyAsFrom(SelectStatementBuilder $selectBuilder) {
		$this->applySelection($selectBuilder);

		$selectBuilder->addFrom(new QueryTable($this->generateTableName($this->entityModel)), $this->tableAlias);
	}

	public function applyAsJoin(SelectStatementBuilder $selectBuilder, $joinType, QueryComparator $onComparator = null) {
		$this->applySelection($selectBuilder);

		return $selectBuilder->addJoin($joinType, new QueryTable($this->generateTableName($this->entityModel)), $this->tableAlias, $onComparator);
	}
	
	public function createDiscriminatorSelection() {
		return new SimpleDiscriminatorSelection($this->registerColumn($this->entityModel, 
				$this->getIdColumnName()), $this->entityModel);
	}
	
	public function createDiscriminatorComparisonStrategy(QueryState $queryState) {
		return new ComparisonStrategy(new SimpleDiscriminatorColumnComparable($this->entityModel->getClass(), $queryState));
	}
}

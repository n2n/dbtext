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
namespace n2n\persistence\meta\data\common;

use n2n\persistence\meta\data\QueryComparator;

use n2n\persistence\Pdo;

use n2n\persistence\meta\data\DeleteStatementBuilder;

class CommonDeleteStatementBuilder implements DeleteStatementBuilder {
	
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	private $table;
	private $whereSelector;
	/**
	 * @var \n2n\persistence\meta\data\common\QueryFragmentBuilderFactory
	 */
	private $fragmentBuilderFactory;
	
	public function __construct(Pdo $dbh, QueryFragmentBuilderFactory $fragmentBuilderFactory) {
		$this->dbh = $dbh;
		$this->whereSelector = new QueryComparator();
		$this->fragmentBuilderFactory = $fragmentBuilderFactory;
	}
	
	public function setTable($tableName, $tableAlias = null) {
		$this->table = array('tableName' => $tableName, 'tableAlias' => $tableAlias);
	}
	
	public function getWhereComparator() {
		return $this->whereSelector;
	}
	
	public function toSqlString() {
		return $this->buildDeleteSql() . $this->buildWhereSql();
	}
	
	private function buildDeleteSql() {
		$sql = 'DELETE FROM ' . $this->dbh->quoteField($this->table['tableName']);
	
		if (isset($this->table['tableAlias'])) {
			$sql .= $this->dbh->quoteField($this->table['tableAlias']);
		}
	
		return $sql;
	}
	
	private function buildWhereSql() {
		if (is_null($this->whereSelector) || $this->whereSelector->isEmpty()) {
			return '';
		}
		$fragmentBuilder = $this->fragmentBuilderFactory->create();
		$this->whereSelector->buildQueryFragment($fragmentBuilder);
		return ' WHERE' . $fragmentBuilder->toSql();
	}
}

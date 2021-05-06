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
namespace n2n\impl\persistence\meta\mysql;

use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\View;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\Pdo;

class MysqlCreateStatementBuilder {
	
	/**
	 * @var Pdo
	 */
	private $dbh;
	
	/**
	 * @var MetaEntity
	 */
	private $metaEntity;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	} 
	
	public function setMetaEntity(MetaEntity $metaEntity) {
		$this->metaEntity = $metaEntity;
	}
	
	public function getMetaEntity() {
		return $this->metaEntity;
	}
	
	public function toSqlString($replace = false, $formatted = false) {
		$sqlString = '';
		foreach ($this->createSqlStatements($replace, $formatted) as $sql) {
			$sqlString .= $sql;
			
			if ($formatted) {
				$sqlString .= PHP_EOL;
			}
			
		}
		return $sqlString;
	}
	
	public function createMetaEntity() {
		foreach ($this->createSqlStatements() as $sql) {
			$this->dbh->exec($sql);
		}
	}
	
	public function createSqlStatements(bool $replace = false, bool $formatted = false) {
		$sqlStatements = array();
		$sql = '';
		
		$columnStatementStringBuilder = new MysqlColumnStatementStringBuilder($this->dbh);
		$indexStatementStringBuilder = new MysqlIndexStatementStringBuilder($this->dbh);
		
		$metaEntity = $this->getMetaEntity();
		
		if ($metaEntity instanceof View) {
			if ($replace) {
				$sqlStatements[] = 'DROP VIEW IF EXISTS ' . $this->dbh->quote($metaEntity->getName()) . ';';
			}
			$sqlStatements[] = 'CREATE VIEW ' . $this->dbh->quoteField($metaEntity->getName()) . ' AS ' . $metaEntity->getQuery() . ';';
		} elseif ($metaEntity instanceof Table) {
			if ($replace) {
				$sqlStatements[] = 'DROP TABLE IF EXISTS ' . $this->dbh->quoteField($this->metaEntity->getName()) . ';';
			}
			
			$sql = 'CREATE TABLE ' . $this->dbh->quoteField($this->metaEntity->getName()) . ' ( ';
			$first = true;
			foreach ($metaEntity->getColumns() as $column) {
				if (!$first) {
					$sql .= ', ';
				} else {
					$first = false;
				}
				if ($formatted) {
					$sql .= PHP_EOL . "\t";
				}
				$sql .= $columnStatementStringBuilder->generateStatementString($column);
			}
			
			//Primary Key
			$primaryKey = $metaEntity->getPrimaryKey();
			if ($primaryKey) {
				$sql .= ', ';
				if ($formatted) {
					$sql .= PHP_EOL . "\t";
				}
				$sql .= 'PRIMARY KEY (';
				$first = true;
				foreach ($primaryKey->getColumns() as $column) {
					if (!$first) {
						$sql .= ', ';
					} else {
						$first = false;
					}
					$sql .= $this->dbh->quoteField($column->getName());
				}
				$sql .= ')';
			}
			if ($formatted) {
				$sql .= PHP_EOL;
			}
			$sql .= ')' . $this->generateAdditionalAttributes() . ' ;';
			
			//Default Charset, engine and collation
			
			$sqlStatements[] = $sql;
			$indexes = $metaEntity->getIndexes();
			foreach ($indexes as $index) {
				if ($index->getType() == IndexType::PRIMARY) continue;
				$sqlStatements[] = 'ALTER TABLE ' . $this->dbh->quoteField($this->metaEntity->getName()) . ' ADD ' 
						. $indexStatementStringBuilder->generateCreateStatementString($index) . ';';
			}
		}
		
		return $sqlStatements;
	}
	
	private function generateAdditionalAttributes() {
		$sql = '';
		$attrs = $this->metaEntity->getAttrs();
		if (isset($attrs[MysqlTable::ATTRS_ENGINE])) {
			$sql .= ' ENGINE=' . $attrs[MysqlTable::ATTRS_ENGINE];
		}
		
		if (isset($attrs[MysqlTable::ATTRS_DEFAULT_CHARSET])) {
			$sql .= ' DEFAULT CHARSET=' . $attrs[MysqlTable::ATTRS_DEFAULT_CHARSET];
		}
		
		if (isset($attrs[MysqlTable::ATTRS_TABLE_COLLATION])) {
			$sql .= ' COLLATE ' . $attrs[MysqlTable::ATTRS_TABLE_COLLATION];
		}
		return $sql;
	}
}

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
namespace n2n\impl\persistence\meta\mssql;

use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\View;
use n2n\persistence\meta\structure\UnknownMetaEntityException;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\Pdo;

class MssqlCreateStatementBuilder {
	
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
	
	public function createSqlStatements($replace = false, $formatted = false) {
		if (!(isset($this->metaEntity))) {
			throw new UnknownMetaEntityException('n2n_persistence_meta_mssql_meta_entity_not_set');
		}
		
		$sqlStatements = array();
		$sql = '';
		
		$columnStatementStringBuilder = new MssqlColumnStatementStringBuilder($this->dbh);
		$indexStatementStringBuilder = new MssqlIndexStatementStringBuilder($this->dbh);
		
		$metaEntity = $this->getMetaEntity();
		if ($metaEntity instanceof View) {
			if ($replace) {
				$sqlStatements[] = 'IF EXISTS (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.' . $this->dbh->quoteField('TABLES') 
						. ' WHERE ' . $this->dbh->quoteField('TABLE_NAME') . ' =\'' . $metaEntity->getName() . '\' AND ' 
						. $this->dbh->quoteField('TABLE_TYPE') . ' = \'' . MssqlMetaEntityBuilder::TABLE_TYPE_VIEW .'\')' 
						. ' DROP VIEW ' . $this->dbh->quoteField($metaEntity->getName()) . ';';
			}
			if ($formatted) {
				$sqlStatements['GO'];
			}
			$sqlStatements[] = 'CREATE VIEW ' . $this->dbh->quoteField($metaEntity->getName()) . ' AS ' . $metaEntity->getQuery() . ';';
			if ($formatted) {
				$sqlStatements['GO'];
			}
		} elseif ($metaEntity instanceof Table) {
			if ($replace) {
				$sqlStatements[] = 'IF EXISTS (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.' . $this->dbh->quoteField('TABLES') 
						. ' WHERE ' . $this->dbh->quoteField('TABLE_NAME') . ' = ' . $this->dbh->quote($metaEntity->getName()) . ' AND ' 
						. $this->dbh->quoteField('TABLE_TYPE') . ' = \'' . MssqlMetaEntityBuilder::TABLE_TYPE_BASE_TABLE .'\')' 
						. ' DROP TABLE ' . $this->dbh->quoteField($metaEntity->getName()) . ';';
						
			}
			$sql = 'CREATE TABLE ' . $this->dbh->quoteField($metaEntity->getName()) . ' ( ';
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
				if ($formatted) {
					$sql .= PHP_EOL . "\t";
				}
				$sql .= ', PRIMARY KEY (';
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
			$sql .= ');';
	
			$sqlStatements[] = $sql;
	
			$indexes = $metaEntity->getIndexes();
			foreach ($indexes as $index) {
				if ($index->getType() == IndexType::PRIMARY) continue;
				$sqlStatements[] = $indexStatementStringBuilder->generateCreateStatementStringForIndex($index) . ';';
			}
		}
		
		return $sqlStatements;
	}
}

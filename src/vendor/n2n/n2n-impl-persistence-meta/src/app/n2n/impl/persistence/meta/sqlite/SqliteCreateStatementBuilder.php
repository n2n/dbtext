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
namespace n2n\impl\persistence\meta\sqlite;

use n2n\persistence\meta\structure\View;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\Column;
use n2n\util\type\CastUtils;
use n2n\persistence\meta\structure\Index;

class SqliteCreateStatementBuilder {
	
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
	
	public function toSqlString($replace = false, $formated = false) {
		$sqlString = '';
		foreach ($this->createSqlStatements($replace, $formated) as $sql) {
			$sqlString .= $sql;
			if ($formated) {
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
		$sqlStatements = array();
		$sql = '';
		
		$columnStatementStringBuilder = new SqliteColumnStatementStringBuilder($this->dbh);
		$indexStatementStringBuilder = new SqliteIndexStatementStringBuilder($this->dbh);
		
		$metaEntity = $this->getMetaEntity();
		
		if ($metaEntity instanceof View) {
			if ($replace) {
				$sqlStatements[] = 'DROP VIEW IF EXISTS ' . $this->dbh->quoteField($metaEntity->getName()) . ';';
			}
			$sqlStatements[] = 'CREATE VIEW ' . $this->dbh->quoteField($metaEntity->getName()) . ' AS ' 
					. $metaEntity->getQuery() . ';';
		
		} elseif ($metaEntity instanceof Table) {
			if ($replace) {
				$sqlStatements[] = 'DROP TABLE IF EXISTS ' . $this->dbh->quoteField($metaEntity->getName()) . ';';
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
				$sql .= ', PRIMARY KEY ' . $this->buildColumnsString($primaryKey->getColumns());
			}
			
			$indexes = $metaEntity->getIndexes();
			foreach ($indexes as $index) {
				CastUtils::assertTrue($index instanceof Index);
				if ($index->getType() != IndexType::FOREIGN) continue;
				if ($formatted) {
					$sql .= PHP_EOL . "\t";
				}
				$sql .= ', FOREIGN KEY' . $this->buildColumnsString($index->getColumns()) . ' REFERENCES ' 
						. $index->getRefTable()->getName() . $this->buildColumnsString($index->getRefColumns());
			}
			
			if ($formatted) {
				$sql .= PHP_EOL;
			}
			$sql .= ');';
	
			$sqlStatements[] = $sql;
	
			foreach ($indexes as $index) {
				if ($index->getType() == IndexType::PRIMARY || $index->getType() == IndexType::FOREIGN) continue;
				
				$sqlStatements[] = $indexStatementStringBuilder->generateCreateStatementString($index) . ';';
			}
		}
		
		return $sqlStatements;
	}
	
	private function buildColumnsString(array $columns) {
		$str = '(';
		$first = true;
		foreach ($columns as $column) {
			CastUtils::assertTrue($column instanceof Column);
			if (!$first) {
				$str .= ', ';
			} else {
				$first = false;
			}
			$str .= $this->dbh->quoteField($column->getName());
		}
		return $str . ')';
	}
}

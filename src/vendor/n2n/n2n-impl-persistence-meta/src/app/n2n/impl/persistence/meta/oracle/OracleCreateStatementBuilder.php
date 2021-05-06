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
namespace n2n\impl\persistence\meta\oracle;

use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\View;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\Pdo;

class OracleCreateStatementBuilder {
	
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
		
		$metaEntity = $this->getMetaEntity();
		if ($metaEntity instanceof Table) {
			//set the columns of the table with the persisted ones, that we get everything the Oracle DBMS sets by itself
			$metaEntityBuilder = new OracleMetaEntityBuilder($this->dbh, $this->metaEntity->getDatabase());
			$metaEntity->setColumns($metaEntityBuilder->createTable($this->metaEntity->getName())->getColumns());
		}
	}
	
	public function createSqlStatements($replace = false, $formatted = false) {
		$sqlStatements = array();
		$sql = '';
		
		$columnStatementStringBuilder = new OracleColumnStatementStringBuilder($this->dbh);
		$indexStatementStringBuilder = new OracleIndexStatementStringBuilder($this->dbh);
		
		$metaEntity = $this->getMetaEntity();
		if ($metaEntity instanceof View) {
			if ($replace) {
				$sqlStatement = 'CREATE OR REPLACE VIEW ' . $this->dbh->quoteField($metaEntity->getName()) . ' AS ' . $metaEntity->getQuery();
				if ($formatted) {
					$sqlStatement .= ';';
				}
				$sqlStatements[] = $sqlStatement;
			} else {
				$sqlStatement = 'CREATE VIEW ' . $metaEntity->getName() . ' AS ' . $metaEntity->getQuery();
				if ($formatted) {
					$sqlStatement .= ';';
				}
				$sqlStatements[] = $sqlStatement;
			}
		} elseif ($metaEntity instanceof Table) {
			if ($replace) {
				$sqlStatements[] = 'BEGIN EXECUTE IMMEDIATE \'DROP TABLE ' . $this->dbh->quoteField($this->metaEntity->getName()) . '\'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF;END;';
				if ($formatted) {
					$sqlStatements[] = '/';
				}
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
			$sql .= ')';
			if ($formatted) {
				$sql .= ';';
			}
			$sqlStatements[] = $sql;
			$indexes = $metaEntity->getIndexes();
			foreach ($indexes as $index) {
				if ($index->getType() == IndexType::PRIMARY) continue;
				if ($replace) {
					$sqlStatements[] = 'DECLARE COUNT_INDEXES INTEGER; BEGIN SELECT COUNT(*) INTO COUNT_INDEXES FROM USER_INDEXES WHERE INDEX_NAME = ' . $this->dbh->quote($index->getName()) . '; IF COUNT_INDEXES > 0 THEN
							EXECUTE IMMEDIATE \'DROP INDEX ' . $this->dbh->quoteField($index->getName()) . '\';END IF;END;';
					if ($formatted) {
						$sqlStatements[] = '/';
					}
				}
				$sqlStatement = $indexStatementStringBuilder->generateCreateStatementString($this->getMetaEntity(), $index);
				if ($formatted) {
					$sqlStatement .= ';';
				}
				$sqlStatements[] = $sqlStatement;
			}
		}
		return $sqlStatements;
	}
}

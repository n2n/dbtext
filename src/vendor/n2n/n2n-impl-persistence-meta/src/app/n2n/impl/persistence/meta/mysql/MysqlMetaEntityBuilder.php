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

use n2n\persistence\meta\structure\Size;

use n2n\persistence\meta\structure\Table;

use n2n\persistence\meta\structure\common\CommonIndex;

use n2n\persistence\meta\structure\common\CommonView;

use n2n\persistence\meta\structure\common\CommonFloatingPointColumn;

use n2n\persistence\meta\structure\common\CommonFixedPointColumn;

use n2n\persistence\meta\structure\common\CommonBinaryColumn;

use n2n\persistence\meta\structure\common\CommonTextColumn;

use n2n\persistence\meta\structure\common\CommonEnumColumn;

use n2n\persistence\meta\structure\common\CommonStringColumn;

use n2n\persistence\meta\structure\IndexType;

use n2n\persistence\Pdo;
use n2n\util\type\CastUtils;
use n2n\persistence\meta\Database;
use n2n\persistence\meta\structure\common\MetaEntityAdapter;
use n2n\persistence\meta\structure\common\ForeignIndex;
use n2n\util\StringUtils;

class MysqlMetaEntityBuilder {
	
	const TABLE_TYPE_BASE_TABLE = 'BASE TABLE';
	const TABLE_TYPE_VIEW = 'VIEW';
	
	/**
	 * @var Pdo
	 */
	private $dbh;
	private $columnDefaultUnsetPossible = false;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
		
		$this->applyColumnDefaultUnsetPossible();
	}
	
	public function createMetaEntityFromDatabase(Database $database, string $name) {
		$metaEntity = $this->createMetaEntity($database->getName(), $name);
		CastUtils::assertTrue($metaEntity instanceof MetaEntityAdapter);
		$metaEntity->setDatabase($database);
		$metaEntity->registerChangeListener($database);
		if ($metaEntity instanceof Table) {
			$this->applyIndexesForTable($database->getName(), $metaEntity);
		}
		
		return $metaEntity;
	}
	
	/**
	 * @param string $name
	 * @return \n2n\persistence\meta\structure\MetaEntity
	 */
	public function createMetaEntity(string $dbName, string $name, $applyIndexes = false) {
		$metaEntity = null;
		$statement = $this->dbh->prepare('SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = :TABLE_SCHEMA AND TABLE_NAME = :TABLE_NAME');
		$statement->execute(array(':TABLE_SCHEMA' => $dbName, ':TABLE_NAME' => $name));
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		
		$tableType = $result['TABLE_TYPE'];
		switch ($tableType) {
			case self::TABLE_TYPE_BASE_TABLE:
				$table = new MysqlTable($name);
				$table->setColumns($this->getColumnsForTable($dbName, $table));
				$table->setAttrs($result);
				
				//get the default Charset
				$characterSetStatement = $this->dbh->prepare('SHOW COLLATION LIKE :COLLATION');
				$characterSetStatement->execute(array(':COLLATION' => $result[MysqlTable::ATTRS_TABLE_COLLATION]));
				if (null != ($characterSetResult = $characterSetStatement->fetch(Pdo::FETCH_ASSOC))) {
					$table->setAttrs(array_merge(array(MysqlTable::ATTRS_DEFAULT_CHARSET => $characterSetResult['Charset']), 
							$table->getAttrs()));
				}
				
				$metaEntity = $table;
				if ($applyIndexes) {
					$this->applyIndexesForTable($dbName, $table);
				}
				break;
			case self::TABLE_TYPE_VIEW:
				$viewStatement = $this->dbh->prepare('SELECT * FROM information_schema.VIEWS WHERE TABLE_SCHEMA = :TABLE_SCHEMA AND TABLE_NAME = :TABLE_NAME');
				$viewStatement->execute(array(':TABLE_SCHEMA' => $dbName, ':TABLE_NAME' => $name));
				$viewResult = $viewStatement->fetch(Pdo::FETCH_ASSOC);
					
				$view = new CommonView($name, $viewResult['VIEW_DEFINITION']);
				$view->setAttrs($viewResult);
				$metaEntity = $view;
				break;
		}
		
		return $metaEntity;
	}
	
	private function getColumnsForTable(string $dbName, MysqlTable $table) {
		$columns = array();
		//show tables not sufficient to get the character set
		$stmt = $this->dbh->prepare('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :TABLE_SCHEMA AND TABLE_NAME = :TABLE_NAME');
		$stmt->execute(array(':TABLE_SCHEMA' => $dbName, ':TABLE_NAME' => $table->getName()));
			
		while (null != ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
			$column = null;
			switch ($row['DATA_TYPE']) {
				case 'int':
					$column = new MysqlIntegerColumn($row['COLUMN_NAME'], Size::INTEGER, !(is_numeric(stripos($row['COLUMN_TYPE'], "unsigned"))));
					break;
	
				case 'tinyint':
					$column = new MysqlIntegerColumn($row['COLUMN_NAME'], Size::SHORT, !(is_numeric(stripos($row['COLUMN_TYPE'], "unsigned"))));
					break;
						
				case 'smallint':
					$column = new MysqlIntegerColumn($row['COLUMN_NAME'], Size::MEDIUM, !(is_numeric(stripos($row['COLUMN_TYPE'], "unsigned"))));
					break;
						
				case 'mediumint':
					$column = new MysqlIntegerColumn($row['COLUMN_NAME'], MysqlSize::NUM_BITS_MEDIUMINT, !(is_numeric(stripos($row['COLUMN_TYPE'], "unsigned"))));
					break;
						
				case 'bigint':
					$column = new MysqlIntegerColumn($row['COLUMN_NAME'], Size::LONG, !(is_numeric(stripos($row['COLUMN_TYPE'], "unsigned"))));
					break;
						
				case 'varchar':
				case 'char':
					$column = new CommonStringColumn($row['COLUMN_NAME'], $row['CHARACTER_MAXIMUM_LENGTH'], $row['CHARACTER_SET_NAME']);
					break;
						
				case 'enum':
					$column = new CommonEnumColumn($row['COLUMN_NAME'], $this->parseOptions($row['COLUMN_TYPE']));
					break;
						
				case 'text':
				case 'tinytext':
				case 'mediumtext':
				case 'longtext':
					$column = new CommonTextColumn($row['COLUMN_NAME'], $row['CHARACTER_MAXIMUM_LENGTH'] * 8, $row['CHARACTER_SET_NAME']);
					break;
				case 'binary':
				case 'varbinary':
				case 'blob':
				case 'tinyblob':
				case 'mediumblob':
				case 'longblob':
					$column = new CommonBinaryColumn($row['COLUMN_NAME'], $row['CHARACTER_MAXIMUM_LENGTH'] * 8);
					break;
				case 'decimal':
					$numIntegerDigits = intval($row['NUMERIC_PRECISION']) - intval($row['NUMERIC_SCALE']);
					$column = new CommonFixedPointColumn($row['COLUMN_NAME'], $numIntegerDigits, $row['NUMERIC_SCALE']);
					break;
				case 'datetime':
				case 'timestamp':
					$column = new MysqlDateTimeColumn($row['COLUMN_NAME'], true, true);
					break;
				case 'date':
					$column = new MysqlDateTimeColumn($row['COLUMN_NAME'], true, false);
					break;
				case 'time':
					$column = new MysqlDateTimeColumn($row['COLUMN_NAME'], false, true);
					break;
				case 'year':
					$column = new MysqlDateTimeColumn($row['COLUMN_NAME'], false, false);
					break;
				case 'float':
					$column = new CommonFloatingPointColumn($row['COLUMN_NAME'], Size::FLOAT);
					break;
				case 'double':
					$column = new CommonFloatingPointColumn($row['COLUMN_NAME'], Size::DOUBLE);
					break;
				default:
					$column = new MysqlDefaultColumn($row['COLUMN_NAME']);
			}
			
			$column->setNullAllowed($row['IS_NULLABLE'] == 'YES');
			if ($this->columnDefaultUnsetPossible) {
				$columnDefault = $row['COLUMN_DEFAULT'];
				if ($columnDefault === null) {
					$column->setDefaultValueAvailable(false);
				} else {
					if ($columnDefault === 'NULL') {
						$column->setDefaultValue(null);
					} else {
						if (StringUtils::startsWith('\'', $columnDefault) && StringUtils::endsWith('\'', $columnDefault)) {
							$column->setDefaultValue(mb_substr($row['COLUMN_DEFAULT'], 1, -1));
						} else {
							$column->setDefaultValue(($row['COLUMN_DEFAULT']));
						}
					}
				}
			} else {
				$column->setDefaultValue($row['COLUMN_DEFAULT']);
			}
			
			if (is_numeric(strpos($row['EXTRA'], 'auto_increment'))) {
				$this->dbh->getMetaData()->getDialect()->applyIdentifierGeneratorToColumn($this->dbh, $column);
			}
			
			$column->setAttrs($row);
			$columns[$row['COLUMN_NAME']] = $column;
		}
		return $columns;
	}
	
	
	private function applyColumnDefaultUnsetPossible() {
		$stmt = $this->dbh->prepare('SELECT VERSION() AS version');
		$stmt->execute();
		
		$matches = [];
		if (!preg_match('/(\d+)\.(\d+)\.\d+\-MariaDB/', $stmt->fetch(PDO::FETCH_ASSOC)['version'], $matches)) {
			$this->columnDefaultUnsetPossible = false;
			return;
		}
		
		$this->columnDefaultUnsetPossible = $matches[1] >= 10 && $matches[2] >= 2;
	}
	
	
	
	private function parseOptions($columnType) {
		return explode(',', preg_replace('/(^enum\(|\)$|\')/', '', $columnType));
	}
	
	public function applyIndexesForTable(string $dbName, Table $table) {
		$sql = 'SHOW INDEX FROM ' . $this->dbh->quoteField($table->getName()) . ' FROM ' . $this->dbh->quoteField($dbName) ;
		$statement = $this->dbh->prepare($sql);
		$statement->execute();
		$results = $statement->fetchAll(Pdo::FETCH_ASSOC);
		$indexes = [];
		foreach ($results as $result) {
			$indexName = $result['Key_name'];
			if (isset($indexes[$indexName])) continue;
	
			$type = null;
			if ($result['Key_name'] == MysqlTable::KEY_NAME_PRIMARY) {
				$type = IndexType::PRIMARY;
			} else {
				$indexSql = 'SHOW INDEX FROM ' . $this->dbh->quoteField($table->getName()) . ' FROM ' 
						. $this->dbh->quoteField($dbName) .  ' WHERE Key_name = :indexName';
				$indexStatement = $this->dbh->prepare($indexSql);
				$indexStatement->execute([':indexName' => $indexName]);
				$indexResult = $indexStatement->fetch(Pdo::FETCH_ASSOC);
				if ($indexResult['Index_type'] == MysqlTable::INDEX_TYPE_FULLTEXT) {
					$type = IndexType::INDEX;
				} elseif ($indexResult['Non_unique']) {
					$type = IndexType::INDEX;
				} else {
					$type = IndexType::UNIQUE;
				}
			}
	
			$columnNames = array();
			$columnsSql = 'SHOW INDEX FROM ' . $this->dbh->quoteField($table->getName()) . ' FROM ' 
					. $this->dbh->quoteField($dbName) . ' WHERE Key_name = :Key_name';
			$columnsStatement = $this->dbh->prepare($columnsSql);
			$columnsStatement->execute(array(':Key_name' => $indexName));
			$columnsResults = $columnsStatement->fetchAll(Pdo::FETCH_ASSOC);
			foreach ($columnsResults as $columnResult) {
				$columnNames[$columnResult['Column_name']] = $columnResult['Column_name'];
			}
			
			$refTable = null;
			$refColumnNames = [];
			if ($type === IndexType::INDEX) {
				
				$fkSql = 'SELECT ' . $this->dbh->quoteField('REFERENCED_TABLE_NAME') . ', ' 
								. $this->dbh->quoteField('REFERENCED_COLUMN_NAME') . ' '
								. 'FROM ' . $this->dbh->quoteField('information_schema') . '.'
								. $this->dbh->quoteField('KEY_COLUMN_USAGE') . ' WHERE ' 
								. $this->dbh->quoteField('CONSTRAINT_NAME') . ' = :indexName AND '
								. $this->dbh->quoteField('TABLE_NAME') . ' = :tableName AND '
								. $this->dbh->quoteField('TABLE_SCHEMA') . ' = :dbName';
				$fkStatement = $this->dbh->prepare($fkSql);
				$fkStatement->execute([':indexName' => $indexName, 
						':tableName' => $table->getName(), ':dbName' => $dbName]);
				$fkResult = $fkStatement->fetchAll(Pdo::FETCH_ASSOC);
				if (count($fkResult) > 0) {
					$type = IndexType::FOREIGN;
					foreach ($fkResult as $fkResultEntry) {
						if (null === $refTable) {
							$refTable = $table->getDatabase()->getMetaEntityByName($fkResultEntry['REFERENCED_TABLE_NAME']);
						}
						$refColumnNames[] = $fkResultEntry['REFERENCED_COLUMN_NAME'];
					}
				}
			}
			$index = null;
			if ($type !== IndexType::FOREIGN) {
				$index = CommonIndex::createFromColumnNames($table, $indexName, $type, $columnNames);
			} else {
				$index = ForeignIndex::createFromColumnNames($table, $indexName, $columnNames, $refTable, $refColumnNames);
			}
			$index->setAttrs($result);
			$indexes[$indexName] = $index;
		}
		
		$table->setIndexes($indexes);
	}
}

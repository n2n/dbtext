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

use n2n\persistence\meta\structure\common\CommonIndex;
use n2n\persistence\meta\structure\common\CommonFloatingPointColumn;
use n2n\persistence\meta\structure\common\CommonFixedPointColumn;
use n2n\persistence\meta\structure\common\CommonBinaryColumn;
use n2n\persistence\meta\structure\common\CommonView;
use n2n\persistence\meta\structure\common\CommonTextColumn;
use n2n\persistence\meta\structure\common\CommonStringColumn;
use n2n\persistence\meta\structure\Size;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\Pdo;
use n2n\persistence\meta\Database;
use n2n\util\type\CastUtils;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\common\MetaEntityAdapter;

class MssqlMetaEntityBuilder {
	
	const TABLE_TYPE_BASE_TABLE = 'BASE TABLE';
	const TABLE_TYPE_VIEW = 'VIEW';
	const INDEX_TYPE_DESC_HEAP = 'HEAP';
	
	/**
	 * @var Pdo
	 */
	private $dbh;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	}
	
	public function createMetaEntityFromDatabase(Database $database, string $name) {
		$metaEntity = $this->createMetaEntity($database->getName(), $name);
		CastUtils::assertTrue($metaEntity instanceof MetaEntityAdapter);
		$metaEntity->setDatabase($database);
		
		if ($metaEntity instanceof Table) {
			$this->applyIndexesForTable($database->getName(), $metaEntity);
		}
		
		return $metaEntity;
	}
	
	/**
	 * @param string $name
	 * @return \n2n\persistence\meta\structure\MetaEntity
	 */
	public function createMetaEntity(string $dbName, string $name) {
		
		$metaEntity = null;
		
		$sql = 'SELECT * FROM information_schema.' . $this->dbh->quoteField('TABLES') . ' WHERE TABLE_CATALOG = :TABLE_CATALOG AND TABLE_NAME = :TABLE_NAME;';
		$statement = $this->dbh->prepare($sql);
		$statement->execute(array(':TABLE_CATALOG' => $dbName, ':TABLE_NAME' => $name));
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		
		$tableType = $result['TABLE_TYPE'];
		switch ($tableType) {
			case self::TABLE_TYPE_BASE_TABLE:
				$table = new MssqlTable($name);
				$columns = $this->getColumnsForTable($table);
				$table->setColumns($dbName, $columns);
				$table->setAttrs($result);
				$metaEntity = $table;
				break;
			case self::TABLE_TYPE_VIEW:
				$viewSql = 'SELECT * FROM information_schema.' . $this->dbh->quoteField('VIEWS') . ' WHERE TABLE_CATALOG = :TABLE_CATALOG AND TABLE_NAME = :TABLE_NAME;';
				$viewStatement = $this->dbh->prepare($viewSql);
				$viewStatement->execute(array(':TABLE_CATALOG' => $dbName, ':TABLE_NAME' => $name));
				$viewResult = $viewStatement->fetch(Pdo::FETCH_ASSOC);
					
				$view = new CommonView($name, $this->parseViewCreateStatement($viewResult['VIEW_DEFINITION']));
				$view->setAttrs($viewResult);
				$metaEntity = $view;
				break;
		}
		
		return $metaEntity;
	}
	
	private function getColumnsForTable(string $dbName, MssqlTable $table) {
		$columns = array();
		//show tables not sufficient to get the character set
		$stmt = $this->dbh->prepare('SELECT * FROM INFORMATION_SCHEMA.[COLUMNS] WHERE TABLE_CATALOG = :TABLE_CATALOG AND TABLE_NAME = :TABLE_NAME');
		$stmt->execute(array(':TABLE_CATALOG' => $dbName, ':TABLE_NAME' => $table->getName()));

		while (null != ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
			$column = null;
			switch ($row['DATA_TYPE']) {
				case 'int':
					$column = new MssqlIntegerColumn($row['COLUMN_NAME'], Size::INTEGER);
					break;
				
				case 'tinyint':
					$column = new MssqlIntegerColumn($row['COLUMN_NAME'], Size::SHORT);
					break;
				
				case 'smallint':
					$column = new MssqlIntegerColumn($row['COLUMN_NAME'], Size::MEDIUM);
					break;
				
				case 'bigint':
					$column = new MssqlIntegerColumn($row['COLUMN_NAME'], Size::LONG);
					break;
					
				case 'nchar':
				case 'nvarchar':
				case 'ntext':
					$characterMaximumLengthDoubleVal = doubleval($row['CHARACTER_MAXIMUM_LENGTH']);
					if ($characterMaximumLengthDoubleVal > 0) {
						$length = $characterMaximumLengthDoubleVal;
					} else {
						$length = MssqlSize::MAX_STRING_STORAGE_LENGTH;
					}
					$column = new CommonStringColumn($row['COLUMN_NAME'], $length, $row['CHARACTER_SET_NAME']);
					break;
					
				case 'char':
				case 'varchar':
				case 'text':
					$characterMaximumLengthDoubleVal = doubleval($row['CHARACTER_MAXIMUM_LENGTH']);
					if ($characterMaximumLengthDoubleVal > 0) {
						$size = $characterMaximumLengthDoubleVal * 8;
					} else {
						$size = MssqlSize::MAX_TEXT_STORAGE_SIZE;
					}
					$column = new CommonTextColumn($row['COLUMN_NAME'], $size, $row['CHARACTER_SET_NAME']);
					break;
					
				case 'binary':
				case 'varbinary':
					$column = new CommonBinaryColumn($row['COLUMN_NAME'], $row['CHARACTER_MAXIMUM_LENGTH'] * 8);
					break;
					
				case 'decimal':
				case 'numeric':
					$numIntegerDigits = intval($row['NUMERIC_PRECISION']) - intval($row['NUMERIC_SCALE']);
					$column = new CommonFixedPointColumn($row['COLUMN_NAME'], $numIntegerDigits, $row['NUMERIC_SCALE']);
					break;
				case 'date':
					$column = new MssqlDateTimeColumn($row['COLUMN_NAME'], true, false, $row['DATETIME_PRECISION']);
					break;
				case 'datetime2':
					$column = new MssqlDateTimeColumn($row['COLUMN_NAME'], true, true, $row['DATETIME_PRECISION']);
					break;
				case 'datetimeoffset':
					$column = new MssqlDateTimeColumn($row['COLUMN_NAME'], true, true, $row['DATETIME_PRECISION'] , true);
					break;
				case 'time':
					$column = new MssqlDateTimeColumn($row['COLUMN_NAME'], false, true, $row['DATETIME_PRECISION']);
					break;
				case 'real':
					$column = new CommonFloatingPointColumn($row['COLUMN_NAME'], Size::DOUBLE);
					break;
				case 'float':
					$column = new CommonFloatingPointColumn($row['COLUMN_NAME'], Size::FLOAT);
					break;
				default:
					$column = new MssqlDefaultColumn($row['COLUMN_NAME']);
					$column->setTable($table);
					break;
			}
			$column->setNullAllowed($row['IS_NULLABLE'] == 'YES');
			$column->setDefaultValue($row['COLUMN_DEFAULT']);
			$column->setAttrs($row);
			
			//set the generated Identifier tag
			$giSql = 'SELECT is_identity, is_computed FROM sys.columns c WHERE c.object_id = OBJECT_ID(:table_name) AND c.name = :column_name';
			$giStatement = $this->dbh->prepare($giSql);
			$giStatement->execute(array(':table_name' => $table->getName(), ':column_name' => $row['COLUMN_NAME']));
			$giResult = $giStatement->fetch(Pdo::FETCH_ASSOC);
			if ((bool) $giResult['is_identity'] || (bool) $giResult['is_computed']) {
				$column->setValueGenerated(true);
				if ($giResult['is_identity']) {
					$this->dbh->getMetaData()->getDialect()->applyIdentifierGeneratorToColumn($this->dbh, $column);
				} else {
					$computedSql = 'SELECT definition FROM sys.computed_columns c WHERE c.object_id = OBJECT_ID(:table_name) AND c.name = :column_name';
					$computedStatement = $this->dbh->prepare($computedSql);
					$computedStatement->execute(array(':table_name' => $table->getName(), ':column_name' => $row['COLUMN_NAME']));
					$computedResult = $computedStatement->fetch(Pdo::FETCH_ASSOC);
					$column->setAttrs(array_merge($column->getAttrs(), array(MssqlColumnStatementStringBuilder::ATTR_NAME_COMPUTED_VALUE => $computedResult['definition'])));
				}
			}
			$column->registerChangeListener($table);
			$columns[$row['COLUMN_NAME']] = $column;
		}
		return $columns;
	}
	
	public function applyIndexesForTable(string $dbName, MssqlTable $table) {
		$indexes = array();
		$columns = $table->getColumns();
		
		//First 'normal' indexes
		//Do not regard the HEAP indexes
		$sql = 'SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(:table_name) AND '
				. $this->dbh->quoteField('type_desc') . '!= :heap';
		$statement = $this->dbh->prepare($sql);
		$statement->execute(array(':table_name' => $table->getName(), ':heap' => self::INDEX_TYPE_DESC_HEAP));
		$results = $statement->fetchAll(Pdo::FETCH_ASSOC);
		foreach ($results as $result) {
			
			if (array_key_exists($result['name'], $indexes)) continue;
			$indexColumns = array();
			$columnsSql = 'SELECT c.name FROM sys.indexes i 
									JOIN sys.index_columns ic ON i.object_id = ic.object_id 
										AND i.index_id = ic.index_id 
									JOIN sys.columns c ON c.object_id = ic.object_id 
										AND c.column_id = ic.column_id 
									WHERE i.object_id = OBJECT_ID(:table_name) 
										AND i.name = :index_name';
			$columnsStatement = $this->dbh->prepare($columnsSql);
			$columnsStatement->execute(array(':table_name' => $table->getName(), ':index_name' => $result['name']));
			$columnsResults = $columnsStatement->fetchAll(Pdo::FETCH_ASSOC);
			foreach ($columnsResults as $columnResult) {
				$indexColumns[$columnResult['name']] = $columns[$columnResult['name']];
			}
			
			$type = $this->getIndexTypeFor($result['is_primary_key'], $result['is_unique']);
			$name = $result['name'];
			if ($type == IndexType::PRIMARY) {
				$name = $table->generatePrimaryKeyName();
			}
			
			$index = new CommonIndex($table, $name, $type, $indexColumns);
			$index->setAttrs($result);
			$indexes[$name] = $index;
		}
		//then the fulltext indexes
//		$sql = 'SELECT * FROM sys.fulltext_indexes 
//							WHERE object_id = OBJECT_ID(:table_name)';
//		$statement = $this->dbh->prepare($sql);
//		$statement->execute(array(':table_name' => $this->getName()));
//		if ($statement->fetch(Pdo::FETCH_ASSOC)){
//			$name = MssqlIndex::FULLTEXT_INDEX_NAME . $this->getName();
//			$this->indexes[$name] = new MssqlIndex($this, $this->dbh, $name, $result, false, false, true);
//		}

		$table->setIndexes($indexes);
	}
	
	private function getIndexTypeFor($isPrimary, $isUnique) {
		if ($isPrimary) {
			return IndexType::PRIMARY;
		} elseif ($isUnique) {
			return IndexType::UNIQUE;
		} else {
			return IndexType::INDEX;
		}
	}
	
	/**
	* Parse the given create statement and extract the query
	* @param string $createStatement
	*/
	private function parseViewCreateStatement($createStatement) {
		$matches = preg_split('/AS/i', $createStatement, 2);
		if (isset($matches[1])) {
			return trim($matches[1]);
		}
		return $createStatement;
	}
}

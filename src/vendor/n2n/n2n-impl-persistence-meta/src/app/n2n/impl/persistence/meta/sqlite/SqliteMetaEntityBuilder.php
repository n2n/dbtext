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

use n2n\persistence\meta\structure\common\CommonIndex;

use n2n\persistence\meta\structure\common\CommonView;

use n2n\persistence\meta\structure\IndexType;

use n2n\persistence\Pdo;
use n2n\persistence\meta\Database;
use n2n\util\type\CastUtils;
use n2n\persistence\meta\structure\common\MetaEntityAdapter;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\common\ForeignIndex;

class SqliteMetaEntityBuilder {
	
	const TYPE_TABLE = 'table';
	const TYPE_VIEW = 'view';
	const TYPE_INDEX = 'index';
	
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
	 * @return MetaEntity
	 */
	public function createMetaEntity(string $dbName, string $name) {
		
		$metaEntity = null;
		
		$sql = 'SELECT * FROM ' . $this->dbh->quoteField($dbName) . '.sqlite_master WHERE type in (:type_table, :type_view) AND name = :name';
		$statement = $this->dbh->prepare($sql);
		$statement->execute([':type_table' => self::TYPE_TABLE, 
				':type_view' => self::TYPE_VIEW, ':name' => $name]);
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		
		$tableType = $result['type'];
		
		switch ($tableType) {
			case self::TYPE_TABLE:
				$table = new SqliteTable($name);
				$table->setColumns($this->getColumnsForTable($dbName, $table));
				$metaEntity = $table;
				break;
			case self::TYPE_VIEW:
				$view = new CommonView($name, $this->parseViewCreateStatement($result['sql']));
				$metaEntity = $view;
				break;
		}
		
		$metaEntity->setAttrs($result);
		return $metaEntity;
	}
	
	private function getColumnsForTable(string $dbName, SqliteTable $table) {
		$columns = array();
		$sql = 'PRAGMA ' . $this->dbh->quoteField($dbName) 
				. '.table_info(' . $this->dbh->quoteField($table->getName()) . ')';
		$statement = $this->dbh->prepare($sql);
		$statement->execute();
		$numPrimaryKeyColumns = 0;
		$generatedIdentifierColumnName = null;
		while (null != ($row = $statement->fetch(PDO::FETCH_ASSOC))) {
			if ($row['pk']) {
				$numPrimaryKeyColumns++;
			} 
			$column = null;
			if (preg_match('/int/i', $row['type'])) {
				$column = new SqliteIntegerColumn($row['name']);
				if ($row['type'] == 'INTEGER' && $row['pk']) {
					$generatedIdentifierColumnName = $row['name'];
				}
			} elseif(preg_match('/char|clob|text/i', $row['type'])) {
				$column = new SqliteStringColumn($row['name']);
			} elseif(empty($row['type']) || preg_match('/blob/i', $row['type'])) {
				$column = new SqliteBinaryColumn($row['name']);
			} elseif (preg_match('/REAL|FLOA|DOUB/i', $row['type'])) {
				$column = new SqliteFloatingPointColumn($row['name']);
			} elseif($row['type'] == SqliteDateTimeColumn::COLUMN_TYPE_NAME) {
				$column = new SqliteDateTimeColumn($row['name']);
			} else {
				$column = new SqliteFixedPointColumn($row['name']);
			}
			$column->setNullAllowed(!$row['notnull']);
			$column->setDefaultValue($row['dflt_value']);
			$column->setAttrs($row);
			$columns[$row['name']] = $column;
		}

		if (($numPrimaryKeyColumns == 1) &&
				(!(is_null($generatedIdentifierColumnName)))) {
			$this->dbh->getMetaData()->getDialect()->applyIdentifierGeneratorToColumn($this->dbh, $columns[$generatedIdentifierColumnName]);
		}
		
		return $columns;
	}
	
	public function applyIndexesForTable(string $dbName, SqliteTable $table) {
		$primaryColumns = false;
		$indexes = array();
		$columns = $table->getColumns();
		$sql = 'PRAGMA ' . $this->dbh->quoteField($dbName) 
					. '.index_list(' . $this->dbh->quoteField($table->getName()) . ')';
		$statement = $this->dbh->prepare($sql);
		$statement->execute();
		while (null != ($result = $statement->fetch(Pdo::FETCH_ASSOC))) {
			$type = null;
			$name = $result['name'];
			if (!($result['unique'])) {
				$type = IndexType::INDEX;
			} else {
				$indexSql = 'SELECT * FROM ' . $this->dbh->quoteField($dbName) . '.sqlite_master WHERE name = :name';
				$indexStatement = $this->dbh->prepare($indexSql);
				$indexStatement->execute(array(':name' => $result['name']));
				$indexResult = $indexStatement->fetch(Pdo::FETCH_ASSOC);
				if (is_null($indexResult['sql'])) {
					// there are different ways for sqlite to store the primary keys its not always in the indexList
					// so we always get the Primary Key at the end
					continue;
				} else {
					$type = IndexType::UNIQUE;
				}
			}
	
			$indexColumns = array();
			$columnsSql = 'PRAGMA ' . $this->dbh->quoteField($dbName) 
					. '.index_info(' . $this->dbh->quoteField($result['name']) . ')';
			$columnsStatement = $this->dbh->prepare($columnsSql);
			$columnsStatement->execute();
			$columnsResults = $columnsStatement->fetchAll(Pdo::FETCH_ASSOC);
			foreach ($columnsResults as $columnResult) {
				$indexColumns[] = $table->getColumnByName($columnResult['name']);
			}
			$index = new CommonIndex($table, $name, $type, $indexColumns);
			$index->setAttrs($result);
			$indexes[] = $index;
		}
		
		//get the primary key information
		$sql = 'PRAGMA ' . $this->dbh->quoteField($dbName)
				. '.table_info(' . $this->dbh->quoteField($table->getName()) . ')';
		$statement = $this->dbh->prepare($sql);
		$statement->execute();
		$indexColumns = array();
		while (null != ($row = $statement->fetch(PDO::FETCH_ASSOC))) {
			if ($row['pk']) {
				$indexColumns[] = $table->getColumnByName($row['name']);
			}
		}
		
		if (count($indexColumns)) {
			$indexes[] = new CommonIndex($table, $table->generatePrimaryKeyName(), IndexType::PRIMARY, $indexColumns);
		}
		
		//get the foreign key information
		$sql = 'PRAGMA ' . $this->dbh->quoteField($dbName)
				. '.foreign_key_list(' . $this->dbh->quoteField($table->getName()) . ')';
		$statement = $this->dbh->prepare($sql);
		$statement->execute();
		while (null != ($row = $statement->fetch(PDO::FETCH_ASSOC))) {
			$indexes[] = ForeignIndex::createFromColumnNames($table, $row['from'] . '_' . $row['table'] . $row['to'], 
					[$row['from']], $table->getDatabase()->getMetaEntityByName($row['table']), [$row['to']]);
		}
		
		$table->setIndexes($indexes);
	}
	
	/**
	 * Parse the given create statement and extract the query
	 * @param string $createStatement
	 */
	private function parseViewCreateStatement($createStatement) {
		$matches = preg_split('/AS/i', $createStatement);
		if (isset($matches[1])) {
			return trim($matches[1]);		
		}
		return $createStatement;
	}
}

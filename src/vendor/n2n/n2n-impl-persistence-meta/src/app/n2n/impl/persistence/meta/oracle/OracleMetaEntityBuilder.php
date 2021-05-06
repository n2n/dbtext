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

use n2n\persistence\meta\structure\Size;

use n2n\persistence\meta\structure\Table;

use n2n\persistence\meta\structure\common\CommonIndex;

use n2n\persistence\meta\structure\common\CommonView;

use n2n\persistence\meta\structure\common\CommonFloatingPointColumn;

use n2n\persistence\meta\structure\common\CommonFixedPointColumn;

use n2n\persistence\meta\structure\common\CommonBinaryColumn;

use n2n\persistence\meta\structure\common\CommonTextColumn;

use n2n\persistence\meta\structure\common\CommonStringColumn;

use n2n\persistence\meta\structure\IndexType;

use n2n\persistence\Pdo;
use n2n\util\type\CastUtils;
use n2n\persistence\meta\Database;
use n2n\persistence\meta\structure\common\TableAdapter;

class OracleMetaEntityBuilder {
	
	const INDEX_UNIQUE = 'UNIQUE';
	const CONSTRAINT_TYPE_PRIMARY = 'P';
	const CONSTRAINT_TYPE_UNIQUE = 'U';
	
	/**
	 * @var Pdo
	 */
	private $dbh;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	}
	
	public function createView(string $name) {
		$view = null;
		$statement = $this->dbh->prepare('SELECT * FROM user_views WHERE view_name = :view_name');
		$statement->execute(array(':view_name' => $name));
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		if ($result) {
			$view = new CommonView($name, $result['TEXT']);
			$view->setAttrs($result);
		}
		return $view;
	}
	
	public function createTableFromDatabase(Database $database, string $name) {
		$metaEntity = $this->createTable($database->getName(), $name);
		CastUtils::assertTrue($metaEntity instanceof TableAdapter);
		$metaEntity->setDatabase($database);
		$this->applyIndexesForTable($database->getName(), $metaEntity);
		
		return $metaEntity;
	}
	
	/**
	 * @param string $name
	 * @return \n2n\persistence\meta\structure\MetaEntity
	 */
	public function createTable(string $name) {
		$table = null;
		//First check for tables
		$statement = $this->dbh->prepare('SELECT * FROM user_tables WHERE tablespace_name = :users AND table_name = :table_name');
		$statement->execute(array(':users' => 'USERS', ':table_name' => $name));
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		
		if ($result) {
			$table = new OracleTable($name);
			$table->setColumns($this->getColumnsForTable($table));
			$table->setAttrs($result);
		}
		return $table;
	}
	
	private function getColumnsForTable(OracleTable $table) {
		$columns = array();
		//show tables not sufficient to get the character set
		$stmt = $this->dbh->prepare('SELECT * FROM user_tab_columns WHERE TABLE_NAME = :TABLE_NAME');
		$stmt->execute(array(':TABLE_NAME' => $table->getName()));
			
		while (null != ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
			$column = null;
			switch ($row['DATA_TYPE']) {
				case 'NUMBER':
					if ($row['DATA_SCALE'] == '0' && ! $row['DATA_PRECISION']) {
						$column = new OracleIntegerColumn($row['COLUMN_NAME']);
					} else {
						// @see http://docs.oracle.com/cd/B28359_01/server.111/b28318/datatype.htm#i16209
						if ($row['DATA_SCALE'] || $row['DATA_PRECISION']) {
							if ($row['DATA_SCALE']) {
								$numDecimalDigits = intval($row['DATA_SCALE']);
								if (0 !== ($precision = intval($row['DATA_PRECISION']))) {
									if ($precision > OracleSize::MAX_FULL_DIGIT_PRECISION) {
										$precision = null;
									}
								} else {
									$precision = OracleSize::MAX_FULL_DIGIT_PRECISION;
								}
								if (!is_null($precision)) {
									$column = new CommonFixedPointColumn($row['COLUMN_NAME'], $precision-$numDecimalDigits, $numDecimalDigits);
								} else {
									$column = new OracleFloatingPointColumn($row['COLUMN_NAME']);
								}
							} else {
								$precision = intval($row['DATA_PRECISION']);
								$numDecimalDigits = OracleSize::MAX_FULL_DIGIT_PRECISION - $precision;
								$column = new CommonFixedPointColumn($row['COLUMN_NAME'], $precision - $numDecimalDigits, $numDecimalDigits);
							}
						} else {
							$column = new OracleFloatingPointColumn($row['COLUMN_NAME']);
						}
					}
					break;
				case 'NVARCHAR2':
				case 'NVARCHAR':
				case 'NCHAR':
				case 'VARCHAR2':
				case 'VARCHAR':
				case 'CHAR':
					if ($row['CHAR_USED'] == 'C') {
						$column = new CommonStringColumn($row['COLUMN_NAME'], $row['CHAR_LENGTH'], $row['CHARACTER_SET_NAME']);
					} else {
						$column = new CommonTextColumn($row['COLUMN_NAME'], $row['DATA_LENGTH'] * 8, $row['CHARACTER_SET_NAME']);
					}
					break;
				case 'CLOB':
				case 'NCLOB':
					$column = new CommonTextColumn($row['COLUMN_NAME'], $row['DATA_LENGTH'] * 8, $row['CHARACTER_SET_NAME']);
					break;
				case 'RAW':
					$column = new CommonBinaryColumn($row['COLUMN_NAME'], $row['DATA_LENGTH'] * 8);
					break;
				case 'LONG RAW':
					$column = new CommonBinaryColumn($row['COLUMN_NAME'], OracleSize::SIZE_LONG);
					break;
					
				case 'BLOB':
					$column = new CommonBinaryColumn($row['COLUMN_NAME'], $row['DATA_LENGTH'] * 8);
					break;
				case 'BINARY_FLOAT':
					$column = new CommonFloatingPointColumn($row['COLUMN_NAME'], Size::FLOAT);
					break;
				case 'BINARY_DOUBLE':
					$column = new CommonFloatingPointColumn($row['COLUMN_NAME'], Size::DOUBLE);
					break;
				case 'DATE':
					$column = new OracleDateTimeColumn($row['COLUMN_NAME'], true, false);
					break;
				default:
					if (preg_match('/TIMESTAMP/i', $row['DATA_TYPE'])) {
						if (preg_match('/TIME ZONE/i', $row['DATA_TYPE'])) {
							if (preg_match('/LOCAL/i', $row['DATA_TYPE'])) {
								$column = new OracleDateTimeColumn($row['COLUMN_NAME'], true, true, $row['DATA_SCALE'], false, true);
							} else {
								$column = new OracleDateTimeColumn($row['COLUMN_NAME'], true, true, $row['DATA_SCALE'], true);
							}
						} else {
							$column = new OracleDateTimeColumn($row['COLUMN_NAME'], true, true, $row['DATA_SCALE']);
						}
					} else {
						$column = new OracleDefaultColumn($row['COLUMN_NAME']);
					}
			}
			$column->setNullAllowed($row['NULLABLE'] == 'Y');
			$column->setDefaultValue($row['DATA_DEFAULT']);
			$column->setAttrs($row);
			$columns[$row['COLUMN_NAME']] = $column;
		}
		return $columns;
	}
	
	public function applyIndexesForTable(Table $table) {
		CastUtils::assertTrue($table instanceof OracleTable);
		$indexes = array();
		$columns = $table->getColumns();
		$statement = $this->dbh->prepare('SELECT * FROM user_indexes WHERE INDEX_TYPE = :NORMAL AND GENERATED != :Y AND TABLE_NAME = :TABLE_NAME');
		$statement->execute(array(':NORMAL' => 'NORMAL', ':Y' => 'Y' ,':TABLE_NAME' => $table->getName()));
		$results = $statement->fetchAll(Pdo::FETCH_ASSOC);
		foreach ($results as $result) {
			$indexColumns = array();
			$columnsStatement = $this->dbh->prepare('SELECT * FROM user_ind_columns WHERE INDEX_NAME = :INDEX_NAME');
			$columnsStatement->execute(array(':INDEX_NAME' => $result['INDEX_NAME']));
			$columnsResults = $columnsStatement->fetchAll(Pdo::FETCH_ASSOC);
			foreach ($columnsResults as $columnResult) {
				$indexColumns[$columnResult['COLUMN_NAME']] = $columns[$columnResult['COLUMN_NAME']];
			}
			
			$name = $result['INDEX_NAME'];
			$type = IndexType::INDEX;
			if ($result['UNIQUENESS'] == self::INDEX_UNIQUE) {
				//check the constraint
				$constraintStatement = $this->dbh->prepare('SELECT * FROM user_constraints WHERE CONSTRAINT_NAME = :CONSTRAINT_NAME');
				$constraintStatement->execute(array(':CONSTRAINT_NAME' => $result['INDEX_NAME']));
				if (null != ($constraintResult = $constraintStatement->fetch(Pdo::FETCH_ASSOC))) {
					if ($constraintResult['CONSTRAINT_TYPE'] == self::CONSTRAINT_TYPE_PRIMARY) {
						$type = IndexType::PRIMARY;
						$name = $table->generatePrimaryKeyName();
					} else {
						$type = IndexType::UNIQUE;
					}
				}
			}
			
			
			$index = new CommonIndex($table, $name, $type, $indexColumns, $result);
			$index->setAttrs($result);
			$indexes[$name] = $index;
		}
		
		//It is possible that the PK is not in the indexes (Another Index has the same constraints)
		$statement = $this->dbh->prepare('SELECT * FROM user_constraints WHERE CONSTRAINT_TYPE = :P AND TABLE_NAME = :TABLE_NAME');
		$statement->execute(array(':P' => self::CONSTRAINT_TYPE_PRIMARY ,':TABLE_NAME' => $table->getName()));
		if (null != ($result = $statement->fetch(Pdo::FETCH_ASSOC))) {
			if (!array_key_exists($result['CONSTRAINT_NAME'], $indexes)) {
				$indexColumns = array();
				$columnsStatement = $this->dbh->prepare('SELECT * FROM user_cons_columns WHERE CONSTRAINT_NAME = :CONSTRAINT_NAME');
				$columnsStatement->execute(array(':CONSTRAINT_NAME' => $result['CONSTRAINT_NAME']));
				$columnsResults = $columnsStatement->fetchAll(Pdo::FETCH_ASSOC);
				foreach ($columnsResults as $columnResult) {
					$indexColumns[$columnResult['COLUMN_NAME']] = $columns[$columnResult['COLUMN_NAME']];
				}
				$index = new CommonIndex($table, $table->generatePrimaryKeyName(), IndexType::PRIMARY, $indexColumns, $result);
				$index->setAttrs($result);
				$indexes[$table->generatePrimaryKeyName()] = $index;
			}
		}
		
		$table->setIndexes($indexes);
	}
}

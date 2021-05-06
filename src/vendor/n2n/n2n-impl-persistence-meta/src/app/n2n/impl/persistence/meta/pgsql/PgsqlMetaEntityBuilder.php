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
namespace n2n\impl\persistence\meta\pgsql;

use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\common\CommonView;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\meta\structure\common\CommonBinaryColumn;
use n2n\persistence\meta\structure\common\CommonFixedPointColumn;
use n2n\persistence\meta\structure\common\CommonFloatingPointColumn;
use n2n\persistence\meta\structure\common\CommonStringColumn;
use n2n\persistence\meta\structure\common\CommonTextColumn;
use n2n\persistence\meta\structure\common\CommonEnumColumn;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\Database;
use n2n\util\type\CastUtils;
use n2n\persistence\meta\structure\common\MetaEntityAdapter;

class PgsqlMetaEntityBuilder {
	const TABLE_TYPE_BASE_TABLE = 'BASE TABLE';
	const TABLE_TYPE_VIEW = 'VIEW';

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

	public function createMetaEntity(string $dbName, string $name) {
		$stmt = $this->dbh->prepare('SELECT * FROM information_schema.tables WHERE table_catalog = ? AND table_name = ?');
		$stmt->execute(array($dbName, $name));
		$result = $stmt->fetch(Pdo::FETCH_ASSOC);

		$metaEntity = null;
		switch ($result['table_type']) {
			case self::TABLE_TYPE_VIEW:

				$stmt = $this->dbh->prepare('select view_definition from INFORMATION_SCHEMA.VIEWS where table_catalog = ? AND table_name = ?');
				$stmt->execute(array($dbName, $name));
				$result = $stmt->fetch(Pdo::FETCH_ASSOC);

				$metaEntity = new CommonView($name, $result['view_definition']);
				$metaEntity->setAttrs($result);

				break;
			case self::TABLE_TYPE_BASE_TABLE:
				$metaEntity = new PgsqlTable($name);
				$metaEntity->setColumns($this->getColumnsForTablename($dbName, $name));
				break;
		}

		return $metaEntity;
	}

	private function toMetaIndexType($rawIndexType) {
		if ($rawIndexType === 'PRIMARY KEY') return IndexType::PRIMARY;
		if ($rawIndexType === 'INDEX') return IndexType::INDEX;

		return $rawIndexType;
	}

	private function getColumnsForTablename(string $dbName, string $name) {
		$stmt = $this->dbh->prepare('
			SELECT * FROM INFORMATION_SCHEMA.columns AS isc
			LEFT JOIN pg_collation AS pc ON isc.collation_name = pc.collname
			WHERE isc.table_catalog = ? AND isc.table_name = ?
		');

		$stmt->execute(array($dbName, $name));
		$result = $stmt->fetchAll(Pdo::FETCH_ASSOC);
		$columns = array();

		foreach ($result as $row) {
			switch ($row['data_type']) {
				case 'date':
					$column = new PgsqlDateTimeColumn($row['column_name'], true, false);
					//$this->applyCommonColumnAttributes($column, $row);
					$columns[$column->getName()] = $column;
					break;
				case 'time with time zone':
				case 'time without time zone':
					$column = new PgsqlDateTimeColumn($row['column_name'], false, true);
					break;
				case 'timestamp with time zone':
				case 'timestamp without time zone':
					$column = new PgsqlDateTimeColumn($row['column_name'], true, true);
					break;
				case 'bytea':
					$column = new CommonBinaryColumn($row['column_name'],
					(isset($row['character_maximum_length']) ? $row['character_maximum_length'] : (pow(2, 31)-1)));
					break;
				case 'money':
				case 'numeric':
					$column = new CommonFixedPointColumn($row['column_name'], ($row['numeric_precision'] - $row['numeric_scale']), $row['numeric_scale']);
					break;
				case 'double precision':
				case 'real':
					$column = new CommonFloatingPointColumn($row['column_name'], $row['numeric_precision']);
					break;
				case 'bigint':
				case 'integer':
				case 'smallint':
					$stmtInteger = $this->dbh->prepare('
						SELECT * FROM INFORMATION_SCHEMA.check_constraints
						WHERE constraint_catalog = ?
						AND (check_clause LIKE ? AND check_clause NOT LIKE ? AND check_clause NOT LIKE ?)
					');
					$stmtInteger->execute(array($dbName, '%' . $row['column_name'] . ' > 0%', '%' . $row['column_name'] . ' > 0% %OR%', '%OR% %' . $row['column_name'] . ' > 0%'));
					$integerResult = $stmtInteger->fetchAll(Pdo::FETCH_ASSOC);

					$unsigned = true;
					if (sizeof($integerResult)) $unsigned = false;

					$column = new PgsqlIntegerColumn($row['column_name'], $row['numeric_precision'], $unsigned, $row);
					break;
				case 'bit varying':
				case 'char':
				case 'character':
				case 'character varying':
					$column = new CommonStringColumn($row['column_name'], $row['character_maximum_length'], $row['collctype']);
					break;
				case 'text':
					$column = new CommonTextColumn($row['column_name'], $row['character_octet_length'], $row['collctype']);
					break;
				case 'USER-DEFINED':
					$stmt = $this->dbh->prepare('SELECT e.enumlabel AS enum_value
							FROM pg_type t
								JOIN pg_enum e ON t.oid = e.enumtypid
								JOIN pg_catalog.pg_namespace n ON n.oid = t.typnamespace
							WHERE t.typname = ?');
					$stmt->execute(array($row['udt_name']));
					$result = $stmt->fetchAll(Pdo::FETCH_ASSOC);

					$values = array();
					foreach ($result as $resultRow) {
						$values[] = $resultRow['enum_value'];
					}
						
					$column = new CommonEnumColumn($row['column_name'], $values);
					break;
				default:
					$column = new PgsqlDefaultColumn($row['column_name']);
					break;
			}
			
			$column->setNullAllowed($row['is_nullable'] == 'YES' ? true : false);
			$column->setDefaultValue($this->parseDefaultValue($row['column_default']));
			if (0 === strpos($row['column_default'], 'nextval')) {
				$column->setValueGenerated(true);
			}
			$column->setAttrs($row);
				
			$columns[$row['column_name']] = $column;
		}
		return $columns;
	}
	
	private function parseDefaultValue(string $default = null) {
		$matches = [];
		if (null === $default || !preg_match('/^\'(.*)\':.*/', $default, $matches)) return $default;
		
		return $matches[1];
	}

	public function applyIndexesForTable(string $dbName, Table $table) {
		$stmtPrimary = $this->dbh->prepare('
			SELECT istc.constraint_name AS indname, istc.constraint_type AS indtype,
				ARRAY (
					SELECT pg_get_indexdef(idx.indexrelid, k + 1, true)
					FROM generate_subscripts(idx.indkey, 1) as k
					ORDER BY k
				) AS indcolumns
			FROM INFORMATION_SCHEMA.table_constraints AS istc
				JOIN pg_index AS idx ON istc.constraint_name = TEXT(idx.indexrelid::regclass)
			WHERE istc.constraint_type != ? AND istc.table_name = ?;');
		$stmtPrimary->execute(array('CHECK', $table->getName()));
		$stmtPrimaryArray = $stmtPrimary->fetchAll(Pdo::FETCH_ASSOC);

		$sql = '
			SELECT i.relname AS indname, \'index\' AS indtype,
				ARRAY(
					SELECT pg_get_indexdef(idx.indexrelid, k + 1, true)
					FROM generate_subscripts(idx.indkey, 1) as k
					ORDER BY k
				) AS indcolumns
			FROM pg_index AS idx
				JOIN pg_class AS i ON i.oid = idx.indexrelid
				JOIN pg_am AS am ON i.relam = am.oid
			WHERE TEXT(idx.indrelid::regclass) = ?
				 AND idx.indisprimary != ? AND idx.indisunique != ? ';
		$executeArray = array($table->getName(), 't', 't');

		$stmt = $this->dbh->prepare($sql);
		$stmt->execute($executeArray);
		$stmtIndex = $stmt->fetchAll(Pdo::FETCH_ASSOC);

		$sql = '
			SELECT i.relname AS indname, \'unique\' AS indtype,
				ARRAY(
					SELECT pg_get_indexdef(idx.indexrelid, k + 1, true)
					FROM generate_subscripts(idx.indkey, 1) as k
					ORDER BY k
				) AS indcolumns
			FROM pg_index AS idx
				JOIN pg_class AS i ON i.oid = idx.indexrelid
				JOIN pg_am AS am ON i.relam = am.oid
			WHERE TEXT(idx.indrelid::regclass) = ?
				 AND idx.indisprimary != ? AND idx.indisunique = ? ';
		$executeArray = array($table->getName(), 't', 't');

		$stmt = $this->dbh->prepare($sql);
		$stmt->execute($executeArray);
		$stmtUnique = $stmt->fetchAll(Pdo::FETCH_ASSOC);

		foreach (array_merge($stmtPrimaryArray, $stmtIndex, $stmtUnique) as $index) {
			$table->createIndex($this->toMetaIndexType($index['indtype']),
				explode(',', substr($index['indcolumns'], 1, -1)), $index['indname']);
		}

		//Foreign Keys
		$sql = 'SELECT c.conname AS name,
						(string_to_array((string_to_array(pg_get_constraintdef(c.oid), \'(\'))[2],\')\'))[1] AS "column_names",
    					c.confrelid::regclass::text AS "foreign_table_name",
						(string_to_array((string_to_array(pg_get_constraintdef(c.oid),\'(\'))[3],\')\'))[1] AS "foreign_column_names"
				FROM pg_constraint AS c
				JOIN pg_namespace AS n ON n.oid = c.connamespace
				WHERE c.contype = ?
    				AND n.nspname = ?
					AND TEXT(c.conrelid::regclass) = ?';
		$executeArray = array('f', 'public', $table->getName());

		$stmt = $this->dbh->prepare($sql);
		$stmt->execute($executeArray);
		$stmtForeign = $stmt->fetchAll(Pdo::FETCH_ASSOC);

		foreach ($stmtForeign as $index) {
			$table->createIndex(IndexType::FOREIGN,
					explode(', ', $index['column_names']), $index['name'], 
					$table->getDatabase()->getMetaEntityByName($index['foreign_table_name']),
					explode(', ', $index['foreign_column_names']));
		}
	}
}
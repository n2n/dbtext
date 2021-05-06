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

use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\Backuper;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\common\MetaManagerAdapter;
use n2n\persistence\meta\structure\common\DatabaseAdapter;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\impl\persistence\meta\sqlite\management\SqliteAlterMetaEntityRequest;
use n2n\impl\persistence\meta\sqlite\management\SqliteCreateMetaEntityRequest;
use n2n\impl\persistence\meta\sqlite\management\SqliteDropMetaEntityRequest;
use n2n\impl\persistence\meta\sqlite\management\SqliteRenameMetaEntityRequest;

class SqliteMetaManager extends MetaManagerAdapter {
	private $metaEntityBuilder;
	
	public function __construct(Pdo $dbh) {
		parent::__construct($dbh);
		$this->metaEntityBuilder = new SqliteMetaEntityBuilder($dbh);
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Database::createBackuper()
	 * @return Backuper
	 */
	public function createBackuper(array $metaEnities = null): Backuper {
		return new SqliteBackuper($this->dbh, $this->createDatabase(), $metaEnities);
	}
	
	protected function createAlterMetaEntityRequest(MetaEntity $metaEntity) {
		return new SqliteAlterMetaEntityRequest($metaEntity);
	}
	
	protected function createCreateMetaEntityRequest(MetaEntity $metaEntity) {
		return new SqliteCreateMetaEntityRequest($metaEntity);
	}
	
	protected function createDropMetaEntityRequest(MetaEntity $metaEntity) {
		return new SqliteDropMetaEntityRequest($metaEntity);
	}
	
	protected function createRenameMetaEntityRequest(MetaEntity $metaEntity, string $oldName, string $newName) {
		return new SqliteRenameMetaEntityRequest($metaEntity, $oldName, $newName);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\common\MetaManagerAdapter::buildDatabase()
	 * @return DatabaseAdapter
	 */
	protected function buildDatabase(): DatabaseAdapter {
		$database = new SqliteDatabase($this->determineDbCharset(), 
				$this->getPersistedMetaEntities(), $this->determineDbAttrs());
		
		foreach ($database->getMetaEntities() as $metaEntity) {
			if (!$metaEntity instanceof Table) continue;
			
			$this->metaEntityBuilder->applyIndexesForTable(
					SqliteDatabase::FIXED_DATABASE_NAME, $metaEntity);
		}
		
		return $database;
	}
	
	private function determineDbCharset() {
		return $this->getPragma('encoding');
	}
	
	private function determineDbAttrs() {
		return ['auto_vacuum' => $this->getPragma('auto_vacuum'),
				'cache_size' => $this->getPragma('cache_size'),
	//			'case_sensitive_like' => $this->getPragma('case_sensitive_like'),
				'count_changes' => $this->getPragma('count_changes'),
	//			'default_synchronous' => $this->getPragma('default_synchronous'),
				'empty_result_callbacks' => $this->getPragma('empty_result_callbacks'),
				'full_column_names' => $this->getPragma('full_column_names'),
				'fullfsync' => $this->getPragma('fullfsync'),
	//			'legacy_file_format' => $this->getPragma('legacy_file_format'),
				'page_size' => $this->getPragma('page_size'),
				'read_uncommitted' => $this->getPragma('read_uncommitted'),
				'short_column_names' => $this->getPragma('short_column_names'),
				'synchronous' => $this->getPragma('synchronous'),
				'temp_store' => $this->getPragma('temp_store'),
	//			'temp_store_directory' => $this->getPragma('temp_store_directory'),
		];
	}
	
	private function getPragma(string $name) {
		$sql = 'PRAGMA ' . $this->dbh->quoteField(SqliteDatabase::FIXED_DATABASE_NAME) . '.' . $name;
		$statement = $this->dbh->prepare($sql);
		$statement->execute();
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		
		return $result[$name];
	}
	
	protected function getPersistedMetaEntities() {
		$metaEntities = array();
		$sql = 'SELECT * FROM ' . $this->dbh->quoteField(SqliteDatabase::FIXED_DATABASE_NAME) 
				. '.sqlite_master WHERE type in (:type_table, :type_view) AND  '
				. $this->dbh->quoteField('name') . 'NOT LIKE :reserved_names';
		$statement = $this->dbh->prepare($sql);
		$statement->execute(
				[':type_table' => SqliteMetaEntityBuilder::TYPE_TABLE,
						':type_view' => SqliteMetaEntityBuilder::TYPE_VIEW,
						':reserved_names' => SqliteDatabase::RESERVED_NAME_PREFIX . '%']);
		while (null != ($result =  $statement->fetch(Pdo::FETCH_ASSOC))) {
			$metaEntities[$result['name']] = $this->metaEntityBuilder->createMetaEntity(
					SqliteDatabase::FIXED_DATABASE_NAME, $result['name']);
		}
		return $metaEntities;
	}
}
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
use n2n\persistence\meta\structure\Backuper;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\common\MetaManagerAdapter;
use n2n\persistence\meta\structure\common\DatabaseAdapter;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\impl\persistence\meta\pgsql\management\PgsqlAlterMetaEntityRequest;
use n2n\impl\persistence\meta\pgsql\management\PgsqlCreateMetaEntityRequest;
use n2n\impl\persistence\meta\pgsql\management\PgsqlDropMetaEntityRequest;
use n2n\impl\persistence\meta\pgsql\management\PgsqlRenameMetaEntityRequest;

class PgsqlMetaManager extends MetaManagerAdapter {
	const TABLE_SCHEMA = 'public';
	private $metaEntityBuilder;
	
	public function __construct(Pdo $dbh) {
		parent::__construct($dbh);
		$this->metaEntityBuilder = new PgsqlMetaEntityBuilder($dbh);
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Database::createBackuper()
	 * @return Backuper
	 */
	public function createBackuper(array $metaEnities = null): Backuper {
		return new PgsqlBackuper($this->dbh, $this->createDatabase(), $metaEnities);
	}
	
	protected function createAlterMetaEntityRequest(MetaEntity $metaEntity) {
		return new PgsqlAlterMetaEntityRequest($metaEntity);
	}
	
	protected function createCreateMetaEntityRequest(MetaEntity $metaEntity) {
		return new PgsqlCreateMetaEntityRequest($metaEntity);
	}
	
	protected function createDropMetaEntityRequest(MetaEntity $metaEntity) {
		return new PgsqlDropMetaEntityRequest($metaEntity);
	}
	
	protected function createRenameMetaEntityRequest(MetaEntity $metaEntity, string $oldName, string $newName) {
		return new PgsqlRenameMetaEntityRequest($metaEntity, $oldName, $newName);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\common\MetaManagerAdapter::buildDatabase()
	 * @return DatabaseAdapter
	 */
	protected function buildDatabase(): DatabaseAdapter {
		$dbName = $this->determineDbName();
		$database = new PgsqlDatabase($dbName, $this->determineDbCharset($dbName), 
				$this->getPersistedMetaEntities($dbName), $this->determineDbAttrs($dbName));
		
		foreach ($database->getMetaEntities() as $metaEntity) {
			if (!$metaEntity instanceof Table) continue;
			
			$this->metaEntityBuilder->applyIndexesForTable($dbName, $metaEntity);
		}
		
		return $database;
	}
	
	private function determineDbName() {
		$stmt = $this->dbh->prepare('SELECT CURRENT_DATABASE() AS name');
		$stmt->execute();
		$result = $stmt->fetch(Pdo::FETCH_ASSOC);
		return $result['name'];
	} 
	
	private function determineDbCharset(string $dbName) {
		$stmt = $this->dbh->prepare('SELECT PG_ENCODING_TO_CHAR(ENCODING) AS charset FROM pg_database WHERE datname = ?;');
		$stmt->execute(array($dbName));
		$result = $stmt->fetch(Pdo::FETCH_ASSOC);
		return $result['charset'];
	}
	
	private function determineDbAttrs(string $dbName) {
		$attrs = [];
		$stmt = $this->dbh->prepare('SHOW ALL');
		$stmt->execute();
		$result = $stmt->fetchAll(Pdo::FETCH_ASSOC);
		
		foreach ($result as $res) {
			$attrs[$res['name']] = $res['setting'];
		}
		
		return $attrs;
	}
	
	private function getPersistedMetaEntities(string $dbName) {
		$sql = 'SELECT table_name FROM information_schema.tables WHERE table_catalog = ? AND table_schema = ?';
		$stmt = $this->dbh->prepare($sql);
		$stmt->execute(array($dbName, self::TABLE_SCHEMA));
		$result = $stmt->fetchAll(Pdo::FETCH_ASSOC);
		
		$metaEntities = array();
		foreach ($result as $row) {
			$metaEntities[$row['table_name']] = $this->metaEntityBuilder->createMetaEntity($dbName, $row['table_name']);
		}
		return $metaEntities;
	}
}
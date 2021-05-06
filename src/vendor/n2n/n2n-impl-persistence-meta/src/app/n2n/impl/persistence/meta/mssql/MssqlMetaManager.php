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

use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\Backuper;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\common\MetaManagerAdapter;
use n2n\persistence\meta\structure\common\DatabaseAdapter;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\impl\persistence\meta\mssql\management\MssqlAlterMetaEntityRequest;
use n2n\impl\persistence\meta\mssql\management\MssqlCreateMetaEntityRequest;
use n2n\impl\persistence\meta\mssql\management\MssqlDropMetaEntityRequest;
use n2n\impl\persistence\meta\mssql\management\MssqlRenameMetaEntityRequest;

class MssqlMetaManager extends MetaManagerAdapter {
	private $metaEntityBuilder;
	
	public function __construct(Pdo $dbh) {
		parent::__construct($dbh);
		$this->metaEntityBuilder = new MssqlMetaEntityBuilder($dbh);
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Database::createBackuper()
	 * @return Backuper
	 */
	public function createBackuper(array $metaEnities = null): Backuper {
		return new MssqlBackuper($this->dbh, $this->createDatabase(), $metaEnities);
	}
	
	protected function createAlterMetaEntityRequest(MetaEntity $metaEntity) {
		return new MssqlAlterMetaEntityRequest($metaEntity);
	}
	
	protected function createCreateMetaEntityRequest(MetaEntity $metaEntity) {
		return new MssqlCreateMetaEntityRequest($metaEntity);
	}
	
	protected function createDropMetaEntityRequest(MetaEntity $metaEntity) {
		return new MssqlDropMetaEntityRequest($metaEntity);
	}
	
	protected function createRenameMetaEntityRequest(MetaEntity $metaEntity, string $oldName, string $newName) {
		return new MssqlRenameMetaEntityRequest($metaEntity, $oldName, $newName);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\common\MetaManagerAdapter::buildDatabase()
	 * @return DatabaseAdapter
	 */
	protected function buildDatabase(): DatabaseAdapter {
		$dbName = $this->determineDbName();
		$database = new MssqlDatabase($dbName, $this->determineDbCharset($dbName), 
				$this->getPersistedMetaEntities($dbName), $this->determineDbAttrs($dbName));
		
		foreach ($database->getMetaEntities() as $metaEntity) {
			if (!$metaEntity instanceof Table) continue;
			
			$this->metaEntityBuilder->applyIndexesForTable($dbName, $metaEntity);
		}
		
		return $database;
	}
	
	private function determineDbName() {
		$sql = 'SELECT DB_NAME() as database_name';
		$statement = $this->dbh->prepare($sql);
		$statement->execute();
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		return $result['database_name'];
	} 
	
	private function determineDbCharset(string $dbName) {
		$sql = 'SELECT collation_name FROM sys.' . $this->dbh->quoteField('databases') . ' WHERE name = :name';
		$statement = $this->dbh->prepare($sql);
		$statement->execute(array(':name' => $dbName));
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		return $result['collation_name'];
	}
	
	private function determineDbAttrs(string $dbName) {
		$sql = 'SELECT * from sys.' . $this->dbh->quoteField('databases') . ' where name = :name';
		$statement = $this->dbh->prepare($sql);
		$statement->execute(array(':name' => $dbName));
		return $statement->fetch(Pdo::FETCH_ASSOC);
	}
	
	private function getPersistedMetaEntities(string $dbName) {
		$metaEntities = array();
		$sql = 'SELECT * FROM information_schema.' . $this->dbh->quoteField('TABLES') . ' WHERE TABLE_CATALOG = :TABLE_CATALOG;';
		$statement = $this->dbh->prepare($sql);
		$statement->execute(array(':TABLE_CATALOG' => $dbName));
		while (null != ($result = $statement->fetch(Pdo::FETCH_ASSOC))) {
			$metaEntities[$result['TABLE_NAME']] = $this->metaEntityBuilder->createMetaEntity($result['TABLE_NAME']);
		}
		return $metaEntities;
	}
}
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

use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\Backuper;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\common\MetaManagerAdapter;
use n2n\persistence\meta\structure\common\DatabaseAdapter;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\impl\persistence\meta\oracle\management\OracleAlterMetaEntityRequest;
use n2n\impl\persistence\meta\oracle\management\OracleCreateMetaEntityRequest;
use n2n\impl\persistence\meta\oracle\management\OracleDropMetaEntityRequest;
use n2n\impl\persistence\meta\oracle\management\OracleRenameMetaEntityRequest;

class OracleMetaManager extends MetaManagerAdapter {
	private $metaEntityBuilder;
	
	public function __construct(Pdo $dbh) {
		parent::__construct($dbh);
		$this->metaEntityBuilder = new OracleMetaEntityBuilder($dbh);
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Database::createBackuper()
	 * @return Backuper
	 */
	public function createBackuper(array $metaEnities = null): Backuper {
		return new OracleBackuper($this->dbh, $this->createDatabase(), $metaEnities);
	}
	
	protected function createAlterMetaEntityRequest(MetaEntity $metaEntity) {
		return new OracleAlterMetaEntityRequest($metaEntity);
	}
	
	protected function createCreateMetaEntityRequest(MetaEntity $metaEntity) {
		return new OracleCreateMetaEntityRequest($metaEntity);
	}
	
	protected function createDropMetaEntityRequest(MetaEntity $metaEntity) {
		return new OracleDropMetaEntityRequest($metaEntity);
	}
	
	protected function createRenameMetaEntityRequest(MetaEntity $metaEntity, string $oldName, string $newName) {
		return new OracleRenameMetaEntityRequest($metaEntity, $oldName, $newName);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\common\MetaManagerAdapter::buildDatabase()
	 * @return DatabaseAdapter
	 */
	protected function buildDatabase(): DatabaseAdapter {
		$dbName = $this->determineDbName();
		$database = new OracleDatabase($dbName, $this->determineDbCharset(), 
				$this->getPersistedMetaEntities($dbName), $this->determineDbAttrs($dbName));
		
		foreach ($database->getMetaEntities() as $metaEntity) {
			if (!$metaEntity instanceof Table) continue;
			
			$this->metaEntityBuilder->applyIndexesForTable($dbName, $metaEntity);
		}
		
		return $database;
	}
	
	private function determineDbName() {
		$sql = 'SELECT SYS_CONTEXT(\'userenv\',\'instance_name\') AS NAME FROM DUAL';
		$statement = $this->dbh->prepare($sql);
		$statement->execute();
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		return $result['NAME'];
	} 
	
	private function determineDbCharset() {
		$sql = 'SELECT * FROM NLS_DATABASE_PARAMETERS  WHERE PARAMETER = \'NLS_CHARACTERSET\'';
		$statement = $this->dbh->prepare($sql);
		$statement->execute();
		$result = $statement->fetch(Pdo::FETCH_ASSOC);
		return $result['VALUE'];
	}
	
	private function determineDbAttrs(string $dbName) {
		$sql = 'SELECT * FROM product_component_version';
		$statement = $this->dbh->prepare($sql);
		$results = $statement->fetchAll(Pdo::FETCH_ASSOC);
		return $results;
	}
	
	private function getPersistedMetaEntities() {
		$metaEntities = array();
		//First check for tables
		$statement = $this->dbh->prepare('SELECT * FROM user_tables WHERE tablespace_name = :users');
		$statement->execute(array(':users' => 'USERS'));
		
		while (null != ($result =  $statement->fetch(Pdo::FETCH_ASSOC))) {
			$metaEntities[] = $this->metaEntityBuilder->createTable($result['TABLE_NAME']);
		}
		
		//Then for views
		$statement = $this->dbh->prepare('SELECT * FROM user_views');
		$statement->execute();
		
		while (null != ($result = $statement->fetch(Pdo::FETCH_ASSOC))) {
			$metaEntities[] = $this->metaEntityBuilder->createView($result['VIEW_NAME']);
		}
		return $metaEntities;
	}
}
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
namespace n2n\persistence\meta\structure\common;

use n2n\persistence\meta\MetaManager;
use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\Database;

abstract class MetaManagerAdapter implements MetaManager, DatabaseChangeListener {
	protected $dbh;
	private $metaEntityBuilder;
	private $changeRequestQueue;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
		$this->changeRequestQueue = new ChangeRequestQueue();
	}

	public function flush() {
		$this->changeRequestQueue->persist($this->dbh);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\MetaManager::createDatabase()
	 * @return Database
	 */
	public function createDatabase(): Database {
		$database = $this->buildDatabase();
		$database->registerChangeListener($this);
		
		return $database;
	}
	
	/**
	 * @param MetaEntity $metaEntity
	 */
	public function onMetaEntityCreate(MetaEntity $metaEntity) {
		$this->addChangeRequest($this->createCreateMetaEntityRequest($metaEntity));
	}
	
	/**
	 * @param MetaEntity $metaEntity
	 */
	public function onMetaEntityAlter(MetaEntity $metaEntity) {
		$this->addChangeRequest($this->createAlterMetaEntityRequest($metaEntity));
	}
	
	/**
	 * @param MetaEntity $metaEntity
	 */
	public function onMetaEntityDrop(MetaEntity $metaEntity) {
		$this->addChangeRequest($this->createDropMetaEntityRequest($metaEntity));
	}
	
	/**
	 * @param MetaEntity $metaEntity
	 */
	public function onMetaEntityNameChange(string $orignalName, MetaEntity $metaEntity) {
		$this->addChangeRequest($this->createRenameMetaEntityRequest(
				$metaEntity, $orignalName, $metaEntity->getName()));
	}
	
	private function addChangeRequest(ChangeRequest $changeRequest) {
		$ignore = false;
		foreach ($this->changeRequestQueue->getAll() as $aChangeRequest) {
			if ($aChangeRequest->isNeutralizedBy($changeRequest)) {
				$this->changeRequestQueue->remove($aChangeRequest);
				return;
			}
			
			$ignore = $ignore || $changeRequest->neutralizesChangeRequest($changeRequest);
		}
		
		if ($ignore) return;
		
		$this->changeRequestQueue->add($changeRequest);
	}
	
	protected abstract function createCreateMetaEntityRequest(MetaEntity $metaEntity);
	protected abstract function createAlterMetaEntityRequest(MetaEntity $metaEntity);
	protected abstract function createDropMetaEntityRequest(MetaEntity $metaEntity);
	protected abstract function createRenameMetaEntityRequest(MetaEntity $metaEntity, string $oldName, string $newName);
	protected abstract function buildDatabase(): DatabaseAdapter;
}
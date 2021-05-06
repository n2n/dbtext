<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\draft\stmt\impl;

use n2n\persistence\Pdo;
use rocket\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\meta\data\QueryPlaceMarker;
use rocket\ei\manage\draft\stmt\DraftMetaInfo;
use n2n\persistence\meta\data\QueryComparator;
use rocket\ei\EiPropPath;
use n2n\persistence\orm\property\BasicEntityProperty;

class SimplePersistDraftStmtBuilder extends DraftStmtBuilderAdapter implements PersistDraftStmtBuilder {
	private $idEntityProperty;
	private $peristStatementBuilder;
	
	private $boundCallbacks = array();
	
	public function __construct(Pdo $pdo, string $tableName, BasicEntityProperty $idEntityProperty, int $draftId = null) {
		parent::__construct($pdo, $tableName);

		$this->idEntityProperty = $idEntityProperty;
		$this->aliasBuilder = new AliasBuilder();
		if ($draftId === null) {
			$this->peristStatementBuilder = $pdo->getMetaData()->createInsertStatementBuilder();
		} else {
			$this->peristStatementBuilder = $pdo->getMetaData()->createUpdateStatementBuilder();
			$placeholderName = $this->aliasBuilder->createPlaceholderName();
			$this->bindValue($placeholderName, $draftId);
			$this->peristStatementBuilder->getWhereComparator()->match(new QueryColumn(DraftMetaInfo::COLUMN_ID), 
					QueryComparator::OPERATOR_EQUAL, new QueryPlaceMarker($placeholderName));
		}
		
		$this->peristStatementBuilder->setTable($this->tableName);
	}
	
	public function getPdo(): Pdo {
		return $this->pdo;
	}

	private $needless = false;
	public function setNeedless(bool $needless) {
		$this->needless = $needless;
	}
	
	public function isNeedless() {
		return $this->needless;
	}
	
	public function setLastMod(\DateTime $dateTime) {
		$this->setRawValue(DraftMetaInfo::COLUMN_LAST_MOD, $this->pdo->getMetaData()->getDialect()
				->getOrmDialectConfig()->buildDateTimeRawValue($dateTime));
	}
	
	public function setType(string $type = null) {
		$this->setRawValue(DraftMetaInfo::COLUMN_TYPE, $type);
	}
	
	public function setUserId(int $userId) {
		$this->setRawValue(DraftMetaInfo::COLUMN_USER_ID, $userId);
	}
	
	public function setDraftedEntityObjId($entityObjIdValue) {
		$this->setRawValue(DraftMetaInfo::COLUMN_ENTIY_OBJ_ID, $entityObjIdValue);
	}
	
	public function hasValues(): bool {
		return empty($this->boundValues) && empty($this->boundCallbacks);
	}
	
	private function setRawValue(string $columnName, $rawValue) {
		$placeholderName = $this->createPlaceholderName();
		
		$this->peristStatementBuilder->addColumn(new QueryColumn($columnName),
				new QueryPlaceMarker($placeholderName));
		$this->bindValue($placeholderName, $rawValue);
		return $placeholderName;
	}
	
	public function registerColumnRawValue(EiPropPath $eiPropPath, string $rawValue = null) {
		return $this->setRawValue(DraftMetaInfo::buildDraftColumnName($eiPropPath), $rawValue);
	}
	
	public function registerColumnCallback(EiPropPath $eiPropPath, \Closure $bindCallback) {
		$columnName = DraftMetaInfo::buildDraftColumnName($eiPropPath);
		$placeholderName = $this->aliasBuilder->createPlaceholderName();
		$this->peristStatementBuilder->addColumn(new QueryColumn($columnName), new QueryPlaceMarker($placeholderName));
		$this->boundCallbacks[$placeholderName] = $bindCallback;
		return $placeholderName;
	}
	
	public function buildPdoStatement() {
		if ($this->needless) return null;
		
		$stmt = $this->pdo->prepare($this->peristStatementBuilder->toSqlString());
		$this->applyBoundValues($stmt);
		foreach ($this->boundCallbacks as $placeholderName => $bindCallback) {
			$bindCallback($stmt, $placeholderName);
		}
		
		return $stmt;
	}
}

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

use n2n\persistence\meta\data\QueryTable;
use n2n\persistence\Pdo;
use rocket\ei\EiPropPath;
use n2n\persistence\meta\data\QueryColumn;
use rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use rocket\ei\manage\draft\stmt\DraftMetaInfo;
use rocket\ei\manage\draft\DraftValueSelection;
use rocket\ei\manage\draft\stmt\DraftValuesResult;
use n2n\persistence\orm\property\BasicEntityProperty;
use n2n\persistence\PdoStatement;
use n2n\persistence\meta\data\QueryItem;
use n2n\persistence\meta\data\SelectStatementBuilder;
use rocket\ei\manage\draft\Draft;

class SimpleFetchDraftStmtBuilder extends DraftStmtBuilderAdapter implements FetchDraftStmtBuilder {
	const DRAF_COLUMN_PREFIX = 'd';

	private $idEntityProperty;
	private $selectBuilder;
	private $tableAlias;
	
	private $idAlias;
	private $entityObjIdAlias;
	private $lastModAlias;
	private $typeAlias;	
	private $userIdAlias;
	
	private $boundIdRawValue;
	private $boundEntityObjIdRawValue;
	private $boundLastModRawValue;
	private $boundTypeRawValue;
	private $boundUserIdRawValue;
	private $draftValueSelections = array();
	
	public function __construct(Pdo $pdo, string $tableName, BasicEntityProperty $idEntityProperty, string $tableAlias = null) {
		parent::__construct($pdo, $tableName);
		
		$this->idEntityProperty = $idEntityProperty;
		$this->selectBuilder = $pdo->getMetaData()->createSelectStatementBuilder();
		$this->selectBuilder->addFrom(new QueryTable($tableName), $tableAlias);
		$this->tableAlias = $tableAlias;

		$this->idAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_ID);
		$this->entityObjIdAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_ENTIY_OBJ_ID);
		$this->typeAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_TYPE);
		$this->userIdAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_USER_ID);
		$this->lastModAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_LAST_MOD);

		$this->selectBuilder->addSelectColumn($this->getDraftIdQueryItem(), $this->idAlias);
		$this->selectBuilder->addSelectColumn($this->getEntityObjIdQueryItem(), $this->entityObjIdAlias);
		$this->selectBuilder->addSelectColumn($this->getTypeQueryItem(), $this->typeAlias);
		$this->selectBuilder->addSelectColumn($this->getUserIdQueryItem(), $this->userIdAlias);
		$this->selectBuilder->addSelectColumn($this->getLastModQueryItem(), $this->lastModAlias);
	}

	/**
	 * @return Pdo
	 */
	public function getPdo(): Pdo {
		return $this->pdo;
	}

	/**
	 * @return \n2n\persistence\meta\data\SelectStatementBuilder
	 */
	public function getSelectStatementBuilder(): SelectStatementBuilder {
		return $this->selectBuilder;
	}
	
	public function getTableName(): string {
		return $this->tableName;
	}
	
	public function getTableAlias() {
		return $this->tableAlias;
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return string column alias
	 */
	public function requestColumn(EiPropPath $eiPropPath): string {
		$columnName = DraftMetaInfo::buildDraftColumnName($eiPropPath);
		$columnAlias = $this->aliasBuilder->createColumnAlias($columnName);
		$this->selectBuilder->addSelectColumn(new QueryColumn($columnName, $this->tableAlias), $columnAlias);
		return $columnAlias;
	}

	public function putDraftValueSelection(EiPropPath $eiPropPath, DraftValueSelection $draftValueSelection) {
		$this->draftValueSelections[(string) $eiPropPath] = $draftValueSelection;
	}
	
	public function buildPdoStatement(): PdoStatement {
		$stmt = $this->pdo->prepare($this->selectBuilder->toSqlString());
		
		$stmt->bindColumn($this->idAlias, $this->boundIdRawValue);
		$stmt->bindColumn($this->entityObjIdAlias, $this->boundEntityObjIdRawValue);
		$stmt->bindColumn($this->lastModAlias, $this->boundLastModRawValue);
		$stmt->bindColumn($this->typeAlias, $this->boundTypeRawValue);
		$stmt->bindColumn($this->userIdAlias, $this->boundUserIdRawValue);
		
		foreach ($this->draftValueSelections as $draftValueSelection) {
			$draftValueSelection->bind($stmt);	
		}
		
		foreach ($this->boundValues as $phName => $value) {
			$stmt->bindValue($phName, $value);
		}
		
		return $stmt;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder::getDraftIdQueryItem()
	 */
	public function getDraftIdQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_ID, $this->tableAlias);
	}

	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder::getLastModQueryItem()
	 */
	public function getLastModQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_LAST_MOD, $this->tableAlias);
	}

	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder::getEntityObjIdQueryItem()
	 */
	public function getEntityObjIdQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_ENTIY_OBJ_ID, $this->tableAlias);
	}
	
	public function getTypeQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_TYPE, $this->tableAlias);
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder::getUserIdQueryItem()
	*/
	public function getUserIdQueryItem(): QueryItem {
		return new QueryColumn(DraftMetaInfo::COLUMN_USER_ID, $this->tableAlias);
	}
	
	public function getIdAlias(): string {
		return $this->idAlias;
	}
	
	public function getEntityObjIdAlias(): string {
		return $this->entityObjIdAlias;
	}
	
	public function getTypeAlias(): string {
		return $this->typeAlias;
	}
	
	public function getLastModAlias(): string {
		return $this->lastModAlias;
	}
	
	public function getUserIdAlias(): string {
		return $this->userIdAlias;
	}
	
	public function getBoundIdRawValue() {
		return $this->boundIdRawValue;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder::buildResult()
	 */
	public function buildResult(): DraftValuesResult {
		$type = Draft::TYPE_NORMAL;
		if (in_array((int) $this->boundTypeRawValue, Draft::getTypes(), true)) {
			$type = (int) $this->boundTypeRawValue;
		}
		
		$values = array();
		foreach ($this->draftValueSelections as $eiPropPathStr => $draftValueSelection) {
			$values[$eiPropPathStr] = $draftValueSelection->buildDraftValue();
		}
		
		return new DraftValuesResult($this->boundIdRawValue, 
				$this->idEntityProperty->parseValue($this->boundEntityObjIdRawValue, $this->pdo), 
				$this->pdo->getMetaData()->getDialect()->getOrmDialectConfig()
						->parseDateTime($this->boundLastModRawValue), 
				$type, $this->boundUserIdRawValue, $values);
	}

}

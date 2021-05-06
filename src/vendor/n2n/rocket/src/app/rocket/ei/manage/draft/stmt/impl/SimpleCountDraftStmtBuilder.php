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
use n2n\persistence\meta\data\QueryColumn;
use rocket\ei\manage\draft\stmt\DraftMetaInfo;
use n2n\persistence\orm\property\BasicEntityProperty;
use n2n\persistence\PdoStatement;
use n2n\persistence\meta\data\QueryFunction;
use n2n\persistence\meta\data\QueryConstant;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\draft\stmt\CountDraftStmtBuilder;
use n2n\persistence\meta\data\QueryItem;
use n2n\persistence\meta\data\SelectStatementBuilder;

class SimpleCountDraftStmtBuilder extends DraftStmtBuilderAdapter implements CountDraftStmtBuilder {
	const DRAF_COLUMN_PREFIX = 'd';

	private $idEntityProperty;
	private $selectBuilder;
	private $tableAlias;
	
	private $countAlias;
	
	private $boundCountRawValue;

	public function __construct(Pdo $pdo, $tableName, BasicEntityProperty $idEntityProperty, $tableAlias = null) {
		parent::__construct($pdo, $tableName);
		
		$this->idEntityProperty = $idEntityProperty;
		$this->selectBuilder = $pdo->getMetaData()->createSelectStatementBuilder();
		$this->selectBuilder->addFrom(new QueryTable($tableName), $tableAlias);
		$this->tableAlias = $tableAlias;

		$this->countAlias = $this->aliasBuilder->createColumnAlias(DraftMetaInfo::COLUMN_ID);
		
		$this->selectBuilder->addSelectColumn(new QueryFunction('COUNT', new QueryConstant(1)), $this->countAlias);
		
	}
	
	/**
	 * @return \n2n\persistence\meta\data\SelectStatementBuilder
	 */
	public function getSelectStatementBuilder(): SelectStatementBuilder {
		return $this->selectBuilder;
	}
	
	public function buildPdoStatement(): PdoStatement {
		$stmt = $this->pdo->prepare($this->selectBuilder->toSqlString());
		$stmt->bindColumn($this->countAlias, $this->boundCountRawValue);
		$this->applyBoundValues($stmt);
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
	
	public function getCountAlias(): string {
		return $this->countAlias;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder::buildResult()
	 */
	public function buildResult(): int {
		if ($this->boundCountRawValue !== null) {
			return $this->boundCountRawValue;
		}
		
		throw new IllegalStateException();
	}
}

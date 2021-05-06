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
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\meta\data\QueryPlaceMarker;
use rocket\ei\manage\draft\stmt\DraftMetaInfo;
use n2n\persistence\meta\data\QueryComparator;
use rocket\ei\manage\draft\stmt\RemoveDraftStmtBuilder;
use n2n\persistence\PdoStatement;

class SimpleRemoveDraftStmtBuilder extends DraftStmtBuilderAdapter implements RemoveDraftStmtBuilder {
	private $draftId;
	private $deleteStatementBuilder;
	
	public function __construct(Pdo $pdo, string $tableName, int $draftId) {
		parent::__construct($pdo, $tableName);
		$this->draftId = $draftId;
		$this->deleteStatementBuilder = $pdo->getMetaData()->createDeleteStatementBuilder();
		$this->deleteStatementBuilder->setTable($tableName);

		$aliasBuilder = new AliasBuilder();
		$placeholderName = $aliasBuilder->createPlaceholderName();
		$this->deleteStatementBuilder->getWhereComparator()->match(new QueryColumn(DraftMetaInfo::COLUMN_ID), 
				QueryComparator::OPERATOR_EQUAL, new QueryPlaceMarker($placeholderName));
		$this->bindValue($placeholderName, $draftId);
	}
	
	public function buildPdoStatement(): PdoStatement {
		$stmt = $this->pdo->prepare($this->deleteStatementBuilder->toSqlString());
		$this->applyBoundValues($stmt);
		return $stmt;
	}
}

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
namespace rocket\ei\manage\draft;

use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\meta\data\QueryPlaceMarker;
use n2n\persistence\meta\data\OrderDirection;
use rocket\ei\manage\draft\stmt\SelectDraftStmtBuilder;
use n2n\persistence\meta\data\QueryConstant;

class RestrictedSelectDraftStmtBuilder {
	private $selectDraftStmtBuilder;
	private $bindValues = array();
	
	public function __construct(SelectDraftStmtBuilder $selectDraftStmtBuilder) {
		$this->selectDraftStmtBuilder = $selectDraftStmtBuilder;
	}
	
	public function restrictToDraftId(int $draftId) {
		$draftIdPhName = $this->selectDraftStmtBuilder->createPlaceholderName();
		
		$this->selectDraftStmtBuilder->getSelectStatementBuilder()->getWhereComparator()
				->andMatch($this->selectDraftStmtBuilder->getDraftIdQueryItem(), QueryComparator::OPERATOR_EQUAL,
						new QueryPlaceMarker($draftIdPhName));
	
		$this->selectDraftStmtBuilder->bindValue($draftIdPhName, $draftId);
	}
	
	public function restrictToEntityObjId($entityObjId) {
		$selectStatementBuilder = $this->selectDraftStmtBuilder->getSelectStatementBuilder();
	
		$entityObjIdPhName = $this->selectDraftStmtBuilder->createPlaceholderName();
		$selectStatementBuilder->getWhereComparator()
				->andMatch($this->selectDraftStmtBuilder->getEntityObjIdQueryItem(), QueryComparator::OPERATOR_EQUAL,
						new QueryPlaceMarker($entityObjIdPhName));
		
		$this->selectDraftStmtBuilder->bindValue($entityObjIdPhName, $entityObjId);
	}
	
	public function restrictToType(int $type, bool $invert = false) {
		$selectStatementBuilder = $this->selectDraftStmtBuilder->getSelectStatementBuilder();
		
		$typePhName = $this->selectDraftStmtBuilder->createPlaceholderName();
		$selectStatementBuilder->getWhereComparator()->andMatch(
				$this->selectDraftStmtBuilder->getTypeQueryItem(), ($invert ? '!=' : '='), 
				new QueryPlaceMarker($typePhName));
		
		$this->selectDraftStmtBuilder->bindValue($typePhName, $type);
	}
	
	public function restrictToUnbound(bool $unbound) {
		$selectStatementBuilder = $this->selectDraftStmtBuilder->getSelectStatementBuilder();
		$selectStatementBuilder->getWhereComparator()->andMatch(
				$this->selectDraftStmtBuilder->getEntityObjIdQueryItem(), 
				($unbound ? QueryComparator::OPERATOR_IS : QueryComparator::OPERATOR_IS_NOT), 
				new QueryConstant(null));
	}
	
	public function limit($limit = null, $num = null) {
		$selectStatementBuilder = $this->selectDraftStmtBuilder->getSelectStatementBuilder();
		$selectStatementBuilder->setLimit($limit, $num);
	}
	
	public function order() {
		$selectStatementBuilder = $this->selectDraftStmtBuilder->getSelectStatementBuilder();
		$selectStatementBuilder->addOrderBy($this->selectDraftStmtBuilder->getLastModQueryItem(), OrderDirection::DESC);
	}
}

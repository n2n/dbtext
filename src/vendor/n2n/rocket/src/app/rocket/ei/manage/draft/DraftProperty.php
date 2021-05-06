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

use n2n\persistence\PdoStatement;
use rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use n2n\core\container\N2nContext;
use rocket\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\ei\manage\draft\stmt\RemoveDraftStmtBuilder;

interface DraftProperty {
	/**
	 * @param FetchDraftStmtBuilder $selectDraftStmtBuilder
	 * @param N2nContext $n2nContext
	 * @return DraftValueSelection
	 */
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, 
			DraftManager $dm, N2nContext $n2nContext): DraftValueSelection;
	
	/**
	 * @param mixed $value
	 * @param PersistDraftStmtBuilder $persistDraftStmtBuilder
	 * @param DraftActionQueue $persistDraftAction
	 */
	public function supplyPersistDraftStmtBuilder($value, $oldValue, PersistDraftStmtBuilder $persistDraftStmtBuilder, 
			PersistDraftAction $persistDraftAction);
	
	/**
	 * @param mixed $value
	 * @param RemoveDraftStmtBuilder $removeDraftStmtBuilder
	 * @param DraftActionQueue $draftActionQueue
	 */
	public function supplyRemoveDraftStmtBuilder($value, $oldValue, RemoveDraftStmtBuilder $removeDraftStmtBuilder, 
			RemoveDraftAction $removeDraftAction);
	
	/**
	 * @param object $object
	 * @param mixed $value
	 */
	public function writeDraftValue($object, $value);
}

interface DraftValueSelection {
	
	public function bind(PdoStatement $stmt);
	
	public function buildDraftValue();
}

class SimpleDraftValueSelection implements DraftValueSelection {
	private $columnAlias;
	protected $rawValue;
	
	public function __construct(string $columnAlias) {
		$this->columnAlias = $columnAlias;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\draft\DraftValueSelection::bind()
	 */
	public function bind(PdoStatement $stmt) {
		$stmt->bindColumn($this->columnAlias, $this->rawValue);
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\draft\DraftValueSelection::buildDraftValue()
	 */
	public function buildDraftValue() {
		return $this->rawValue;
	}
}

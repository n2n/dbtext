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

use rocket\ei\manage\draft\stmt\DraftValuesResult;
use n2n\util\ex\IllegalStateException;

class DraftingContext {
	private $drafts = array(); 
	private $draftDefinitions = array();
	private $draftValuesResults = array();
	private $draftMap = array();
	
	public function add(DraftDefinition $draftDefinition, Draft $draft, DraftValuesResult $draftValuesResult = null) {
		$objHash = spl_object_hash($draft);
		$this->drafts[$objHash] = $draft;
		$this->draftDefinitions[$objHash] = $draftDefinition;
		$this->draftValuesResults[$objHash] = $draftValuesResult;
		
		if (!$draft->isNew()) {
			$this->identifyDraft($draft);
		}
	}
	
	public function setDraftValuesResult(Draft $draft, DraftValuesResult $draftValuesResult) {
		$objHash = spl_object_hash($draft);
		
		if (!isset($this->drafts[$objHash])) {
			throw new DraftingException('DraftingContext contains no such Draft.');
		}
		
		$this->draftValuesResults[$objHash] = $draftValuesResult;
	}
	
	public function identifyDraft(Draft $draft) {
		$objHash = spl_object_hash($draft);
		
		if (!isset($this->drafts[$objHash])) {
			throw new DraftingException('DraftingContext contains no such Draft.');
		}
		
		$tableName = $this->draftDefinitions[$objHash]->getTableName();
		if (!isset($this->draftMap[$tableName])) {
			$this->draftMap[$tableName] = array();
		}
		
		$draftId = $draft->getId();
		if (isset($this->draftMap[$tableName][$draftId])
				&& $this->draftMap[$tableName][$draftId] !== $draft) {
			throw new DraftingException('Draft with same id already in context: ' . $draftId);
		}
		
		$this->draftMap[$tableName][$draft->getId()] = $draft;
	}
		
	public function containsDraftId(DraftDefinition $draftDefinition, int $draftId): bool {
		return isset($this->draftMap[$draftDefinition->getTableName()][$draftId]);
	}
	
	public function getDraftById(DraftDefinition $draftDefinition, int $id): Draft {
		if (isset($this->draftMap[$draftDefinition->getTableName()][$id])) {
			return $this->draftMap[$draftDefinition->getTableName()][$id];
		}
		
		throw new IllegalStateException('DraftingContext contains no such Draft.');
	}
	
	public function getDraftDefinitionByDraft(Draft $draft) {
		$objHash = spl_object_hash($draft);
		if (isset($this->draftDefinitions[$objHash])) {
			return $this->draftDefinitions[$objHash];
		}
		
		throw new IllegalStateException('DraftingContext contains no such Draft.');
	}
	
	/**
	 * @param Draft $draft
	 * @return DraftValuesResult
	 */
	public function getDraftValuesResultByDraft(Draft $draft) {
		$objHash = spl_object_hash($draft);
		if (isset($this->draftValuesResults[$objHash])) {
			return $this->draftValuesResults[$objHash];
		}
		
		return new IllegalStateException('DraftingContext contains no such DraftValuesResult.');
	}
	
	public function persistContext(DraftActionQueue $draftActionQueue) {
		$draftActionQueue->initialize();
		
		foreach ($this->drafts as $draft) {
			if ($draftActionQueue->containsDraft($draft)) continue;
			
			$draftActionQueue->persist($draft);
		}
	}
}

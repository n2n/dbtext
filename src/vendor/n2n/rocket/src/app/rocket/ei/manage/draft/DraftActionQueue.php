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

use n2n\util\type\ArgUtils;
use n2n\persistence\orm\EntityManager;
use n2n\core\container\N2nContext;
use n2n\util\ex\IllegalStateException;

class DraftActionQueue {
	private $draftingContext;
	private $em;
	private $persistActions = array();
	private $removeActions = array();
	private $draftActions = array();
	
	public function __construct(DraftingContext $draftingContext, EntityManager $em, N2nContext $n2nContext) {
		$this->draftingContext = $draftingContext;
		$this->em = $em;
		$this->n2nContext = $n2nContext;
	}
	
	public function getDraftingContext() {
		return $this->draftingContext;
	}
	
	public function getEntityManager() {
		return $this->em;
	}
	
	public function persist(Draft $draft, DraftDefinition $draftDefinition = null): DraftAction {
		$objHash = spl_object_hash($draft);
		if (isset($this->persistActions[$objHash])) {
			return $this->persistActions[$objHash];
		}
		
		if (isset($this->removeActions[$objHash])) {
			throw new IllegalStateException('RemoveAction available.');
// 			$this->removeActions[$objHash]->disable();
// 			unset($this->removeActions[$objHash]);
		}
		
		if ($draft->isNew()) {
			ArgUtils::assertTrue($draftDefinition !== null, 'DraftDefinition required for new drafts.');
		} else {
			$draftDefinition = $this->draftingContext->getDraftDefinitionByDraft($draft);
		}
		
		$this->persistActions[$objHash] = $persistAction = new CommonModDraftAction($draft, $draftDefinition, $this);
		
		if (!$draft->isNew()) return $persistAction;
		
		$this->draftingContext->add($draftDefinition, $draft);
		$persistAction->executeAtEnd(function () use ($draft) {
			$this->draftingContext->identifyDraft($draft);
		});
		
		return $persistAction;
	}
	
	public function remove(Draft $draft, bool $important = false) {
		$objHash = spl_object_hash($draft);
		
		if (isset($this->removeActions[$objHash])) {
			return $this->removeActions[$objHash];
		}
		
		if (isset($this->persistActions[$objHash])) {
			if (!$important) return null;

			throw new IllegalStateException('PersistAction available.');
// 			$this->persistActions[$objHash]->disable();
// 			unset($this->persistActions[$objHash]);
		}
		
		$draftDefinition = $this->draftingContext->getDraftDefinitionByDraft($draft);
		
		if ($draft->isNew()) {
			$this->draftingContext->remove($draft);
			return null;
		}
				
		$this->removeActions[$objHash] = $removeAction = new CommonModDraftAction($draft, $draftDefinition, $this);
		
		if (!$draft->isNew()) return $removeAction;
		
		$this->draftingContext->remove($draft);
				
		return $removeAction;
	}
	
	public function containsDraft(Draft $draft) {
		$objHash = spl_object_hash($draft);
		return isset($this->persistActions[$objHash]) || isset($this->removeActions[$objHash]);
	}
	
	public function initialize() {
		$newInits = null;
		do {
			$newInits = false;
			
			foreach ($this->removeActions as $key => $removeAction) {
				if ($removeAction->isInitialized()) continue;
				
				$newInits = true;
				$removeAction->setDraftStmtBuilder($removeAction->getDraftDefinition()
						->createRemoveDraftStmtBuilder($removeAction, $this));
			}
			
			foreach ($this->persistActions as $key => $persistAction) {
				if ($persistAction->isInitialized()) continue;
				
				$newInits = true;
				$persistAction->setDraftStmtBuilder($persistAction->getDraftDefinition()
						->createPersistDraftStmtBuilder($persistAction, $this));
			}
		} while($newInits);
	}
	
	public function addDraftAction(DraftAction $draftAction) {
	    $this->draftActions[spl_object_hash($draftAction)] = $draftAction;
	}
	
	public function execute() {
		$this->initialize();
		
		while (null !== ($removeAction = array_pop($this->removeActions))) {
			$removeAction->execute();
		}
		
		while (null !== ($persistAction = array_pop($this->persistActions))) {
			$persistAction->execute();
		}
		
		while (null !== ($draftAction = array_pop($this->draftActions))) {
		    $draftAction->execute();
		}
	}
}

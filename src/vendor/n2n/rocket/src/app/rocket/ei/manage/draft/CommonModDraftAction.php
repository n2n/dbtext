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

use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\draft\stmt\DraftStmtBuilder;
use n2n\util\ex\NotYetImplementedException;

class CommonModDraftAction extends DraftActionAdapter implements PersistDraftAction, RemoveDraftAction {
	private $draft;
	private $draftDefinition;
	private $queue;
	
	private $draftStatementBuilder;
	
	public function __construct(Draft $draft, DraftDefinition $draftDefinition, DraftActionQueue $queue) {
		$this->draft = $draft;
		$this->draftDefinition = $draftDefinition;
		$this->queue = $queue;
		
		if ($draft->isNew()) {
			$this->idUpdate();
		}
	}
	
	private function idUpdate() {
		$pdo = $this->queue->getEntityManager()->getPdo();
		$dialect = $pdo->getMetaData()->getDialect();
		
		if (!$dialect->isLastInsertIdSupported()) {
			throw new NotYetImplementedException('Drafts for ' . get_class($dialect) . ' not yet supported.');
		}
		
		$that = $this;
		$this->executeAtEnd(function () use ($that) {
			$that->getDraft()->setId($that->getDraftStmtBuilder()->getPdo()->lastInsertId());
		});
	}
	
	public function getDraft(): Draft {
		return $this->draft;
	}
	
	public function getDraftDefinition(): DraftDefinition {
		return $this->draftDefinition;
	}
	
	public function getQueue(): DraftActionQueue {
		return $this->queue;
	}
	
	public function isInitialized() {
		return $this->draftStatementBuilder !== null;
	}
	
	public function setDraftStmtBuilder(DraftStmtBuilder $draftStatementBuilder) {
		$this->draftStatementBuilder = $draftStatementBuilder;
	}
	
	public function getDraftStmtBuilder() {
		if ($this->draftStatementBuilder !== null) {
			return $this->draftStatementBuilder;
		}
	
		throw new IllegalStateException('No DraftStmtBuilder assigned.');
	}
	
	protected function exec() {
		if (null !== ($pdoStatement = $this->getDraftStmtBuilder()->buildPdoStatement())) {
			$pdoStatement->execute();
		}
	}
	
}

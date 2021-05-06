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
namespace rocket\impl\ei\component\prop\adapter;

use n2n\util\ex\IllegalStateException;

use rocket\ei\manage\draft\DraftProperty;
use rocket\ei\manage\draft\stmt\FetchDraftStmtBuilder;
use n2n\core\container\N2nContext;
use rocket\ei\manage\draft\stmt\PersistDraftStmtBuilder;
use rocket\ei\manage\draft\SimpleDraftValueSelection;
use rocket\ei\manage\draft\DraftManager;
use rocket\ei\manage\draft\DraftValueSelection;
use rocket\ei\manage\draft\PersistDraftAction;
use rocket\ei\manage\draft\RemoveDraftAction;
use rocket\ei\EiPropPath;
use rocket\ei\manage\draft\stmt\RemoveDraftStmtBuilder;
use rocket\impl\ei\component\prop\adapter\config\DraftConfigurable;

abstract class DraftablePropertyEiPropAdapter extends EditablePropertyEiPropAdapter implements DraftConfigurable, DraftProperty {
	protected $draftable = false;

	public function isDraftable(): bool {
		return $this->draftable;
	}
	
	/**
	 * @param bool $draftable
	 */
	public function setDraftable(bool $draftable) {
		$this->draftable = $draftable;
	}
	
	
// 	protected function createEifField(Eiu $eiu): EifField {
// 		$eifField = parent::createEifField($eiu);
		
// 		// @todo implement
		
// 		return $eiField;
// 	}
	
	
	
// 	/* (non-PHPdoc)
// 	 * @see \rocket\impl\ei\component\prop\EditablePropertyEiPropAdapter::createEiConfigurator()
// 	 */
// 	public function createConfigurator(): AdaptableEiPropConfigurator {
// 		return parent::createConfigurator();
// 	}
	
		
	public function getDraftProperty() {
		if ($this->draftable) {
			return $this;
		}
		
		throw new IllegalStateException('EiProp not draftable.');
	}
	
	public function createDraftValueSelection(FetchDraftStmtBuilder $selectDraftStmtBuilder, DraftManager $dm, 
			N2nContext $n2nContext): DraftValueSelection {
		return new SimpleDraftValueSelection($selectDraftStmtBuilder->requestColumn(EiPropPath::from($this)));
	}
	
	public function supplyPersistDraftStmtBuilder($value, $oldValue, PersistDraftStmtBuilder $persistDraftStmtBuilder, 
			PersistDraftAction $persistDraftAction) {
		if ($value !== $oldValue) {
			$persistDraftStmtBuilder->registerColumnRawValue(EiPropPath::from($this), $value);
		}
	}
	
	public function supplyRemoveDraftStmtBuilder($value, $oldValue, RemoveDraftStmtBuilder $removeDraftStmtBuilder, 
			RemoveDraftAction $removeDraftAction) {
	}
	
	public function writeDraftValue($object, $value) {
		$this->getPropertyAccessProxy()->setValue($object, $value);
	}
	
// 	public function getDraftColumnName() {
// 		return $this->getEntityProperty()->getReferencedColumnName();
// 	}
	
// 	public function checkDraftMeta(Pdo $dbh) {
// 	}
	
// 	public function draftCopy($value) {
// 		return $value;
// 	}
	
// 	public function publishCopy($value) {
// 		return $value;
// 	}
	
// 	public function mapDraftValue($draftId, MappingJob $mappingJob, \ArrayObject $rawDataMap, \ArrayObject $mappedValues) {
// 		$this->getEntityProperty()->mapValue($mappingJob, $rawDataMap, $mappedValues);
// 	}
	
// 	public function supplyDraftPersistingJob($mappedValue, PersistingJob $persistingJob) {
// 		$this->getEntityProperty()->supplyPersistingJob($mappedValue, $persistingJob);
// 	}
	
// 	public function supplyDraftRemovingJob($mappedValue, RemovingJob $deletingJob) {
// 		$this->getEntityProperty()->supplyRemovingJob($mappedValue, $deletingJob);
// 	}
}

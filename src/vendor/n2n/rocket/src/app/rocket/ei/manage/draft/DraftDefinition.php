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

use rocket\ei\EiPropPath;
use n2n\core\container\N2nContext;
use rocket\ei\manage\draft\stmt\impl\SimplePersistDraftStmtBuilder;
use rocket\ei\manage\draft\stmt\impl\SimpleRemoveDraftStmtBuilder;
use rocket\ei\manage\draft\stmt\impl\SimpleFetchDraftStmtBuilder;
use rocket\ei\manage\draft\stmt\DraftValuesResult;
use n2n\util\type\ArgUtils;
use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\model\EntityModel;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\draft\stmt\impl\SimpleCountDraftStmtBuilder;

class DraftDefinition {
	const ALIAS = 'd';
	
	private $tableName;
	private $entityModel;
	private $draftProperties = array();
	
	public function __construct(string $tableName, EntityModel $entityModel) {
		$this->tableName = $tableName;
		$this->entityModel = $entityModel;
	}
	
	public function getTableName(): string {
		return $this->tableName;
	}
	
	public function getEntityModel(): EntityModel {
		return $this->entityModel;
	}

	public function putDraftProperty(EiPropPath $eiPropPath, DraftProperty $draftProperty) {
		return $this->draftProperties[(string) $eiPropPath] = $draftProperty;
	}
	
	public function containsEiPropPath(EiPropPath $eiPropPath) {
		return isset($this->draftProperties[(string) $eiPropPath]);
	}
	
	public function getDraftProperties() {
		return $this->draftProperties;
	}
	
	public function isEmpty() {
		return empty($this->draftProperties);
	}
	
	public function createCountDraftStmtBuilder(DraftManager $dm, N2nContext $n2nContext, $tableAlias = null) {
		$idEntityProperty = $this->entityModel->getIdDef()->getEntityProperty();
		return new SimpleCountDraftStmtBuilder($dm->getEntityManager()->getPdo(), $this->tableName,
				$idEntityProperty, $tableAlias);
	}
	
	public function createFetchDraftStmtBuilder(DraftManager $dm, N2nContext $n2nContext, $tableAlias = null) {
		$idEntityProperty = $this->entityModel->getIdDef()->getEntityProperty();
		$stmtBuilder = new SimpleFetchDraftStmtBuilder($dm->getEntityManager()->getPdo(), $this->tableName, 
				$idEntityProperty, $tableAlias);
		
		foreach ($this->draftProperties as $id => $draftProperty) {
			$stmtBuilder->putDraftValueSelection(new EiPropPath(array($id)), 
					$draftProperty->createDraftValueSelection($stmtBuilder, $dm, $n2nContext));
		}
		
		return $stmtBuilder;
	}
	
	public function createPersistDraftStmtBuilder(PersistDraftAction $persistDraftAction) {
		$draft = $persistDraftAction->getDraft();
		$pdo = $persistDraftAction->getQueue()->getEntityManager()->getPdo();
		$idEntityProperty = $this->entityModel->getIdDef()->getEntityProperty();
		$stmtBuilder = new SimplePersistDraftStmtBuilder($pdo, $this->tableName, $idEntityProperty, $draft->getId(false));
		
		$draftingContext = $persistDraftAction->getQueue()->getDraftingContext();
		
		$empty = false;
		$draftValuesResult = null;
		if (!$draft->isNew()) {
			$draftValuesResult = $draftingContext->getDraftValuesResultByDraft($draft);
			$empty = $draftValuesResult->getType() === $draft->getType() 
					&& $draftValuesResult->getLastMod() === $draft->getLastMod()
					&& $draftValuesResult->getUserId() === $draft->getUserId();
		}
		
		$stmtBuilder->setType($draft->getType());
		$stmtBuilder->setUserId($draft->getUserId());
		$stmtBuilder->setLastMod($draft->getLastMod());
		
		$eiEntityObj = $draft->getEiEntityObj();
		if ($eiEntityObj->isPersistent()) { 
			$stmtBuilder->setDraftedEntityObjId($idEntityProperty->buildRaw($draft->getEiEntityObj()->getId(), $pdo));
		}
		
		$draftValuesMap = $draft->getDraftValueMap();
		foreach ($this->draftProperties as $id => $draftProperty) {
			$eiPropPath = new EiPropPath(array($id));
			$oldValue = null;
			if ($draftValuesResult !== null) {
				$oldValue = $draftValuesResult->getValue($eiPropPath);
			}
			
			$draftProperty->supplyPersistDraftStmtBuilder($draftValuesMap->getValue($eiPropPath), 
					$oldValue, $stmtBuilder, $persistDraftAction);
		}
		
		if ($empty && $stmtBuilder->hasValues()) {
			$stmtBuilder->setNeedless(true);
			return $stmtBuilder;
		}
		
		$persistDraftAction->executeAtEnd(function () use ($draftingContext, $draft) {
			$newDraftValuesResult = new DraftValuesResult($draft->getId(), 
					($draft->getEiEntityObj()->hasId() ? $draft->getEiEntityObj()->getId() : null),
					$draft->getLastMod(), $draft->getType(), $draft->getUserId(), 
					$draft->getDraftValueMap()->getValues());
			$draftingContext->setDraftValuesResult($draft, $newDraftValuesResult);
		});
		
		return $stmtBuilder;
	}
	
	public function createRemoveDraftStmtBuilder(RemoveDraftAction $removeDraftAction, DraftActionQueue $draftActionQueue) {
		$draft = $removeDraftAction->getDraft();
		$draftActionQueue = $removeDraftAction->getQueue();
		$statementBuilder = new SimpleRemoveDraftStmtBuilder($draftActionQueue->getEntityManager()->getPdo(), 
				$this->tableName, $draft->getId());
		
		$draftValuesMap = $draft->getDraftValueMap();
		$draftValuesResult = $draftActionQueue->getDraftingContext()->getDraftValuesResultByDraft($draft);		
		foreach ($this->draftProperties as $id => $draftProperty) {
			$eiPropPath = new EiPropPath(array($id));
			$oldValue = $draftValuesResult->getValue($eiPropPath);
			
			$draftProperty->supplyRemoveDraftStmtBuilder($draftValuesMap->getValue($eiPropPath), $oldValue,
					$statementBuilder, $removeDraftAction);
		}
		
		return $statementBuilder;
	}
	
	public function createDraftedEntityObj(DraftValuesResult $draftValuesResult, $baseEntityObj = null) {
		ArgUtils::valObject($baseEntityObj, true);
		
		$draftedEntityObj = ReflectionUtils::createObject($this->entityModel->getClass());
		if ($baseEntityObj !== null) {
			$this->entityModel->copy($baseEntityObj, $draftedEntityObj);
		}
		
		$values = $draftValuesResult->getValues();
		foreach ($this->draftProperties as $id => $draftProperty) {
			IllegalStateException::assertTrue(array_key_exists($id, $values)); 
			$draftProperty->writeDraftValue($draftedEntityObj, $values[$id]);
		}
		
		return $draftedEntityObj;
	}
}

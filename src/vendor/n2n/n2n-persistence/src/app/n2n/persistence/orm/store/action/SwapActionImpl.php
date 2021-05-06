<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
// namespace n2n\persistence\orm\store;

// use n2n\persistence\meta\data\QueryColumn;
// use n2n\persistence\meta\data\QueryPlaceMarker;
// use n2n\util\ex\IllegalStateException;
// use n2n\persistence\meta\data\QueryComparator;
// use n2n\persistence\orm\store\action\SwapAction;

// class SwapActionImpl extends EntityActionAdapter implements SwapAction {
// 	private $removeActionQueue;
// 	private $removeMeta;
// 	private $persistenceActionQueue;
// 	private $persistMeta;
	
// 	public function __construct(RemoveActionQueue $removeActionQueue, ActionMeta $removeMeta, PersistActionQueue $persistenceActionQueue, ActionMeta $persistMeta) {
// 		$this->removeActionQueue = $removeActionQueue;
// 		$this->removeMeta = $removeMeta;
// 		$this->persistenceActionQueue = $persistenceActionQueue;
// 		$this->persistMeta = $persistMeta;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\RemoveAction::getActionQueue()
// 	 */
// 	public function getActionQueue() {
// 		return $this->removeActionQueue;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\RemoveAction::getRemoveMeta()
// 	 */
// 	public function getRemoveMeta() {
// 		return $this->removeMeta;	
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\action\PersistAction::getPersistActionQueue()
// 	 */
// 	public function getPersistActionQueue() {
// 		return $this->persistenceActionQueue;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\action\PersistAction::getMeta()
// 	 */
// 	public function getMeta() {
// 		return $this->persistMeta;		
// 	}

// 	public function execute() {
// 		if ($this->executed) return;
// 		$this->executed = true;
				
// 		$this->triggerAtStartClosures();
		
// 		$removeItems = $this->removeMeta->getItems();
// 		$persistItems = $this->persistMeta->getItems();
		
// 		foreach ($removeItems as $riKey => $removeItem) {
// 			foreach ($persistItems as $piKey => $persistItem) {
// 				if ($removeItem->getTableName() != $persistItem->getTableName()) continue;
				
// 				unset($removeItems[$riKey]);
// 				unset($persistItems[$piKey]);
				
// 				$this->update($removeItem, $persistItem);
// 			}
			
// 			if (isset($removeItems[$riKey])) {
// 				$this->remove($removeItem);
// 			}
// 		}
		
// 		foreach ($persistItems as $persistItem) {
// 			$this->insert($persistItem);
// 		}
		
// 		$this->triggerAtEndClosures();
// 	}
	
// 	private function update(ActionMetaItem $removeItem, ActionMetaItem $persistItem) {
// 		if ($removeItem->isEmpty() && $persistItem->isEmpty()) {
// 			return;
// 		}

// 		$generatedColumnName = null;
// 		if ($this->persistMeta->isIdGenerated()) {
// 			$generatedColumnName = $this->persistMeta->getIdColumnName();
// 		}
		
// 		$dbh = $this->persistenceActionQueue->getPdo();
// 		$metaData = $dbh->getMetaData();

// 		$updateBuilder = $metaData->createUpdateStatementBuilder();
// 		$updateBuilder->setTable($persistItem->getTableName());
// 		$rawValues = array();
		
// 		foreach ($removeItem->getRawValues() as $columnName => $rawValue) {
// 			if ($generatedColumnName === $columnName) continue;
// 			$updateBuilder->addColumn(new QueryColumn($columnName), new QueryPlaceMarker());
// 			$rawValues[] = $rawValue;
// 		}
		
// 		foreach ($persistItem->getRawValues() as $columnName => $rawValue) {
// 			if ($generatedColumnName === $columnName) continue;
// 			$updateBuilder->addColumn(new QueryColumn($columnName), new QueryPlaceMarker());
// 			$rawValues[] = $rawValue;
// 		}
		
// 		if (!sizeof($rawValues)) {
// 			return;
// 		}
		
// 		$updateBuilder->getWhereComparator()->match(new QueryColumn($this->persistMeta->getIdColumnName()),
// 				QueryComparator::OPERATOR_EQUAL, new QueryPlaceMarker());
// 		$rawValues[] = $this->persistMeta->getId();
// 		$stmt = $dbh->prepare($updateBuilder->toSqlString());
// 		$stmt->execute($rawValues);	
// 	}
	
// 	private function insert(ActionMetaItem $persistItem) {
// 		if ($persistItem->isIdGenerated()) {
// 			throw IllegalStateException::createDefault();
// 		}
		
// 		$dbh = $this->persistenceActionQueue->getPdo();
// 		$metaData = $dbh->getMetaData();
		
// 		$insertBuilder = $metaData->createInsertStatementBuilder();
// 		$insertBuilder->setTable($persistItem->getTableName());
// 		$rawValues = array();
		
// 		foreach ($persistItem->getRawValues() as $columnName => $rawValue) {
// 			$insertBuilder->addColumn(new QueryColumn($columnName), new QueryPlaceMarker());
// 			$rawValues[] = $rawValue;
// 		}
				
// 		$stmt = $dbh->prepare($insertBuilder->toSqlString());
// 		$stmt->execute($rawValues);
// 	}
	
// 	private function remove(ActionMetaItem $removeItem) {
// 		$dbh = $this->removeActionQueue->getPdo();
// 		$metaData = $dbh->getMetaData();
		
// 		$deleteBuilder = $dbh->getMetaData()->createDeleteStatementBuilder();
// 		$deleteBuilder->setTable($removeItem->getTableName());
// 		$deleteBuilder->getWhereComparator()->match(new QueryColumn($this->removeMeta->getIdColumnName()),
// 				QueryComparator::OPERATOR_EQUAL, new QueryPlaceMarker());
		
// 		$stmt = $dbh->prepare($deleteBuilder->toSqlString());
// 		$stmt->execute(array($this->removeMeta->getId()));
// 	}
	
// }

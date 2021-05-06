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
// namespace rocket\ei\adaptive;

// use n2n\persistence\orm\store\ActionJobMeta;
// use n2n\persistence\orm\model\EntityModel;

// class AdaptiveActionJobMeta implements ActionJobMeta {
// 	private $decoratedMeta;
// 	private $columnNamePrefix;
// 	private $entityIdColumnName;
// 	private $id;
// 	private $entityId;
// 	private $items;
		
// 	public function __construct(ActionJobMeta $decoratedMeta, $tableNamePrefix, $columnNamePrefix, $idGenerated, 
// 			$idColumnName, $sequenceName, $entityIdColumnName = null, $forMeta = false) {
// 		$this->decoratedMeta = $decoratedMeta;
// 		$this->columnNamePrefix = $columnNamePrefix;
// 		$this->entityIdColumnName = $entityIdColumnName;
		
// 		$decoratedMeta->setIdColumnName($idColumnName);
// 		$decoratedMeta->setIdGenerated($idGenerated);
// 		$decoratedMeta->setSequenceName($sequenceName);
		
// 		foreach ($decoratedMeta->getItems() as $item) {
// 			$tableName = $item->getTableName();
// 			$draftTableName = $tableNamePrefix . $tableName;
// 			$this->items[$tableName] = $item;
// 			$item->setTableName($draftTableName);
			
// 			foreach ($item->getRawValues() as $columnName => $rawValue) {
// 				$entityModel = $item->getEntityModel();
// 				$decoratedMeta->removeRawValue($entityModel, $columnName);
// 				$decoratedMeta->setRawValue($entityModel, $columnNamePrefix . $columnName, 
// 						($forMeta ? $columnName : $rawValue));
// 			}
// 		}
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::getEntityModel()
// 	 */
// 	public function getEntityModel() {
// 		return $this->decoratedMeta->getEntityModel();
// 	}
// 	/**
// 	 * @param string $columnName
// 	 * @param mixed $rawValue
// 	 */
// 	public function setMetaRawValue($columnName, $rawValue) {
// 		if ($columnName == $this->entityIdColumnName) {
// 			$this->entityId = $rawValue;
// 		}
		
// 		$this->decoratedMeta->setRawValue($this->getEntityModel()->getTopEntityModel(), $columnName, $rawValue);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::setRawValue()
// 	 */
// 	public function setRawValue(EntityModel $entityModel, string $columnName, $rawValue) {
// 		$this->decoratedMeta->setRawValue($entityModel, $this->columnNamePrefix . $columnName, $rawValue);
// 	}
	
// 	public function removeRawValue(EntityModel $entityModel, $columnName) {
// 		$this->decoratedMeta->removeRawValue($entityModel, $columnName);	
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::setIdGenerated()
// 	 */
// 	public function setIdGenerated($idGenerated) {
// 		$this->decoratedMeta->setIdGenerated($idGenerated);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::isIdGenerated()
// 	 */
// 	public function isIdGenerated() {
// 		return $this->decoratedMeta->isIdGenerated();
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::setSequenceName()
// 	 */
// 	public function setSequenceName($sequenceName) {
// 		$this->decoratedMeta->setSequenceName($sequenceName);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::getSequenceName()
// 	 */
// 	public function getSequenceName() {
// 		return $this->decoratedMeta->getSequenceName();
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::setIdColumnName()
// 	 */
// 	public function setIdColumnName($idColumnName) {
// 		$this->decoratedMeta->setIdColumnName($idColumnName);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::getIdColumnName()
// 	 */
// 	public function getIdColumnName() {
// 		return $this->decoratedMeta->getIdColumnName();
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::hasId()
// 	 */
// 	public function hasId() {
// 		return $this->decoratedMeta->hasId();	
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::setId()
// 	 */
// 	public function setId($id) {
// 		$this->decoratedMeta->setId($id);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::getId()
// 	 */
// 	public function getId() {
// 		return $this->decoratedMeta->getId();
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::removeId()
// 	 */
// 	public function removeId() {
// 		$this->decoratedMeta->removeId();
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::hasObjectId()
// 	 */
// 	public function hasObjectId() {
// 		return isset($this->entityId);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::getObjectId()
// 	 */
// 	public function getObjectId() {
// 		return $this->entityId;
// 	}
	
// 	public function setEntityId($entityId) {
// 		$this->entityId = $entityId;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::getItems()
// 	 */
// 	public function getItems() {
// 		return $this->items;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\persistence\orm\store\ActionJobMeta::getRawDataMap()
// 	 */
// 	public function getRawDataMap() {
// 		return $this->decoratedMeta->getRawDataMap();
// 	}
// }

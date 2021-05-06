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
namespace n2n\persistence\orm\store\action\meta;

use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\meta\data\PersistStatementBuilder;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\meta\data\QueryPlaceMarker;
use n2n\persistence\PdoStatement;

class ActionMetaItem {
	private $entityModel;
	private $tableName;
	private $idGenerated;
	private $rawValues = array();
	private $dataTypes = array();
	
	public function __construct(EntityModel $entityModel, $idGenerated) {
		$this->entityModel = $entityModel;
		$this->tableName = $entityModel->getTableName();
		$this->idGenerated = $idGenerated;
	}
	
	public function getEntityModel() {
		return $this->entityModel;
	}
	
	public function setIdGenerated($idGenerated) {
		$this->idGenerated = $idGenerated;
	}
	
	public function isIdGenerated() {
		return $this->idGenerated;
	}
		
	public function getTableName() {
		return $this->tableName;
	}
	
	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}
	
	public function isEmpty() {
		return !(boolean) sizeof($this->rawValues);
	}
	
	public function setRawValue($columnName, $rawValue, int $pdoDataType = null) {
		$this->rawValues[$columnName] = $rawValue;
		$this->dataTypes[$columnName] = $pdoDataType;
	}
	
	public function removeRawValue($columnName) {
		unset($this->rawValues[$columnName]);
		unset($this->dataTypes[$columnName]);
	}	
	
	public function getRawValues() {
		return $this->rawValues;
	}
	
	public function apply(PersistStatementBuilder $stmtBuilder) {
		$stmtBuilder->setTable($this->tableName);
		foreach ($this->rawValues as $columnName => $rawValue) {
			$stmtBuilder->addColumn(new QueryColumn($columnName), new QueryPlaceMarker($columnName));
		}
	}
	
	public function bindRawValues(PdoStatement $pdoStatement) {
		foreach ($this->rawValues as $columnName => $rawValue) {
			if (isset($this->dataTypes[$columnName])) {
				$pdoStatement->bindValue($columnName, $rawValue, $this->dataTypes[$columnName]);
			} else {
				$pdoStatement->autoBindValue($columnName, $rawValue);
			}
		}
	}
}

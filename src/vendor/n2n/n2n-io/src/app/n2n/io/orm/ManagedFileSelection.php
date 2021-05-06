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
namespace n2n\io\orm;

use n2n\persistence\meta\data\QueryItem;
use n2n\persistence\PdoStatement;
use n2n\io\managed\FileManager;
use n2n\persistence\orm\query\select\Selection;
use n2n\persistence\orm\query\select\EagerValueBuilder;
use n2n\io\managed\impl\engine\QualifiedNameFormatException;
use n2n\persistence\orm\CorruptedDataException;

class ManagedFileSelection implements Selection {
	private $queryItem;
	private $fileManager;
	private $entityProperty;
	
	private $qualifiedName;

	public function __construct(QueryItem $queryItem, FileManager $fileManager, ManagedFileEntityProperty $entityProperty) {
		$this->queryItem = $queryItem;
		$this->fileManager = $fileManager;
		$this->entityProperty = $entityProperty;
	}
	
	public function getSelectQueryItems() {
		return array($this->queryItem);
	}

	public function bindColumns(PdoStatement $stmt, array $columnAliases) {
		$stmt->shareBindColumn($columnAliases[0], $this->qualifiedName);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\select\Selection::createValueBuilder()
	 */
	public function createValueBuilder() {
		if ($this->qualifiedName === null) {
			return new EagerValueBuilder(null);
		}
		
		$file = null;
		try {
			$file = $this->fileManager->getByQualifiedName($this->qualifiedName);
		} catch (QualifiedNameFormatException $e) {
			throw new CorruptedDataException('Failed to lookup value for ' . $this->entityProperty, 0, 
					new CorruptedDataException('Field \'' . $this->entityProperty->getColumnName()
							. '\' of table \'' . $this->entityProperty->getEntityModel()->getTableName() 
							. '\' contains an invalid value: ' . $this->qualifiedName, 0, $e));	
		}
		
		if ($file !== null) {
			return new EagerValueBuilder($file);
		}
		
		return new EagerValueBuilder(new UnknownFile($this->qualifiedName, get_class($this->fileManager)));
	}
}

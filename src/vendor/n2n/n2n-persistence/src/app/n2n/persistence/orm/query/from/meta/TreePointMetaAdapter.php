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
namespace n2n\persistence\orm\query\from\meta;

use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\orm\query\QueryState;

abstract class TreePointMetaAdapter implements TreePointMeta {
	protected $queryState;
	protected $entityModel;
	protected $idColumnName;
	
	private $metaGenerator;
	
	public function __construct(QueryState $queryState, EntityModel $entityModel) {
		$this->queryState = $queryState;
		$this->entityModel = $entityModel;
		$this->idColumnName = $entityModel->getIdDef()->getEntityProperty()->getColumnName();
	}

	public function setMetaGenerator(MetaGenerator $metaGenerator = null) {
		$this->metaGenerator = $metaGenerator;
	}

	protected function generateTableName(EntityModel $entityModel) {
		if ($this->metaGenerator === null) {
			return $entityModel->getTableName();
		}

		return $this->metaGenerator->generateTableName($entityModel);
	}

	protected function generateColumnName(EntityModel $entityModel, $columnName) {
		if ($this->metaGenerator === null) {
			return $columnName;
		}

		return $this->metaGenerator->generateColumnName($entityModel, $columnName);
	}
	
	public function getMetaColumnAliases() {
		return array();
	}
	
	public function setIdColumnName(string $idColumnname) {	
		$this->idColumnName	= $idColumnname;
	}
	
	public function getIdColumnName() {		
		return $this->idColumnName;
	}
}

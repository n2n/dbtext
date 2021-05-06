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

use n2n\persistence\PdoStatement;
use n2n\persistence\orm\query\select\EagerValueBuilder;

class JoinedDiscriminatorSelection implements DiscriminatorSelection {
	private $idQueryItems = array();
	private $entityModels = array();
	private $values = array();

	public function __construct(array $idQueryItems, array $entityModels) {
		$this->idQueryItems = $idQueryItems;
		$this->entityModels = $entityModels;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\from\meta\DiscriminatorSelection::determineEntityModel()
	*/
	public function determineEntityModel() {
		$identifiedEntityModel = null;
		foreach ($this->entityModels as $key => $entityModel) {
			if (!isset($this->values[$key])) continue;
			$identifiedEntityModel = $entityModel;
		}

		return $identifiedEntityModel;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\select\Selection::getSelectQueryItems()
	*/
	public function getSelectQueryItems() {
		return $this->idQueryItems;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\select\Selection::bindColumns()
	*/
	public function bindColumns(PdoStatement $stmt, array $columnAliases) {
		foreach ($columnAliases as $key => $columnAlias) {
			$this->values[$key] = null;
			$stmt->shareBindColumn($columnAlias, $this->values[$key]);
		}
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\query\select\Selection::createValueBuilder()
	*/
	public function createValueBuilder() {
		if (null !== ($entityModel = $this->determineEntityModel())) {
			return new EagerValueBuilder($entityModel->getClass());
		}

		return new EagerValueBuilder(null);
	}
}

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

use n2n\util\ex\IllegalStateException;

use n2n\persistence\orm\model\EntityModel;

class JoinedTableActionMeta extends ActionMetaAdapter {
	private $items = null;
	
	public function __construct(EntityModel $entityModel) {
		parent::__construct($entityModel);
		 
		$items = array();
		foreach ($entityModel->getAllSuperEntityModels(true) as $className => $entityModel) {
			if ($entityModel->getIdDef()->getEntityProperty()->getEntityModel()->equals($entityModel)) {
				$items[$className] = new ActionMetaItem($entityModel, 
						$entityModel->getIdDef()->isGenerated());
			} else {
				$items[$className] = new ActionMetaItem($entityModel, false);
			}
		}
		$this->items = array_reverse($items);
	}
	
	protected function assignRawValue(EntityModel $entityModel, $columnName, $rawValue, $isId, int $pdoDataType = null) {
		$className = $entityModel->getClass()->getName();
		if (!isset($this->items[$className])) {
			throw IllegalStateException::createDefault();
		}
		
		if (!$isId) {
			$this->items[$className]->setRawValue($columnName, $rawValue, $pdoDataType);
			return;
		}
		
		foreach ($this->items as $item) {
			$item->setRawValue($columnName, $rawValue, $pdoDataType);
		}
	}
	
	protected function unassignRawValue(EntityModel $entityModel, $columnName, $isId) {
		$className = $entityModel->getClass()->getName();
		if (!isset($this->items[$className])) {
			throw IllegalStateException::createDefault();
		}
		
		if (!$isId) {
			$this->items[$className]->removeRawValue($columnName);
			return;
		}
		
		foreach ($this->items as $item) {
			$item->removeRawValue($columnName);
		}
	}
	
	public function getItems() {
		return $this->items;
	}
}
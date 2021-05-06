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
namespace n2n\persistence\orm\store\action;

use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\meta\data\QueryPlaceMarker;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\orm\store\action\meta\ActionMeta;
use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\orm\store\ValueHashCol;

class RemoveActionImpl extends EntityActionAdapter implements RemoveAction {
	private $meta;
	private $oldValueHashCol;
	/**
	 * @param ActionQueue $actionQueue
	 * @param ActionMeta $meta
	 */
	public function __construct(ActionQueue $actionQueue, EntityModel $entityModel, $id, $entity, 
			ActionMeta $meta, ValueHashCol $oldValueHashCol) {
		parent::__construct($actionQueue, $entityModel, $id, $entity);
		$this->meta = $meta;
		$this->oldValueHashCol = $oldValueHashCol;
	}
	
	/**
	 * @return \n2n\persistence\orm\store\ValueHashCol
	 */
	public function getOldValueHashCol() {
		return $this->oldValueHashCol;
	}
	
	protected function exec() {
		$pdo = $this->actionQueue->getEntityManager()->getPdo();
		
		foreach ($this->meta->getItems() as $item) {
			$deleteBuilder = $pdo->getMetaData()->createDeleteStatementBuilder();
			$deleteBuilder->setTable($item->getTableName());
			$deleteBuilder->getWhereComparator()->match(new QueryColumn($this->meta->getIdColumnName()),
					QueryComparator::OPERATOR_EQUAL, new QueryPlaceMarker());

			$stmt = $pdo->prepare($deleteBuilder->toSqlString());
			$stmt->execute(array($this->meta->getIdRawValue()));
		}
	}
}

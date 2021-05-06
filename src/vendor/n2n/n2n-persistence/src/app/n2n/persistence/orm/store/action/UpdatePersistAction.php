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

use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\meta\data\QueryPlaceMarker;
use n2n\persistence\meta\data\QueryComparator;

class UpdatePersistAction extends PersistActionAdapter {

	public function isNew() {
		return false;
	}
	
	protected function exec() {
		$dbh = $this->actionQueue->getEntityManager()->getPdo();
		$metaData = $dbh->getMetaData();
				
		$idColumnName = $this->meta->getIdColumnName();
		
		foreach ($this->meta->getItems() as $item) {
			if ($item->isEmpty()) continue;
				
			$updateBuilder = $metaData->createUpdateStatementBuilder();
			$item->apply($updateBuilder);
			
// 			$rawValues = array();
// 			foreach ($item->getRawValues() as $columnName => $rawValue) {
// 				$updateBuilder->addColumn(new QueryColumn($columnName), new QueryPlaceMarker());
// 				$rawValues[] = $rawValue;
// 			}
			
// 			if (empty($rawValues)) continue;
				
			$updateBuilder->getWhereComparator()->match(new QueryColumn($idColumnName),
					QueryComparator::OPERATOR_EQUAL, new QueryPlaceMarker($idColumnName));
				
			$stmt = $dbh->prepare($updateBuilder->toSqlString());
			$item->bindRawValues($stmt);
			$stmt->autoBindValue($idColumnName, $this->meta->getIdRawValue());
			
			$stmt->execute();
		}
	}
}

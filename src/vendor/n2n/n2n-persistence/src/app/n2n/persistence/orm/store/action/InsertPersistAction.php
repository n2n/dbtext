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

use n2n\util\ex\IllegalStateException;
use n2n\persistence\Pdo;

class InsertPersistAction extends PersistActionAdapter {

	public function isNew() {
		return true;
	}
	
	protected function exec() {
		$pdo = $this->actionQueue->getEntityManager()->getPdo();
		$metaData = $pdo->getMetaData();
		$dialect = $metaData->getDialect();
	
// 		$idDef = $this->entityInfo->getEntityModel()->getIdDef();
// 		$idProperty = $idDef->getEntityProperty();

		if ($this->meta->isIdGenerated() && !$dialect->isLastInsertIdSupported()) {
			$this->applyIdRawValue($dialect->generateSequenceValue($pdo, $this->meta->getSequenceName()), $pdo);
		}

		$idColumnName = $this->meta->getIdColumnName();
	
		foreach ($this->meta->getItems() as $item) {
			$insertBuilder = $metaData->createInsertStatementBuilder();
			$item->apply($insertBuilder);
			
// 			$insertBuilder->setTable($item->getTableName());
// 			$rawValues = array();
// 			foreach ($item->getRawValues() as $columnName => $rawValue) {
// 				if ($columnName == $idColumnName && $item->isIdGenerated() 
// 						&& $dialect->isLastInsertIdSupported()) {
// 					continue;
// 				}
// 				$insertBuilder->addColumn(new QueryColumn($columnName), new QueryPlaceMarker());
// 				$rawValues[] = $rawValue;
// 			}
			
			$stmt = $pdo->prepare($insertBuilder->toSqlString());
			$item->bindRawValues($stmt);
			$stmt->execute();
	
			if ($item->isIdGenerated() && $dialect->isLastInsertIdSupported()) {
				$this->applyIdRawValue($pdo->lastInsertId($this->meta->getSequenceName()), $pdo);
			}
		}
	}
	
	private function applyIdRawValue($idRawValue, Pdo $pdo) {
		$this->meta->setIdRawValue($idRawValue, true);
		$this->id = $this->entityModel->getIdDef()->getEntityProperty()
				->parseValue($this->meta->getEntityIdRawValue(), $pdo);
		IllegalStateException::assertTrue($this->id !== null && $idRawValue !== null);
	}	
}

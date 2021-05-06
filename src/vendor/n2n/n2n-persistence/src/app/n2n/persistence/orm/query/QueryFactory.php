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
namespace n2n\persistence\orm\query;

use n2n\persistence\orm\query\select\Selection;
use n2n\persistence\PdoStatement;

class QueryFactory {

	public static function create(QueryState $queryState, QueryModel $queryModel) {
		$pdo = $queryState->getPdo();
		
		$columnBindJobs = array();
		$resultSelections = array();
		$hiddenSelections = array();
		
		$queryItemSelect = $queryModel->getQueryItemSelect();
		
		foreach ($queryModel->getNamedSelectQueryPoints() as $alias => $queryPoint) {
			$selection = $queryPoint->requestSelection();
			$columnBindJobs[] = self::createBindColumnJob($queryItemSelect, $selection);
			$resultSelections[$alias] = $selection;
		}
		
		foreach ($queryModel->getUnnamedSelectQueryPoints() as $queryPoint) {
			$selection = $queryPoint->requestSelection();
			$columnBindJobs[] = self::createBindColumnJob($queryItemSelect, $selection);
			$resultSelections[] = $selection;
		}
		
		foreach ($queryModel->getHiddenSelectQueryPoints() as $queryPoint) {
			$selection = $queryPoint->requestSelection();
			$columnBindJobs[] = self::createBindColumnJob($queryItemSelect, $selection);
			$hiddenSelections[] = $selection;
		}
		
		$selectBuilder = $pdo->getMetaData()->createSelectStatementBuilder();
		
		$queryModel->apply($selectBuilder);
		 
		$stmt = $pdo->prepare($selectBuilder->toSqlString());
		
		foreach ($columnBindJobs as $columnBindJob) {
			$columnBindJob->bindColumns($stmt);
		}
		
		foreach ($queryState->getPlaceholderValues() as $name => $value) {
			$stmt->autoBindValue($name, $value);
		}
		
		return new Query($queryState->getEntityManager()->getLoadingQueue(), $stmt, $queryState->getPlaceholders(),
				$resultSelections, $hiddenSelections);
	}
	
// 	public static function createQueryFromState(QueryState $queryState, array $resultSelections, array $hiddenSelections) {
// 		$queryItemSelect = $queryModel->getQueryItemSelect();
// 		$columnBindJobs = array();
		
// 		foreach ($resultSelections as $resultSelection) {
// 			$columnBindJobs[] = self::createBindColumnJob($queryItemSelect, $selection);
// 		}
		
// 		foreach ($hiddenSelections as $hiddenSelection) {
// 			$columnBindJobs[] = self::createBindColumnJob($queryItemSelect, $selection);
// 		}

// 		$selectBuilder = $pdo->getMetaData()->createSelectStatementBuilder();
		
// 		$stmt = $pdo->prepare($selectBuilder->toSqlString());
		
// 		foreach ($columnBindJobs as $columnBindJob) {
// 			$columnBindJob->bindColumns($stmt);
// 		}
		
// 		foreach ($queryState->getPlaceholderValues() as $name => $value) {
// 			$stmt->bindValue($name, $value);
// 		}
		
// 		return new Query($stmt, $queryState->getPlaceholders(),
// 				$resultSelections, $hiddenSelections);
// 	}
	
	public static function createBindColumnJob(QueryItemSelect $queryItemSelect, Selection $selection) {
		$columnAliases = array();
		foreach ($selection->getSelectQueryItems() as $key => $queryItem) {
			$columnAliases[$key] = $queryItemSelect->selectQueryItem($queryItem);
		}
		return new BindColumnJob($selection, $columnAliases);
	}
}

class BindColumnJob {
	private $selection;
	private $columnAliases;
	
	public function __construct(Selection $selection, array $columnAliases) {
		$this->selection = $selection;
		$this->columnAliases = $columnAliases;
	}
	
	public function bindColumns(PdoStatement $stmt) {
		$this->selection->bindColumns($stmt, $this->columnAliases);
	}
}

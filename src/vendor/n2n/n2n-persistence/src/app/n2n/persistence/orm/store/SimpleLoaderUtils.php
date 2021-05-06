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
namespace n2n\persistence\orm\store;

use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\orm\query\QueryState;
use n2n\persistence\orm\query\select\Selection;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\query\Query;
use n2n\persistence\orm\query\from\Tree;
use n2n\persistence\orm\query\QueryFactory;
use n2n\persistence\orm\query\QueryItemSelect;

class SimpleLoaderUtils {
	private $em;
	public $entityModel;
	
	public $queryState;
	public $tree;
	public $metaTreePoint;
	
	public $selection;
	private $bindColumnJob;
	public $selectBuilder;
	
	public function __construct(EntityManager $em, EntityModel $entityModel) {
		$this->em = $em;
		$this->entityModel = $entityModel;
	}
	
	public function initialize() {
		$this->queryState = new QueryState($this->em);
		$this->tree = new Tree($this->queryState);
		$this->metaTreePoint = $this->tree->createBaseTreePoint($this->entityModel, 'e');
	}
	
	public function setSelection(Selection $selection) {
		$this->selection = $selection;
	}
	
	public function build() {
		IllegalStateException::assertTrue($this->selection !== null && $this->tree !== null);
		$this->selectBuilder = $this->em->getPdo()->getMetaData()->createSelectStatementBuilder();
		
		$queryItemSelect = new QueryItemSelect($this->queryState);
		$this->bindColumnJob = QueryFactory::createBindColumnJob($queryItemSelect, $this->selection);
		$queryItemSelect->apply($this->selectBuilder);
		$this->tree->apply($this->selectBuilder);
		return $this->selectBuilder;
	}
	
	public function createQuery() {
		IllegalStateException::assertTrue($this->selectBuilder !== null);
		
		$stmt = $this->em->getPdo()->prepare($this->selectBuilder->toSqlString());
		$this->bindColumnJob->bindColumns($stmt);
		
		foreach ($this->queryState->getPlaceholderValues() as $name => $value) {
			$stmt->bindValue($name, $value);
		}
		
		return new Query($this->em->getLoadingQueue(), $stmt, $this->queryState->getPlaceholders(), array($this->selection), array());
	}
}

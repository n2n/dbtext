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

use n2n\persistence\orm\store\PersistenceContext;
use n2n\persistence\Pdo;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\persistence\orm\EntityManager;
use n2n\util\type\ArgUtils;
use n2n\util\StringUtils;

class QueryState {
	const TABLE_ALIAS_PREFIX = 'tbl';
	const COLUMN_ALIAS_PREFIX = 'col';
	const PLACE_HOLDER_PREFIX = 'ph';

	private $em;
	
	private $tableAliasIndex = 0;
	private $columnAliasIndex = 0;

	private $placeholderValues = array();
	private $placeholders = array();
	/**
	 * @param Pdo $pdo
	 * @param EntityModelManager $entityModelManager
	 * @param PersistenceContext $persistenceContext
	 */
	public function __construct(EntityManager $em) {
		$this->em = $em;
	}
	/**
	 * @return EntityManager
	 */
	public function getEntityManager() {
		return $this->em;
	}
	/**
	 * @return Pdo
	 */
	public function getPdo() {
		return $this->em->getPdo();
	}
	/**
	 * @return EntityModelManager
	 */
	public function getEntityModelManager() {
		return $this->em->getEntityModelManager();	
	}
	/**
	 * @return PersistenceContext
	 */
	public function getPersistenceContext() {
		return $this->em->getPersistenceContext();
	}
	/**
	 * @param string $tableName
	 * @return string
	 */
	public function createTableAlias(string $tableName = null) {
		if ($tableName !== null) {
			$tableName = mb_strtolower(StringUtils::buildAcronym($tableName));
		}
		
		return self::TABLE_ALIAS_PREFIX . $this->tableAliasIndex++ . '_' . $tableName;
	}
	/**
	 * @param string $columnName
	 * @return string
	 */
	public function createColumnAlias($columnName = null) {
		return self::COLUMN_ALIAS_PREFIX . $this->columnAliasIndex++ . '_' . $columnName;
	}
	/**
	 * @param mixed $value scalar
	 * @return string
	 */
	public function registerPlaceholderValue($value) {
		ArgUtils::valType($value, 'scalar', true);
		$placholerName = self::PLACE_HOLDER_PREFIX . sizeof($this->placeholderValues);
		$this->placeholderValues[$placholerName] = $value;
		return $placholerName;
	}
	/**
	 * @return array
	 */
	public function getPlaceholderValues() {		
		return $this->placeholderValues;
	}
	
	public function registerPlaceholder($name, Placeholder $placeholder) {
		return $this->placeholders[$name] = $placeholder;
	}
	
	public function getPlaceholders() {
		return $this->placeholders;
	}
}

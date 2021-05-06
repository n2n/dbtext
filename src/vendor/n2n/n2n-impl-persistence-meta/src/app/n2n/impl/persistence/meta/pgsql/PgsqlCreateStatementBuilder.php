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
namespace n2n\impl\persistence\meta\pgsql;

use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\MetaEntity;
use n2n\persistence\meta\structure\View;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\meta\structure\EnumColumn;
use n2n\util\type\CastUtils;
use n2n\persistence\meta\structure\Table;

class PgsqlCreateStatementBuilder {
	private $pdo;
	private $metaEntity;

	private $sqlStatements = array();
	private $enumTypeSqlStatements = array();

	public function __construct(Pdo $dbh) {
		$this->pdo = $dbh;
	}

	public function setMetaEntity(MetaEntity $metaEntity) {
		$this->metaEntity = $metaEntity;
	}

	public function getMetaEntity() {
		return $this->metaEntity;
	}

	public function toSqlString($replace = false, $formatted = false) {
		$sqlString = '';
		foreach ($this->createSqlStatements($replace, $formatted) as $sql) {
			$sqlString .= $sql;

			if ($formatted) {
				$sqlString .= PHP_EOL;
			}

		}
		return $sqlString;
	}

	public function executeSqlStatements() {
		foreach ($this->createSqlStatements() as $sqlStatement) {
			$this->pdo->exec($sqlStatement);
		}
	}

	public function createSqlStatements($replace = false, $formatted = false) {
		$this->sqlStatements = array();
		$this->enumTypeSqlStatements = array();

		$metaEntity = $this->getMetaEntity();
		$quotedMetaEntityName = $this->pdo->quoteField($metaEntity->getName());

		if ($metaEntity instanceof View) {
			if ($replace) {
				$this->sqlStatements[] = 'DROP VIEW IF EXISTS ' . $quotedMetaEntityName . '; ';
			}
				
			$this->sqlStatements[] = 'CREATE VIEW ' . $quotedMetaEntityName . ' AS '
					. $metaEntity->getQuery();
						
			return $this->sqlStatements;
		}

		if ($metaEntity instanceof Table) {
			if ($replace) {
				$this->sqlStatements[] = 'DROP TABLE IF EXISTS ' . $quotedMetaEntityName . '; ';
			}
				
			$this->sqlStatements[] = 'CREATE TABLE ' . $quotedMetaEntityName . ' ('
					. implode(', ', $this->buildColumnSqlFragments($formatted)) . ');';
						
			$this->buildIndexesSql($replace, $formatted);
			
			return array_merge($this->enumTypeSqlStatements, $this->sqlStatements);
		}

		throw new IllegalStateException('Inavlid meta entity given. Given type is "' . get_class($this->metaEntity));
	}

	private function buildColumnSqlFragments($formatted = false) {
		$metaEntity = $this->getMetaEntity();
		CastUtils::assertTrue($metaEntity instanceof Table);
		
		$columns = $metaEntity->getColumns();
		$columnArray = array();
		$columnStatementFragmentBuilder = new PgsqlColumnStatementFragmentBuilder($this->pdo);
		$enumStatementBuilder = new PgsqlEnumStatementBuilder($this->pdo);

		foreach ($columns as $column) {
			if ($column instanceof EnumColumn) {
				if ($enumStatementBuilder->containsEnumType($column)) {
					$this->enumTypeSqlStatements[] = $enumStatementBuilder->buildDropEnumTypeStatement($column);
				}

				$this->enumTypeSqlStatements[] = $enumStatementBuilder->buildCreateEnumTypeStatement($column);
			}
				
			$columnArray[] = ($formatted ? PHP_EOL . "\t" : '') . $columnStatementFragmentBuilder->generateColumnFragment($column);
		}

		return $columnArray;
	}

	private function buildIndexesSql(bool $replace = false, bool $formatted = false) {
		$metaEntity = $this->getMetaEntity();
		CastUtils::assertTrue($metaEntity instanceof Table);
		
		if (count($indexes = $metaEntity->getIndexes()) > 0) {
			$indexStatementBuilder = new PgsqlIndexStatementBuilder($this->pdo);
			foreach ($indexes as $index) {
				if ($replace) {
					$this->sqlStatements[] = $indexStatementBuilder->buildDropStatement($index);
				}

				$this->sqlStatements[] = $indexStatementBuilder->buildCreateStatement($index);
			}
		}
	}
}
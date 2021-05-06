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
namespace n2n\impl\persistence\meta\mssql;

use n2n\persistence\meta\data\QueryTable;
use n2n\persistence\meta\structure\common\BackuperAdapter;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\data\QueryConstant;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\Pdo;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\CastUtils;

class MssqlBackuper extends BackuperAdapter {

	public function start() {
		if (!(($this->getOutputStream()) || !($this->getOutputStream()->isOpen()) )) {
			throw new IllegalStateException('Outputstream not set');
		}

		$this->getOutputStream()->write($this->getHeader());

		$metaEntities = $this->getMetaEntities();
		if (is_null($metaEntities)) {
			$metaEntities = $this->database->getMetaEntities();
		}

		$createStatementBuilder = new MssqlCreateStatementBuilder($this->dbh);
		
		$dialect = $this->dbh->getMetaData()->getDialect();
		CastUtils::assertTrue($dialect instanceof MssqlDialect);
		
		foreach ($metaEntities as $metaEntity) {
				
			if (is_scalar($metaEntity)) {
				$metaEntity = $this->database->getMetaEntityByName($metaEntity);
			}

			//first the structure
			if ($this->isBackupStructureEnabled()) {
				$createStatementBuilder->setMetaEntity($metaEntity);
				$this->getOutputStream()->write($createStatementBuilder->toSqlString($this->isReplaceTableEnabled(), true) . PHP_EOL) ;
			}
				
			//then the data
			if ($this->isBackupDataEnabled() && ($metaEntity instanceof Table)) {
				$hasIdentifierGenerator = false;
// 				$columnStatementStringBuilder = new MssqlColumnStatementStringBuilder($this->dbh);
					//remove Identity for insert
				foreach ($metaEntity->getColumns() as $column) {
					//@todo extend DialectInterface byisColumnIdentifierGenerator
					
					
					if ($dialect->isColumnIdentifierGenerator($column)) {
						$hasIdentifierGenerator = true;
						break;
					}
				}
				
				$selectStatementBuilder = $this->dialect->createSelectStatementBuilder($this->dbh);
				$selectStatementBuilder->addFrom(new QueryTable($metaEntity->getName()), null);
				$sql = $selectStatementBuilder->toSqlString();
				$statement = $this->dbh->prepare($sql);
				$statement->execute();
				$results = $statement->fetchAll(Pdo::FETCH_ASSOC);
				if ($hasIdentifierGenerator && count($results) > 0) {
					$this->getOutputStream()->write('SET IDENTITY_INSERT ' . $this->dbh->quoteField($metaEntity->getName()) . ' ON;');
				}
				foreach($results as $row) {
					$insertStatementBuilder = $this->dialect->createInsertStatementBuilder($this->dbh);
					$insertStatementBuilder->setTable($metaEntity->getName());
					foreach ($row as $columnName => $value) {
						$column = $metaEntity->getColumnByName($columnName);
						if ($column->isValueGenerated() && !$dialect->isColumnIdentifierGenerator($column)) continue;

						$insertStatementBuilder->addColumn(new QueryColumn($columnName), new QueryConstant($value));
					}
					$this->getOutputStream()->write($insertStatementBuilder->toSqlString() . ";" . PHP_EOL) ;
				}
				if (count($results) > 0) {
					if ($hasIdentifierGenerator) {
						$this->getOutputStream()->write('SET IDENTITY_INSERT ' . $this->dbh->quoteField($metaEntity->getName()) . ' OFF;');
					}
					$this->getOutputStream()->write(PHP_EOL);
				}
			}
		}
		$this->getOutputStream()->flush();
	}
}

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
namespace n2n\impl\persistence\meta\mysql\management;

use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\View;
use n2n\impl\persistence\meta\mysql\MysqlIndexStatementStringBuilder;
use n2n\impl\persistence\meta\mysql\MysqlColumnStatementStringBuilder;
use n2n\impl\persistence\meta\mysql\MysqlMetaEntityBuilder;
use n2n\persistence\Pdo;
use n2n\util\type\CastUtils;
use n2n\persistence\meta\structure\common\AlterMetaEntityRequestAdapter;

class MysqlAlterMetaEntityRequest extends AlterMetaEntityRequestAdapter  {
	
	public function execute(Pdo $dbh) {
		$columnStatementStringBuilder = new MysqlColumnStatementStringBuilder($dbh);
		$indexStatementStringBuilder = new MysqlIndexStatementStringBuilder($dbh);
		$metaEntity = $this->getMetaEntity();
		$metaEntityBuilder = new MysqlMetaEntityBuilder($dbh);
		
		if ($metaEntity instanceof View) {
			$dbh->exec('ALTER VIEW ' . $dbh->quoteField($metaEntity->getName()) . ' AS ' . $metaEntity->getQuery());
			return;
		}
		
		if ($metaEntity instanceof Table) {
			//columns to Add
			$columns = $metaEntity->getColumns();
			$persistedTable = $metaEntityBuilder->createMetaEntityFromDatabase(
					$dbh->getMetaData()->getMetaManager()->createDatabase(), $metaEntity->getName());
			CastUtils::assertTrue($persistedTable instanceof Table);
			$persistedColumns = $persistedTable->getColumns();

			foreach ($columns as $column) {
				if (!$persistedTable->containsColumnName($column->getName())) {
					$dbh->exec('ALTER TABLE ' . $dbh->quoteField($this->getMetaEntity()->getName()) . ' ADD COLUMN ' 
							. $columnStatementStringBuilder->generateStatementString($column));
				} elseif (!$column->equals($persistedTable->getColumnByName($column->getName()))) {
					$dbh->exec('ALTER TABLE ' . $dbh->quoteField($this->getMetaEntity()->getName()) 
							. ' CHANGE COLUMN ' . $dbh->quoteField($column->getName()) 
							. ' ' . $columnStatementStringBuilder->generateStatementString($column)); 
				}
			}
			
			foreach ($persistedColumns as $persistedColumn) {
				if ($metaEntity->containsColumnName($persistedColumn->getName())) continue;
				
				$dbh->exec('ALTER TABLE ' . $dbh->quoteField($this->getMetaEntity()->getName()) 
						. ' DROP COLUMN ' . $dbh->quoteField($persistedColumn->getName()));
			}
			
			foreach ($persistedTable->getIndexes() as $persistedIndex) {
				if ($metaEntity->containsIndexName($persistedIndex->getName()) 
						&& $persistedIndex->equals($metaEntity->getIndexByName($persistedIndex->getName()))) continue;
				$dbh->exec($indexStatementStringBuilder->generateDropStatementString($persistedIndex));
			}
			
			foreach ($metaEntity->getIndexes() as $index) {
				if ($persistedTable->containsIndexName($index->getName()) 
						&& $persistedTable->getIndexByName($index->getName())->equals($index)) continue;
				
				$dbh->exec('ALTER TABLE ' . $dbh->quoteField($this->getMetaEntity()->getName()) . ' ADD ' 
						. $indexStatementStringBuilder->generateCreateStatementString($index));
			}
		}
	}
}

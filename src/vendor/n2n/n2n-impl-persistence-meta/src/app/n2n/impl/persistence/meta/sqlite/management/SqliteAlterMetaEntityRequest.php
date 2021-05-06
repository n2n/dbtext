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
namespace n2n\impl\persistence\meta\sqlite\management;

use n2n\persistence\meta\structure\IndexType;
use n2n\impl\persistence\meta\sqlite\SqliteCreateStatementBuilder;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\View;
use n2n\impl\persistence\meta\sqlite\SqliteIndexStatementStringBuilder;
use n2n\impl\persistence\meta\sqlite\SqliteMetaEntityBuilder;
use n2n\persistence\Pdo;
use n2n\util\type\CastUtils;
use n2n\persistence\meta\structure\common\AlterMetaEntityRequestAdapter;

class SqliteAlterMetaEntityRequest extends AlterMetaEntityRequestAdapter {
	
	public function execute(Pdo $dbh) {
// 		$columnStatementStringBuilder = new SqliteColumnStatementStringBuilder($dbh);
		$indexStatementStringBuilder = new SqliteIndexStatementStringBuilder($dbh);
		$metaEntityBuilder = new SqliteMetaEntityBuilder($dbh, $this->getMetaEntity()->getDatabase());
		
		$metaEntity = $this->getMetaEntity();
		if ($metaEntity instanceof View) {
			$dbh->exec('DROP VIEW ' . $dbh->quoteField($metaEntity->getName()));
			$dbh->exec('CREATE VIEW ' . $dbh->quoteField($metaEntity->getName()) . ' AS ' . $metaEntity->getQuery());
			return;
		}				
		
		if ($metaEntity instanceof Table) {
			//columns to Add
			$columns = $metaEntity->getColumns();
			$persistedTable = $metaEntityBuilder->createMetaEntityFromDatabase($dbh->getMetaData()->getMetaManager()->createDatabase(),
					$metaEntity->getName());
			CastUtils::assertTrue($persistedTable instanceof Table);
			
			$persistedColumns = $persistedTable->getColumns();
			$createStatementBuilder = new SqliteCreateStatementBuilder($dbh);
			$copyColumns = array();
			$tempTableName =  'temp_' . $this->getMetaEntity()->getName();
			$dbh->exec('DROP TABLE IF EXISTS ' . $tempTableName);
			
			//Drop old indexes that we don't have duplicate Index Names
			foreach ($persistedTable->getIndexes() as $index) {
				if ($index->getType() == IndexType::PRIMARY || $index->getType() == IndexType::FOREIGN) continue;

				$dbh->exec($indexStatementStringBuilder->generateDropStatementString($index));
			}
			
			$dbh->exec('ALTER TABLE ' . $metaEntity->getName() . ' RENAME TO ' . $tempTableName );
			
			$createStatementBuilder->setMetaEntity($metaEntity);
			$createStatementBuilder->createMetaEntity();
			
			foreach ($columns as $column) {
				if (!$persistedTable->containsColumnName($column->getName())) continue;
				
				$copyColumns[] = $dbh->quoteField($column->getName()); 
			}
			
			$dbh->exec('INSERT INTO ' . $metaEntity->getName() . '('. implode(',',$copyColumns) . ') SELECT ' 
					. implode(',', $copyColumns) . 'FROM ' . $tempTableName);
			
			$dbh->exec('DROP TABLE ' . $tempTableName);
			//Indexes are already created in the create statement
		}
	}
}

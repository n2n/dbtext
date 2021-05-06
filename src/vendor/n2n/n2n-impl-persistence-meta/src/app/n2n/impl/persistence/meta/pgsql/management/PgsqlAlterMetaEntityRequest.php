<?php
namespace n2n\impl\persistence\meta\pgsql\management;

use n2n\persistence\Pdo;
use n2n\impl\persistence\meta\pgsql\PgsqlIndexStatementBuilder;
use n2n\persistence\meta\structure\Table;
use n2n\impl\persistence\meta\pgsql\PgsqlMetaEntityBuilder;
use n2n\persistence\meta\structure\View;
use n2n\impl\persistence\meta\pgsql\PgsqlColumnStatementFragmentBuilder;
use n2n\persistence\meta\structure\common\AlterMetaEntityRequestAdapter;

class PgsqlAlterMetaEntityRequest extends AlterMetaEntityRequestAdapter {

	public function execute(Pdo $dbh) {
		$metaEntity = $this->getMetaEntity();
		$quotedEntityName = $dbh->quoteField($metaEntity->getName());
		
		if ($metaEntity instanceof View) {
			$dbh->exec('DROP VIEW ' . $quotedEntityName);
			$dbh->exec('CREATE VIEW ' . $quotedEntityName . ' AS ' . $dbh->quote($metaEntity->getQuery()));
			return;
		}

		$database = $metaEntity->getDataBase();
		$columnStatementStringBuilder = new PgsqlColumnStatementFragmentBuilder($dbh);
		$indexStatementStringBuilder = new PgsqlIndexStatementBuilder($dbh);
		$metaEntityBuilder = new PgsqlMetaEntityBuilder($dbh);
		
		if ($metaEntity instanceof Table) {
			//columns to Add
			$metaEntityBuilder = new PgsqlMetaEntityBuilder($dbh, $database);
			
			//columns to Add
			$columns = $metaEntity->getColumns();
			$persistedTable = $metaEntityBuilder->createMetaEntityFromDatabase(
					$dbh->getMetaData()->getMetaManager()->createDatabase(), $this->getMetaEntity()->getName());
			$persistedColumns = $persistedTable->getColumns();
			$sql = '';
			
			foreach ($columns as $column) {
				if (!$persistedTable->containsColumnName($column->getName())) {
					$sql .= $columnStatementStringBuilder->buildAddColumnStatement($column);
				} elseif (!$column->equals($persistedTable->getColumnByName($column->getName()))) {
					$sql .= $columnStatementStringBuilder->buildAlterColumnStatement($column, 
							$persistedTable->getColumnByName($column->getName()));
				}
			}
				
			foreach ($persistedColumns as $persistedColumn) {
				if ($metaEntity->containsColumnName($persistedColumn->getName())) continue;
				
				$sql .= $columnStatementStringBuilder->buildDropColumnStatement($persistedColumn);
			}

			foreach ($persistedTable->getIndexes() as $persistedIndex) {
				if ($metaEntity->containsIndexName($persistedIndex->getName())
						&& $persistedIndex->equals($metaEntity->getIndexByName($persistedIndex->getName()))) continue;
				$persistedTable->removeIndexByName($persistedIndex->getName());
				$sql .= $indexStatementStringBuilder->buildDropStatement($persistedIndex);
			}
			
			foreach ($metaEntity->getIndexes() as $index) {
				if ($persistedTable->containsIndexName($index->getName())
						&& $persistedTable->getIndexByName($index->getName())->equals($index)) continue;
				
				$sql .=  $indexStatementStringBuilder->buildCreateStatement($index);
			}
			
			$dbh->exec($sql);
		}
	}
}
<?php
namespace n2n\impl\persistence\meta\pgsql;

use n2n\persistence\meta\structure\Index;

use n2n\persistence\meta\structure\IndexType;

use n2n\persistence\Pdo;

class PgsqlIndexStatementBuilder {
	
	/**
	 * @var Pdo
	 */
	private $pdo;
	
	public function __construct(Pdo $pdo) {
		$this->pdo = $pdo;
	}
	
	public function buildDropStatement(Index $index) {
		$quotedTableName = $this->pdo->quoteField($index->getTable()->getName());
		$quotedIndexName = $this->pdo->quoteField($index->getName());
		
		switch ($index->getType()) {
			case IndexType::PRIMARY:
			case IndexType::FOREIGN:
				return ' ALTER TABLE ' . $quotedTableName . ' DROP CONSTRAINT ' . $quotedIndexName . ';';
			default:
				return ' DROP INDEX ' . $quotedIndexName . ';';
		}
	}
	
	public function buildCreateStatement(Index $index) {
		$quotedTableName = $this->pdo->quoteField($index->getTable()->getName());
		$quotedIndexName = $this->pdo->quoteField($index->getName());
		
		if ($index->getType() === IndexType::PRIMARY) {
			return ' ALTER TABLE ' . $quotedTableName . 'ADD CONSTRAINT ' . $quotedIndexName . ' PRIMARY KEY ' 
					. $this->buildColumnsFragment($index->getColumns()) . ';';
		}
		
		
		if ($index->getType() === IndexType::FOREIGN) {
			return ' ALTER TABLE ' . $quotedTableName . 'ADD CONSTRAINT ' . $quotedIndexName . ' FOREIGN KEY ' 
					. $this->buildColumnsFragment($index->getColumns()) . ' REFERENCES ' . $this->pdo->quoteField($index->getRefTable()->getName())
					. ' ' . $this->buildColumnsFragment($index->getRefColumns()) . ';';
		}
		
		
		return ' CREATE ' . ($index->getType() === IndexType::UNIQUE ?  'UNIQUE ' : '') 
				. 'INDEX ' . $quotedIndexName . ' ON ' . $quotedTableName . $this->buildColumnsFragment($index->getColumns()) . ';';
	}
	
	private function buildColumnsFragment(array $columns) {
		$s =  ' (';
		
		$first = true;
		foreach ($columns as $column) {
			if (!$first) {
				$s .= ', ';
			} else {
				$first = false;
			}
			$s .= $this->pdo->quoteField($column->getName());
		}
		
		return 	$s . ')';
	}
}
<?php
namespace n2n\persistence\meta\structure\common;

use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\meta\structure\Column;

class ForeignIndex extends IndexAdapter {
	
	private $refColumns = [];
	private $refTable = null;
	
	public function __construct(Table $table, $name, array $columns, 
			?Table $refTable, ?array $refColumns) {
		parent::__construct($table, $name, IndexType::FOREIGN, $columns);
		
		$this->refTable = $refTable;
		foreach ((array) $refColumns as $refColumn) {
			$this->addRefColumn($refColumn);
		}
	}
	
	public function getRefColumns(): array {
		return $this->refColumns;
	}
	
	public function getRefTable(): ?Table {
		return $this->refTable;
	}
	
	public function addRefColumn(Column $column) {
		$refTable = $column->getTable();
		
		$this->refColumns[$column->getName()] = $column;
	}
	
	public static function createFromColumnNames(Table $table, string $name, array $columnNames,
			Table $refTable, array $refColumnNames) {
		$columns = [];
		foreach ($columnNames as $columnName) {
			$columns[] = $table->getColumnByName($columnName);
		}
		
		$refColumns = [];
		foreach ($refColumnNames as $refColumnName) {
			$refColumns[] = $refTable->getColumnByName($refColumnName);
		}
		
		return new ForeignIndex($table, $name, $columns, $refTable, $refColumns);
	}
}
<?php
namespace n2n\impl\persistence\meta\pgsql;

use n2n\persistence\meta\structure\common\CommonEnumColumn;
use n2n\persistence\Pdo;

class PgsqlEnumStatementBuilder {

	private $pdo;
	
	public function __construct(Pdo $pdo) {
		$this->pdo = $pdo;
	}
	
	public function buildCreateEnumTypeStatement(CommonEnumColumn $column) {
		return 'CREATE TYPE ' . $this->pdo->quoteField(self::buildEnumColumnName($column)) 
				. ' AS ENUM (\'' . implode('\',\'', $column->getValues()) . '\');';
	}
	
	public function buildDropEnumTypeStatement(CommonEnumColumn $column) {
		return 'DROP TYPE ' . $this->pdo->quoteField(self::buildEnumColumnName($column));
	}

	public function containsEnumType(CommonEnumColumn $column) {
		$sql = 'SELECT n.nspname AS enum_schema,
					t.typname AS enum_name,
					e.enumlabel AS enum_value
				FROM pg_type t
					JOIN pg_enum e ON t.oid = e.enumtypid
					JOIN pg_catalog.pg_namespace n ON n.oid = t.typnamespace
				WHERE t.typname = ?
				LIMIT 1;';
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(array(self::buildEnumColumnName($column)));
	
		return count($stmt->fetchAll(Pdo::FETCH_ASSOC)) > 0;
	}
	
	public function buildEnumColumnName(CommonEnumColumn $column) {
		return 'enum_' . $column->getTable()->getName() . '_' . $column->getName();
	}
}
<?php
namespace n2n\impl\persistence\meta\pgsql;

use n2n\persistence\meta\structure\Size;
use n2n\persistence\meta\structure\FixedPointColumn;
use n2n\persistence\meta\structure\UnavailableTypeException;
use n2n\persistence\Pdo;
use n2n\persistence\meta\structure\TextColumn;
use n2n\persistence\meta\structure\StringColumn;
use n2n\persistence\meta\structure\FloatingPointColumn;
use n2n\persistence\meta\structure\DateTimeColumn;
use n2n\persistence\meta\structure\BinaryColumn;
use n2n\persistence\meta\structure\IntegerColumn;
use n2n\persistence\meta\structure\Column;
use n2n\persistence\meta\structure\EnumColumn;

class PgsqlColumnStatementFragmentBuilder {
	private $pdo;
	private $enumStatementBuilder;
	private $inAlterMode = false;
	
	public function __construct(Pdo $pdo) {
		$this->pdo = $pdo;
		$this->enumStatementBuilder = new PgsqlEnumStatementBuilder($pdo);
	}
	
	public function buildDropColumnStatement(Column $column) {
		$quotedEntityName = $this->pdo->quoteField($column->getTable()->getName());
		$quotedColumnName = $this->pdo->quoteField($column->getName());
		
		$sql = 'ALTER TABLE ' . $quotedEntityName . ' DROP COLUMN ' . $quotedColumnName . ';';
		
		if ($column instanceof EnumColumn) {
			$sql .= $this->enumStatementBuilder->buildDropEnumTypeStatement($column);
		}
		
		return $sql;
	}
	
	public function buildAddColumnStatement(Column $column) {
		$quotedEntityName = $this->pdo->quoteField($column->getTable()->getName());
		
		return 'ALTER TABLE ' . $quotedEntityName . ' ADD COLUMN ' .
				$this->generateColumnFragment($column) . ';';
	}
	
	public function buildAlterColumnStatement(Column $column, Column $originalColumn) {
		$quotedEntityName = $this->pdo->quoteField($column->getTable()->getName());
		$quotedColumnName = $this->pdo->quote($column->getName());
		
		if ($column->equalsType($originalColumn)) {
			$sql = '';
			if ($column instanceof EnumColumn) {
				$sql .= $this->enumStatementBuilder->containsEnumType($column) 
						. $this->enumStatementBuilder->buildCreateEnumTypeStatement($column); 
			}
			
			$this->inAlterMode = true;
			$sql = 'ALTER TABLE ' . $quotedEntityName . ' ALTER COLUMN ' . $quotedColumnName . ' ' 
					. $this->generateColumnFragment($column);
			$this->inAlterMode = false;
			
			return $sql;
		}
		
		return $this->buildDropColumnStatement($column) . $this->buildAddColumnStatement($column);
	}
	
	public function generateColumnFragment(Column $column) {
		if (null === ($type = $this->getTypeForCurrentState($column))) {
			throw new UnavailableTypeException('No column type for column "' . $column->getName() . '" with type "' . get_class($column) 
							.'" in Table "' . $column->getTable()->getName(). '" given.');	
		} 
		
		$statementString = $this->pdo->quoteField($column->getName()) . ($this->inAlterMode ? ' TYPE ': ' ') . $type;
		$statementString .= $this->generateDefaultStatementStringPart($column);
		
		return $statementString;
	}

	private function generateDefaultStatementStringPart(Column $column, bool $alter = false) {
		$statementString = '';
		
		if ($column->isNullAllowed()) {
			$statementString .= ' NULL';
		} else {
			$statementString .= ' NOT NULL';
		}
		
		$defaultValue = $column->getDefaultValue();
		if ($column->isDefaultValueAvailable() 
				&& (null !== $defaultValue || $column->isNullAllowed())) {
			$statementString .= ($this->inAlterMode ? ' SET ': ' ') . 'DEFAULT ';
			if (null === $defaultValue) {
				$statementString .= 'NULL';
			} elseif (is_numeric($defaultValue)) {
				$statementString .= $defaultValue;
			} else {
				$statementString .= $this->dbh->quote($defaultValue) ;
			}
		}
		
		return $statementString;
	}
	
	private function getTypeForCurrentState(Column $column) {
		if ($column instanceof BinaryColumn) {
			return 'BYTEA';
		}
		
		if ($column instanceof DateTimeColumn) {
			if ($column->isDateAvailable() && $column->isTimeAvailable()) {
				return 'TIMESTAMP';
			}
			
			if ($column->isDateAvailable()) {
				return 'DATE';
			}
			
			return 'TIME';
		}
		
		if ($column instanceof FixedPointColumn) {
			return 'NUMERIC(' . ($column->getNumIntegerDigits() + $column->getNumDecimalDigits()) . ',' . $column->getNumDecimalDigits() . ')';
		}
		
		if ($column instanceof FloatingPointColumn) {
			if ($column->getSize() <= Size::FLOAT) {
				return 'REAL';
			}
			
			return 'DOUBLE PRECISION';
		}
		
		if ($column instanceof IntegerColumn) {
			if ($column->getSize() <= Size::MEDIUM) {
				if ($column->isValueGenerated() && !$column->isNullAllowed()) {
					return 'SMALLSERIAL';
				}
				
				return 'SMALLINT';
			}
			
			if ($column->getSize() <= Size::INTEGER) {
				if ($column->isValueGenerated() && !$column->isNullAllowed()) {
					return 'SERIAL'; 
				}
				
				return 'INTEGER';
			}
		
			if ($column->isValueGenerated() && !$column->isNullAllowed()) {
				return 'BIGSERIAL';
			}
			return 'BIGINT';
		}
		
		if ($column instanceof StringColumn) {
			return 'VARCHAR(' . $column->getLength(). ')';
		}
		
		if ($column instanceof TextColumn) {
			return 'TEXT';
		}
		
		if ($column instanceof EnumColumn) {
			return $this->enumStatementBuilder->buildEnumColumnName($column);
		}

		if (($attrs = $column->getAttrs()) && isset($attrs['data_type']) ) {
			return $attrs['data_type'];
		}
		
		return null;
	}
}
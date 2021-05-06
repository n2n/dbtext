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
namespace n2n\impl\persistence\meta\sqlite;

use n2n\persistence\meta\structure\Index;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\Pdo;

class SqliteIndexStatementStringBuilder {
	
	private $dbh;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	}
	
	public function generateCreateStatementString(Index $index) {
		$statementString = 'CREATE';
	
		switch ($index->getType()) {
			case (IndexType::PRIMARY) :
				throw new \InvalidArgumentException('Sqlite does not allow to create primary keys manually');
			case (IndexType::FOREIGN) :
				throw new \InvalidArgumentException('Sqlite does not allow to create foreign keys manually');
			case (IndexType::UNIQUE) :
				$statementString .= ' UNIQUE';
		}
		
		$statementString .=  ' INDEX ' . $this->dbh->quoteField($index->getName()) . ' ON ' 
						. $this->dbh->quoteField($index->getTable()->getName()) . '(';
	
		$first = true;
		foreach ($index->getColumns() as $column) {
			if (!$first) {
				$statementString .= ', ';
			} else {
				$first = false;
			}
			$statementString .= $this->dbh->quoteField($column->getName());
		}
	
		$statementString .= ')';
	
		return $statementString;
	}
	
	public function generateDropStatementString(Index $index) {
		$statementString = '';
		switch ($index->getType()) {
			case (IndexType::PRIMARY) :
				throw new \InvalidArgumentException('Sqlite does not allow to drop primary keys manually');
			case (IndexType::UNIQUE) :
			case (IndexType::INDEX) :
				$statementString .= 'DROP INDEX'  
						. $this->dbh->quoteField($index->getName());
				break;
		}
		return $statementString;
	}
	
}

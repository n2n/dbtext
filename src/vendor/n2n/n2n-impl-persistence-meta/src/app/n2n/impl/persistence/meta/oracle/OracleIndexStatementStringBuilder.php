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
namespace n2n\impl\persistence\meta\oracle;

use n2n\persistence\meta\structure\Index;

use n2n\persistence\meta\structure\IndexType;

use n2n\persistence\Pdo;

class OracleIndexStatementStringBuilder {
	
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	}
	
	public function generateCreateStatementString(OracleTable $table, Index $index) {
		$statementString = '';
	
		switch ($index->getType()) {
			case (IndexType::PRIMARY) :
				$statementString .= 'ALTER TABLE ' . $this->dbh->quoteField($table->getName()) . 'ADD PRIMARY KEY';
				break;
			case (IndexType::UNIQUE) :
				$statementString .= 'CREATE UNIQUE INDEX ' . $this->dbh->quoteField($index->getName()) . ' ON ' . $this->dbh->quoteField($table->getName());
				break;
			case (IndexType::INDEX) :
				$statementString .= 'CREATE INDEX ' . $this->dbh->quoteField($index->getName()) . ' ON ' . $this->dbh->quoteField($table->getName());
				break;
		}
		
		$statementString .=  ' (';
	
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
	
	public function generateDropStatementString(OracleTable $table, Index $index) {
		switch ($index->getType()) {
			case (IndexType::PRIMARY) :
				return 'ALTER TABLE ' . $this->dbh->quoteField($table->getName()) . ' DROP PRIMARY KEY';
			default:
				return 'DROP INDEX ' . $index->getName(); 
		}
	}
}

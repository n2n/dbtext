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
namespace n2n\impl\persistence\meta\mysql;

use n2n\persistence\meta\structure\Index;

use n2n\persistence\meta\structure\IndexType;

use n2n\persistence\Pdo;

class MysqlIndexStatementStringBuilder {
	
	const INDEX_TYPE_NAME_FULLTEXT_INDEX = 'FULLTEXT';
	
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	
	public function __construct(Pdo $dbh) {
		$this->dbh = $dbh;
	}
	
	public function generateCreateStatementString(Index $index) {
		$statementString = '';
		$attrs = $index->getAttrs();
		if (isset($attrs['Index_type']) && ($attrs['Index_type'] == self::INDEX_TYPE_NAME_FULLTEXT_INDEX)) {
			$statementString .= 'FULLTEXT INDEX ' . $this->dbh->quoteField($index->getName());
		} else {
			switch ($index->getType()) {
				case (IndexType::PRIMARY) :
					$statementString .= 'PRIMARY KEY';
					break;
				case (IndexType::UNIQUE) :
					$statementString .= 'UNIQUE INDEX ' . $this->dbh->quoteField($index->getName());
					break;
				case (IndexType::INDEX) :
					$statementString .= 'INDEX ' . $this->dbh->quoteField($index->getName());
					break;
				case (IndexType::FOREIGN) :
					$statementString .= 'CONSTRAINT ' . $this->dbh->quoteField($index->getName()) . ' FOREIGN KEY';
					break;
			}
		}
		
		$statementString .= $this->buildColumnsString($index->getColumns());
		
		if ($index->getType() === IndexType::FOREIGN) {
			$statementString .= ' REFERENCES ' . $this->dbh->quoteField($index->getRefTable()->getName()) 
					. $this->buildColumnsString($index->getRefColumns());
		}
	
		return $statementString;
	}
	
	private function buildColumnsString(array $columns) {
		$statementString =  ' (';
		
		$first = true;
		foreach ($columns as $column) {
			if (!$first) {
				$statementString .= ', ';
			} else {
				$first = false;
			}
			$statementString .= $this->dbh->quoteField($column->getName());
		}
		
		return $statementString . ')';
	}
	
	public function generateDropStatementString(Index $index, bool $checkExistance = false) {
		$dropStatementSTring =  'ALTER TABLE ' . $this->dbh->quoteField($index->getTable()->getName()) . ' DROP ';
		
		switch ($index->getType()) {
			case (IndexType::PRIMARY) :
				return $dropStatementSTring . 'PRIMARY KEY';
			case (IndexType::UNIQUE) :
			case (IndexType::INDEX) :
				return $dropStatementSTring . $this->dbh->quoteField($index->getName());
			case (IndexType::FOREIGN) :
				return $dropStatementSTring. 'FOREIGN KEY ' . ($checkExistance ? 'IF EXISTS ' : '') 
						.  $this->dbh->quoteField($index->getName());
		}
	}
}

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
namespace n2n\persistence\meta;

use n2n\persistence\Pdo;

class MetaData {
	private $dbh;
	private $dialect;
	private $metaManager;
	private $database;
	
	public function __construct(Pdo $dbh, Dialect $dialect) {
		$this->dbh = $dbh;
		$this->dialect = $dialect;
	}
	/**
	 * @return \n2n\persistence\meta\MetaManager
	 */
	
	public function getMetaManager() {
		if (null === $this->metaManager) {
			$this->metaManager = $this->getDialect()->createMetaManager($this->dbh);
		}
		
		return $this->metaManager;
	}
	
	/**
	 * @return \n2n\persistence\meta\Database
	 */
	public function getDatabase() {
		if (null === $this->database) {
			$this->database = $this->getMetaManager()->createDatabase();
		}
		
		return $this->database;
	}
	
	/**
	 * @return \n2n\persistence\meta\data\SelectStatementBuilder
	 */
	public function createSelectStatementBuilder() {
		return $this->dialect->createSelectStatementBuilder($this->dbh);
	}
	
	/**
	 * @return \n2n\persistence\meta\data\InsertStatementBuilder
	 */
	public function createInsertStatementBuilder() {
		return $this->dialect->createInsertStatementBuilder($this->dbh);
	}
	
	/**
	 * @return \n2n\persistence\meta\data\UpdateStatementBuilder
	 */
	public function createUpdateStatementBuilder() {
		return $this->dialect->createUpdateStatementBuilder($this->dbh);
	}
	/**
	 * @return \n2n\persistence\meta\data\DeleteStatementBuilder
	 */
	public function createDeleteStatementBuilder() {
		return $this->dialect->createDeleteStatementBuilder($this->dbh);
	}
	
	/**
	 * @return \n2n\persistence\meta\Dialect
	 */
	public function getDialect() {
		return $this->dialect;
	}
}

<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the HANGAR PROJECT.
 *
 * HANGAR is free to use. You are free to redistribute it but are not permitted to make any
 * modifications without the permission of Hofmänner New Media.
 *
 * HANGAR is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * The following people participated in this project:
 *
 * Thomas Günther.............: Developer, Architect, Frontend UI, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Concept
 * Andreas von Burg...........: Concept
 */
namespace hangar\api;

use n2n\persistence\meta\Database;
use n2n\persistence\meta\structure\Table;

class DbInfo {
	private $database;
	private $table;
	
	public function __construct(Database $database, Table $table) {
		$this->database = $database;
		$this->table = $table;
	}
	
	public function getDatabase() {
		return $this->database;
	}
	/**
	 * @return Table
	 */
	public function getTable() {
		return $this->table;
	}
	
	public function removeColumn($columnName) {
		if (!$this->isColumnAvailable($columnName)) return;
		
		$this->table->removeColumnByName($columnName);
	}
	
	public function isColumnAvailable($columnName) {
		return $this->table->containsColumnName($columnName);
	}
}

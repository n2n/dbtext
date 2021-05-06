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
namespace n2n\impl\persistence\meta\mssql;

use n2n\persistence\meta\structure\common\CommonView;

use n2n\persistence\meta\structure\MetaEntityFactory;
use n2n\persistence\meta\Database;
use n2n\persistence\meta\structure\Table;
use n2n\persistence\meta\structure\View;

class MssqlMetaEntityFactory implements MetaEntityFactory {
	
	/**
	 * @var MssqlDatabase
	 */
	private $database;
	
	public function __construct(MssqlDatabase $database) {
		$this->database = $database;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\MetaEntityFactory::getDatabase()
	 * @return Database
	 */
	public function getDatabase(): Database {
		return $this->database;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\MetaEntityFactory::createTable()
	 * @return Table
	 */
	public function createTable(string $name): Table {
		$metaEntity = new MssqlTable($name);
		$this->database->addMetaEntity($metaEntity);
		return $metaEntity;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\MetaEntityFactory::createView()
	 * @return View
	 */
	public function createView($name, $query): View {
		$metaEntity = new CommonView($name, $query);
		$this->database->addMetaEntity($metaEntity);
		return $metaEntity;
	}
}

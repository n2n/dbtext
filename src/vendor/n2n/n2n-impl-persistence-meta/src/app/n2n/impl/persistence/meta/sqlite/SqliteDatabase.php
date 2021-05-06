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

use n2n\persistence\meta\structure\common\DatabaseAdapter;
use n2n\persistence\meta\structure\MetaEntityFactory;

class SqliteDatabase extends DatabaseAdapter {
	const RESERVED_NAME_PREFIX = 'sqlite_';
	const FIXED_DATABASE_NAME = 'main';
	
	/**
	 * @var SqliteMetaEntityFactory
	 */
	private $metaEntityFactory;
	private $charset;
	private $attrs;
	
	public function __construct($charset, $metaEntities, $attrs) {
		parent::__construct(self::FIXED_DATABASE_NAME, $charset, $metaEntities, $attrs);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\Database::createMetaEntityFactory()
	 * @return MetaEntityFactory
	 */
	public function createMetaEntityFactory(): MetaEntityFactory {
		if (null === $this->metaEntityFactory) {
			$this->metaEntityFactory = new SqliteMetaEntityFactory($this);
		}
		
		return $this->metaEntityFactory;
	}
}

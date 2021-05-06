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
namespace n2n\impl\persistence\meta\pgsql;

use n2n\io\InputStream;

use n2n\persistence\Pdo;

use n2n\persistence\meta\data\Importer;
use n2n\util\ex\IllegalStateException;

class PgsqlImporter implements Importer {
	private $dbh;
	private $inputStream;

	public function __construct(Pdo $dbh, InputStream $inputStream) {
		$this->dbh = $dbh;
		$this->inputStream = $inputStream;
	}

	public function execute() {
		if (!($this->inputStream->isOpen())) {
			throw new IllegalStateException('Inputstream not open');
		}
		$this->dbh->exec($this->inputStream->read());
	}
}

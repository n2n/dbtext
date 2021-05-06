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

use n2n\util\ex\IllegalStateException;
use n2n\io\InputStream;
use n2n\persistence\Pdo;
use n2n\persistence\meta\data\Importer;

class MssqlImporter implements Importer {
	const STATEMENT_DELIMITER = ';';
	const BATCH_CONTEXT_DELIMITER = 'GO';
	/**
	 * @var \n2n\persistence\Pdo
	 */
	private $dbh;
	
	/**
	 * @var \n2n\io\InputStream
	 */
	private $inputStream;
	
	/**
	 * @param \n2n\persistence\Pdo $dbh
	 * @param \n2n\io\InputStream $inputStream
	 */
	public function __construct(Pdo $dbh, InputStream $inputStream) {
		$this->dbh = $dbh;
		$this->inputStream = $inputStream;
	}
	
	public function execute() {
		if (!($this->inputStream->isOpen())) {
			throw new IllegalStateException('Inputstream not open');
		}
		
		$statement = '';
		$inStringContext = '';
		$containsGo = false;
		foreach (str_split($this->inputStream->read()) as $character)  {
			if ($character == '\'' || $character == '"')  {
				if (strlen($inStringContext) == 0) {
					$inStringContext = $character;
				} else {
					if ($inStringContext == $character) {
						$inStringContext = '';
					}
				}
			}
			if (strlen($inStringContext) == 0 && ($character == self::STATEMENT_DELIMITER ||
					($containsGo = substr($statement . $character, -strlen(self::BATCH_CONTEXT_DELIMITER)) == self::BATCH_CONTEXT_DELIMITER))) {
				// The GO-Keyword is used in Many MS utilities including SQL Server Management Studio to determine if you are in Batch Context
				// @see http://stackoverflow.com/questions/2299249/what-is-the-use-of-go-in-sql-server-management-studio
				if ($containsGo) {
					$statement = substr($statement . $character, 0, strlen($statement . $character) - strlen(self::BATCH_CONTEXT_DELIMITER));
				}
				if ($statement) {
					$this->dbh->exec($statement);
				}
				$statement = '';
			} else {
				$statement .= $character;
			}
		}
	}

}

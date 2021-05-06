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
namespace n2n\persistence\meta\structure\common;

use n2n\core\N2N;

use n2n\io\OutputStream;

use n2n\persistence\Pdo;

use n2n\persistence\meta\Database;

use n2n\persistence\meta\structure\Backuper;

abstract class BackuperAdapter implements Backuper {
/**
	 * @var Database
	 */
	protected $database;

	/**
	 * @var \n2n\persistence\Pdo
	 */
	protected $dbh;
	/**
	 * @var array
	 */
	private $metaEntities;
	private $backupStructureEnabled;
	private $backupDataEnabled;
	private $replaceTableEnabled;
	/**
	 * @var n2n\persistence\meta\Dialect
	 */
	protected $dialect;
	
	/**
	 * @var n2n\io\OutputStream
	 */
	private $outputStream;

	public function __construct(Pdo $dbh, Database $database, array $metaEntities = null) {
		$this->dbh = $dbh;
		$this->dialect = $dbh->getMetaData()->getDialect();
		$this->database = $database;
		$this->metaEntities = $metaEntities;
		$this->backupDataEnabled = true;
		$this->backupStructureEnabled = true;
		$this->replaceTableEnabled = true;
	}

	public function setMetaEntities(array $metaEntities = null) {
		$this->metaEntities = $metaEntities;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\meta\structure\Backuper::getMetaEntities()
	 */
	public function getMetaEntities() {
		return $this->metaEntities;
	}

	public function setBackupStructureEnabled($backupStructureEnabled) {
		$this->backupStructureEnabled = $backupStructureEnabled;
	}

	public function isBackupStructureEnabled() {
		return $this->backupStructureEnabled;
	}

	public function setBackupDataEnabled($backupDataEnabled) {
		$this->backupDataEnabled = (bool) $backupDataEnabled;
	}

	public function isBackupDataEnabled() {
		return $this->backupDataEnabled;
	}

	public function setOutputStream(OutputStream $outputStream) {
		$this->outputStream = $outputStream;
	}

	public function getOutputStream() {
		return $this->outputStream;
	}
	
	public function setReplaceTableEnabled($replaceTableEnabled) {
		$this->replaceTableEnabled = (bool) $replaceTableEnabled;
	}
	
	public function isReplaceTableEnabled() {
		return $this->replaceTableEnabled;
	}

	protected function getHeader() {
		$now = new \DateTime();
		return "-- " . $this->dialect->getName() .  " Backup of " . $this->database->getName() . PHP_EOL 
				. "-- Date " . $now->format(\DateTime::W3C) . PHP_EOL  
				. "-- Backup by " . N2N::getAppConfig()->general()->getPageUrl() . PHP_EOL . PHP_EOL;
	}
}

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
namespace n2n\persistence\meta\structure;

use n2n\util\ex\IllegalStateException;

use n2n\io\OutputStream;

interface Backuper {
	/**
	 * The passed MetaEntities will be backuped. If null all MetaEntities will be backuped.
	 * 
	 * @param array $metaEntities An array of MetaEntities or names of MetaEntities
	 */
	public function setMetaEntities(array $metaEntities);
	/**
	 * Returns the MetaEntities which will be backuped.
	 */
	public function getMetaEntities();
	/**
	 * backupStructureEnabled = true causes the Backuper to create CREATE TABLE statements for each table.
	 * 
	 * @param bool $backupStructureEnabled If true, the structure will be backuped. 
	 */
	public function setBackupStructureEnabled($backupStructureEnabled);
	/**
	 * 
	 * @return boolean true If structure will be backuped. 
	 */
	public function isBackupStructureEnabled();
	
	/**
	 * backupStructureEnabled = true causes the Backuper to create INSERT STATEMENTS statements for each line.
	 *
	 * @param bool $backupStructureEnabled If true, the data will be backuped.
	 */
	public function setBackupDataEnabled($backupStructureEnabled);
	/**
	 *
	 * @return boolean true If data will be backuped.
	 */
	public function isBackupDataEnabled();
	/**
	 * Backup will be written to the OutputStream 
	 * @param OutputStream $outputStream
	 */
	public function setOutputStream(OutputStream $outputStream);
	/**
	 * @return OutputStream
	 */
	public function getOutputStream();
	/**
	 * $replaceTableEnabled = true advises the backuper to create a backup which replaces its structure when imported.  
	 * @param bool $replaceTableEnabled
	 */
	public function setReplaceTableEnabled($replaceTableEnabled);
	/**
	 * @return bool
	 */
	public function isReplaceTableEnabled();
	/**
	 * Starts writing to output stream
	 * @throws IllegalStateException
	 */
	public function start();
}

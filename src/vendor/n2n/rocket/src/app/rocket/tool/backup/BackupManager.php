<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\tool\backup;

use n2n\io\fs\FileResourceStream;
use n2n\core\VarStore;
use n2n\core\N2N;
use n2n\io\managed\impl\FileFactory;

class BackupManager {
	const PREFIX_FILE_NAME = 'backup';
	const SUFFIX_FILE_NAME = 'full-manual.sql';
	const DATE_TIME_FORMAT = 'Y-m-d-H-i-s';
	const MODULE_DIR = 'rocket';
	
	public static function createBackup($fileName = null) {
		$backuper = N2N::getPdoPool()->getPdo()->getMetaData()->getMetaManager()->createBackuper();
		$backuper->setBackupDataEnabled(true);
		$backuper->setReplaceTableEnabled(true);
		$backuper->setOutputStream(new FileResourceStream(self::generateFile($fileName), 'w'));
		$backuper->start();
	}
	
	public static function deleteBackups($pattern) {
		foreach (self::getBackupDir()->getChildren($pattern) as $fsPath) {
			$fsPath->delete();
		}
	}
	
	public static function requestBackupFile($fileName) {
		return FileFactory::createFromFs(N2N::getVarStore()->requestFileFsPath(
				VarStore::CATEGORY_BAK, self::MODULE_DIR, null, $fileName), $fileName);
	}
	/**
	 * @return \n2n\io\fs\FsPath
	 */
	public static function getBackupDir() {
		return N2N::getVarStore()->requestDirFsPath(VarStore::CATEGORY_BAK, self::MODULE_DIR, null, true);
	}
	
	private static function generateFile($fileName = null) {
		if (is_null($fileName)) {
			$fileName = implode('-', array(self::PREFIX_FILE_NAME, date(self::DATE_TIME_FORMAT), self::SUFFIX_FILE_NAME));
		}
		return N2N::getVarStore()->requestFileFsPath(VarStore::CATEGORY_BAK, self::MODULE_DIR, null,
				$fileName, true, true);
	}
}

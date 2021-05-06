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
namespace rocket\tool\backup\controller;

use rocket\tool\backup\BackupManager;
use n2n\web\http\controller\ControllerAdapter;
use n2n\util\DateUtils;
use n2n\util\DateParseException;
use n2n\context\Lookupable;

class BackupBatchController extends ControllerAdapter implements Lookupable {
	const SUFFIX_FILE_NAME = 'full-daily.sql';
	
	public function _onNewDay() {
		BackupManager::createBackup(implode('-', array(BackupManager::PREFIX_FILE_NAME, 
				date(BackupManager::DATE_TIME_FORMAT), self::SUFFIX_FILE_NAME)));
		$this->cleanUpBackupDir();
	}
	
	private function cleanUpBackupDir() {
		$today = new \DateTime();
		$backupIndex = array();
// 		$children = BackupManager::getBackupDir()->getChildren();
		foreach (array_reverse(BackupManager::getBackupDir()->getChildren()) as $file) {
			//Just Regard automatically created Backups
			if (strpos($file, self::SUFFIX_FILE_NAME) === false) continue;
			$matches = array();
			if (!preg_match('/\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}/', (string) $file, $matches)) continue;
			try {
				$dateCreated = DateUtils::createDateTimeFromFormat(BackupManager::DATE_TIME_FORMAT, reset($matches));
				$diff = $today->diff($dateCreated);
				if (!$diff->invert) continue;
				$delete = false;
				if ($diff->y > 0) {
					if (!isset($backupIndex[$dateCreated->format('Y-M')])) {
						$backupIndex[$dateCreated->format('Y-M')] = true;
						continue;
					}
					$delete = true;
				}
				if (!$delete && $diff->m > 0) {
					if ($dateCreated->format('w') === '1' && !isset($backupIndex[$dateCreated->format('Y-M-d')])) {
						$backupIndex[$dateCreated->format('Y-M-d')] = true;
						continue;
					}
					$delete = true;
				}
				if ($delete) {
					BackupManager::deleteBackups($file->getName());
				}
			} catch (DateParseException $e) {
				continue;
			} 
		}
	}
}

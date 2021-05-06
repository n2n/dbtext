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

class BackupController extends ControllerAdapter {
	
	public function index() {
		$this->forward('..\view\backupOverview.html', 
				array('files' => array_reverse(BackupManager::getBackupDir()->getChildren('*.sql'))));
	}
	
	public function doCreate() {
		BackupManager::createBackup();
		$this->redirectToController();
	}
	
	public function doDownload($fileName) {
		$this->sendFile(BackupManager::requestBackupFile($fileName));
	}
	
	public function doDelete($pattern) {
		BackupManager::deleteBackups($pattern);
		$this->redirectToController();
	}
}

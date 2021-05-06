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
namespace rocket\tool\controller;

use rocket\tool\backup\controller\BackupController;

use n2n\web\http\controller\ControllerAdapter;
use rocket\tool\mail\controller\MailCenterController;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\http\annotation\AnnoPath;
use n2n\web\http\ForbiddenException;
use rocket\user\model\LoginContext;
use n2n\web\http\StatusException;
use n2n\web\http\Response;

class ToolController extends ControllerAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->m('backupOverview', new AnnoPath(self::ACTION_BACKUP_OVERVIEW . '/params*:*'));
		$ai->m('mailCenter', new AnnoPath(self::ACTION_MAIL_CENTER . '/params*:*'));
		$ai->m('clearCache', new AnnoPath(self::ACTION_CLEAR_CACHE));
	}
	
	const ACTION_BACKUP_OVERVIEW = 'backup-overview';
	const ACTION_MAIL_CENTER = 'mail-center';
	const ACTION_CLEAR_CACHE = 'clear-cache';
	
	private $loginContext;
	
	private function _init(LoginContext $loginContext) {
		$this->loginContext = $loginContext;
	}
	
	private function verifyAdmin() {
		if ($this->loginContext->getCurrentUser()->isAdmin()) return;
		
		throw new ForbiddenException();
	}
	
	private function verifyHtml() {
		if ('text/html' == $this->getRequest()->getAcceptRange()
				->bestMatch(['text/html', 'application/json'])) {
			$this->forward('\rocket\core\view\anglTemplate.html');
			return true;
		}
		
		return false;
	}
	
	public function index() {
		$this->verifyAdmin();
		
		if ($this->verifyHtml()) {
			return;
		}
		
		throw new StatusException(Response::STATUS_406_NOT_ACCEPTABLE);
	}
	
	public function backupOverview(array $params = null) {
		$this->verifyAdmin();
		
		if ($this->verifyHtml()) {
			return;
		}
		
		$this->delegate(new BackupController());
	}
	
	public function mailCenter(MailCenterController $mailCenterController, array $params = null) {
		$this->verifyAdmin();
		
		if (empty($params) && $this->verifyHtml()) {
			return;
		}
		
		$this->delegate($mailCenterController);
	}
	
	public function clearCache() {
		$this->verifyAdmin();
		
		if ($this->verifyHtml()) {
			return;
		}

		$this->getN2nContext()->getAppCache()->clear();
	}
}

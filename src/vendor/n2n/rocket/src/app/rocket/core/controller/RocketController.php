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
namespace rocket\core\controller;

use rocket\tool\controller\ToolController;
use n2n\web\http\ForbiddenException;
use rocket\user\model\LoginContext;
use rocket\user\controller\RocketUserController;
use rocket\core\model\RocketState;
use n2n\web\http\PageNotFoundException;
use n2n\l10n\N2nLocale;
use n2n\web\http\Request;
use n2n\web\http\controller\ControllerAdapter;
use rocket\core\model\DeleteLoginModel;
use rocket\core\model\Rocket;
use n2n\core\container\PdoPool;
use rocket\user\controller\RocketUserGroupController;
use n2n\web\http\Response;
use rocket\core\model\launch\UnknownLaunchPadException ;
use n2n\core\config\N2nLocaleConfig;
use n2n\web\http\controller\impl\ScrRegistry;
use n2n\web\http\controller\impl\ScrBaseController;
use n2n\l10n\MessageContainer;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;
use n2n\core\N2N;
use n2n\web\http\NoHttpRefererGivenException;

class RocketController extends ControllerAdapter {
	const NAME = 'rocket';
	
	private $loginContext;
	
	private function _init(LoginContext $loginContext) {
		$this->loginContext = $loginContext;
	}
	
	public function prepare(Request $request, N2nLocaleConfig $localeConfig, ScrRegistry $scrRegistry, 
			Rocket $rocket) {
		$request->setN2nLocale($localeConfig->getAdminN2nLocale());
		$this->getControllerContext()->setName(self::NAME);
		$scrRegistry->setBaseUrl($this->getHttpContext()->getControllerContextPath($this->getControllerContext())
				->ext('scr')->toUrl());
		$rocket->setControllerContext($this->getControllerContext());
	}
	
	public function doDevLogin($userId) {
		if (!N2N::isDevelopmentModeOn()) {
			throw new ForbiddenException();
		}
		
		$this->loginContext->loginByUserId($userId);
		
		try {
			$this->redirectToReferer();
		} catch (NoHttpRefererGivenException $e) {
			$this->redirectToController();
		}
	}
	
	private function verifyUser() {
		if ($this->loginContext->hasCurrentUser()) {
			return true;
		}
		
		$this->beginTransaction();
		
		if ($this->dispatch($this->loginContext, 'login')) {
			$this->commit();
			$this->refresh();
			return false;
		}
		
		$this->commit();
		
		$this->forward('~\user\view\login.html', array('loginContext' => $this->loginContext));
		return false;
	}
	
	public function doLogout() {
		$this->loginContext->logout();
		$this->redirectToController();
	}
	
	public function index() {
		if (!$this->verifyUser()) return;
		
// 		if ('text/html' == $this->getRequest()->getAcceptRange()
// 				->bestMatch(['text/html', 'application/json'])) {
			$this->forward('\rocket\core\view\anglTemplate.html');
			return;
// 		}
		
		
	}
	
	public function doUsers(RocketUserController $delegateController, array $delegateParams = array()) {
		if (!$this->verifyUser()) return;
		
		$this->delegate($delegateController);
	}
	
	public function doUserGroups(RocketUserGroupController $delegateController, array $delegateParams = array()) {
		if (!$this->verifyUser()) return;
		
		if (!$this->loginContext->getCurrentUser()->isAdmin()) {
			throw new ForbiddenException();
		}
		
		$this->delegate($delegateController);
	}
	
	public function doManage(Rocket $rocket, RocketState $rocketState, N2nLocale $n2nLocale, PdoPool $dbhPool, 
			MessageContainer $mc, $navItemId, array $delegateParams = array()) {
		if (!$this->verifyUser()) return;
		
		$launchPad = null;
		try {
			$launchPad = $rocket->getSpec()->getLaunchPadById($navItemId);
		} catch (UnknownLaunchPadException $e) {
			throw new PageNotFoundException('navitem not found', 0, $e);
		}
		
		if (!$this->loginContext->getSecurityManager()->isLaunchPadAccessible($launchPad)) {
			throw new ForbiddenException();
		}
		
		$rocketState->setActiveLaunchPad($launchPad);
		
		$this->beginTransaction();
		
		$delegateControllerContext = $this->createDelegateContext();
		$delegateControllerContext->setController($launchPad->lookupController($this->getN2nContext(), 
				$delegateControllerContext));
		
		$this->delegateToControllerContext($delegateControllerContext);
		
		$transactionApproveAttempt = $launchPad->approveTransaction($this->getN2nContext());
		if ($transactionApproveAttempt->isSuccessful()) {
			$this->commit();
		
// 			$bo = $this->getResponse()->fetchBufferedOutput();
// 			$this->getResponse()->reset();
// 			echo $bo;
// 			die('HOLERADIO');
			return;
		}
		
		$mc->addAll($transactionApproveAttempt->getReasonMessages());
// 		test($transactionApproveAttempt->getReasonMessages());
// 		$bo = $this->getResponse()->fetchBufferedOutput();
// 		$this->getResponse()->reset();
// 		echo $bo;
// 		die('HOLERADIO');
		$this->rollBack();
	}
	
	public function doTools(ToolController $toolController, array $delegateParams = array()) {
		if (!$this->verifyUser()) return;

		if (!$this->loginContext->getCurrentUser()->isAdmin()) {
			throw new ForbiddenException();
		}
		
		$this->delegate($toolController);
	}
	
	public function doScr(array $delegateParams, ScrBaseController $scrBaseController) {
		if (!$this->verifyUser()) return;
		
		$this->delegate($scrBaseController);
	}
	
	public function notFound() {
		if (!$this->verifyUser()) return;
		
		$this->getResponse()->setStatus(Response::STATUS_404_NOT_FOUND);
		$this->forward('..\view\notFound.html');
	}
	
	public function doAbout() {
		if (!$this->verifyUser()) return;
		
		$this->forward('..\view\about.html');
	}
}

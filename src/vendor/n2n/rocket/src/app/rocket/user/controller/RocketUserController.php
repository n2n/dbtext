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
namespace rocket\user\controller;

use rocket\core\model\RocketState;
use n2n\l10n\DynamicTextCollection;
use n2n\l10n\MessageContainer;
use rocket\user\model\RocketUserForm;
use n2n\web\http\ForbiddenException;
use n2n\web\http\PageNotFoundException;
use rocket\user\model\LoginContext;
use rocket\user\model\RocketUserDao;
use n2n\web\http\controller\ControllerAdapter;
use rocket\user\bo\RocketUser;
use n2n\web\http\controller\ParamBody;
use n2n\validation\build\impl\Validate;
use n2n\validation\plan\impl\Validators;
use n2n\l10n\Message;
use n2n\util\crypt\hash\HashUtils;
use n2n\web\http\controller\impl\HttpData;

class RocketUserController extends ControllerAdapter {
	private $rocketUserDao;
	private $loginContext;
	private $rocketState;
	private $dtc;
	
	private function _init(RocketUserDao $rocketUserDao, LoginContext $loginContext, RocketState $rocketState,
			DynamicTextCollection $dtc) {
		$this->rocketUserDao = $rocketUserDao;
		$this->loginContext = $loginContext;
		$this->rocketState = $rocketState;
		$this->dtc = $dtc;
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
		
		$this->sendJson([
			'users' => $this->rocketUserDao->getUsers()
		]);
	}
	
	function doAdd(MessageContainer $messageContainer) {
		$this->verifyAdmin();
		
		if ($this->verifyHtml()) {
			return;
		}
		
		throw new PageNotFoundException();
	}
	
	function postDoAdd(ParamBody $body, RocketUserDao $userDao) {
		$this->verifyAdmin();
		
		$this->beginTransaction();
		
		$httpData = $body->parseJsonToHttpData();
		
		
		$user = new RocketUser();
		
		if (!$this->handlePw($user, $httpData)) {
			return;
		}
		
		$this->handleUser($user, $httpData->reqHttpData('user'));
	}
	
	private function handlePw(RocketUser $user, HttpData $httpData) {
		$valResult = $this->val(Validate::attrs($httpData->toDataMap())
				->props(['password', 'passwordConfirmation'], Validators::mandatory(),
						Validators::closure(function ($password, $passwordConfirmation) {
							if ($password === $passwordConfirmation) {
								return;
							}
							
							return [
								'passwordConfirmation' => Message::createCode('password_confirmation_does_not_match_err', 
										null, 'rocket')
							];
						})));
		
		if ($valResult->sendErrJson()) {
			return false;
		}
		
		$user->setPassword(HashUtils::buildHash($httpData->reqString('password')));
		return true;
	}
	
	/**
	 * @param int $userId
	 * @throws PageNotFoundException
	 * @return \rocket\user\bo\RocketUser
	 */
	private function lookupUser($userId, bool $editable) {
		$user = $this->rocketUserDao->getUserById($userId);
		
		if (null === $user) {
			throw new PageNotFoundException();
		}
		
		if (!$this->loginContext->getCurrentUser()->isSuperAdmin() && $user->isSuperAdmin()) {
			throw new ForbiddenException();
		}
		
		return $user;
	}
	
// 	public function doEdit($userId, MessageContainer $messageContainer) {
// 		$this->verifyAdmin();
		
// 		$this->beginTransaction();
		
// 		$user = $this->rocketUserDao->getUserById($userId);
// 		if (null === $user) {
// 			throw new PageNotFoundException();
// 		}

// 		$currentUser = $this->loginContext->getCurrentUser();
		
// 		$userForm = new RocketUserForm($user, $this->rocketUserDao->getRocketUserGroups());
// 		if (!$user->equals($currentUser) && $currentUser->isAdmin()) {
// 			$userForm->setMaxPower($currentUser->getPower());
// 		}
		
// 		$this->applyBreadcrumbs($userForm);
		
// 		if ($this->dispatch($userForm, 'save')) {
// 			$messageContainer->addInfoCode('user_edited_info', 
// 					array('user' => $userForm->getRocketUser()->getNick()));
// 			$this->commit();
			
// 			$this->redirectToController();
// 			return;
// 		}
		
// 		$this->commit();
// 		$this->forward('..\view\userEdit.html', array('userForm' => $userForm));
// 	}
	
	function getDoUser($userId) {
		$this->verifyAdmin();
		
		if ('text/html' == $this->getRequest()->getAcceptRange()
				->bestMatch(['text/html', 'application/json'])) {
			$this->forward('\rocket\core\view\anglTemplate.html');
			return;
		}
		
		$this->beginTransaction();
		
		$user = $this->lookupUser($userId, true);
		
		$this->commit();
		
		$this->sendJson($user);
	}
	
	private function getPowerOptions() {
		$currentUser = $this->loginContext->getCurrentUser();
		
		$powerOptions = array();
		
		switch ($currentUser->getPower()) {
			case RocketUser::POWER_SUPER_ADMIN:
				$powerOptions[RocketUser::POWER_SUPER_ADMIN] = RocketUser::POWER_SUPER_ADMIN;
			case RocketUser::POWER_ADMIN:
				$powerOptions[RocketUser::POWER_ADMIN] = RocketUser::POWER_ADMIN;
			case RocketUser::POWER_NONE:
				$powerOptions[RocketUser::POWER_NONE] = RocketUser::POWER_NONE;
		}
		
		return $powerOptions;
	}
	
	function getDoChPw(array $params = null) {
		$this->verifyAdmin();
		if ($this->verifyHtml()) {
			return;
		}
		
		throw new PageNotFoundException();
	}
	
	function postDoChPw(ParamBody $body) {
		$this->verifyAdmin();
		
		$this->beginTransaction();
		
		$httpData = $body->parseJsonToHttpData();
		
		$user = $this->lookupUser($httpData->reqInt('userId'), true);
		
		if (!$this->handlePw($user, $httpData)) {
			return;
		}
		
		$this->sendJson(['status' => 'OK', 'user' => $user]);
	}
	
	function putDoUser($userId, ParamBody $body) {
		$this->verifyAdmin();
		
		$this->beginTransaction();
		
		$user = $this->lookupUser($userId, true);
		
		$this->handleUser($user, $body->parseJsonToHttpData());
	}
	
	private function handleUser(RocketUser $user, HttpData $httpData) {
		$valResult = $this->val(Validate::attrs($httpData->toDataMap())
				->props(['username'], Validators::mandatory(), Validators::minlength(3),
						Validators::closure(function($nick, RocketUserDao $userDao) use ($user) {
							if ($user->getNick() === $nick || !$userDao->containsNick($nick)) {
								return;
							}
							
							return Message::createCode('user_nick_already_taken_err', null, 'rocket');
						}))
				->props(['email'], Validators::email())
				->props(['username', 'firstname', 'lastname', 'email'], Validators::maxlength(255))
				->prop('power', Validators::enum($this->getPowerOptions())));
		
		if ($valResult->sendErrJson()) {
			return;
		}
		
		$user->setNick($httpData->reqString('username'));
		$user->setFirstname($httpData->optString('firstname'));
		$user->setLastname($httpData->optString('lastname'));
		$user->setEmail($httpData->optString('email'));
		$user->setPower($httpData->reqString('power'));
		
		$this->rocketUserDao->saveUser($user);
		
		$this->sendJson([
			'user' => $user
		]);
	}


	public function doProfile(MessageContainer $mc) {
		$this->beginTransaction();
			
		$userForm = new RocketUserForm($this->loginContext->getCurrentUser());
		
		if ($this->dispatch($userForm, 'save')) {
// 			$this->userDao->saveUser($userForm->getUser());
			$this->commit();
			
			$mc->addInfoCode('user_profile_saved_info');
			$this->refresh();
			return;
		}
		
		$this->commit();
		$this->forward('..\view\userEdit.html', array('userForm' => $userForm));
	}
	
	public function deleteDoUser($userId) {
		$this->verifyAdmin();
		
		$this->beginTransaction();
		
		$user = $this->rocketUserDao->getUserById($userId);
		if ($user === null) {
			$this->sendJson(['status' => 'OK']);
			return;
		}
		
		$currentUser = $this->loginContext->getCurrentUser();
		if ((!$currentUser->isSuperAdmin()) || $user->equals($currentUser)) {
			$this->sendJson(['status' => 'ERR']);
		}
		
		$this->rocketUserDao->deleteUser($user);
		$this->commit();
		
		$this->sendJson(['status' => 'ERR']);
	}
}

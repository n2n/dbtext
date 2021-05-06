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
namespace rocket\user\model;

use n2n\l10n\MessageContainer;
use n2n\web\dispatch\Dispatchable;
use n2n\context\RequestScoped;
use n2n\reflection\annotation\AnnoInit;
use n2n\context\annotation\AnnoSessionScoped;
use n2n\util\crypt\hash\HashUtils;
use rocket\user\model\security\RocketUserSecurityManager;
use n2n\util\ex\IllegalStateException;
use rocket\user\model\security\SecurityManager;
use rocket\user\bo\RocketUser;

class LoginContext implements RequestScoped, Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('currentUserId', new AnnoSessionScoped());
	}
	
	const MAX_LOGIN_ATTEMPTIONS = 10;
	
	protected $nick;
	protected $rawPassword;
	
	private $currentUser;
	private $currentUserId;
	private $userDao;
	private $securityManager;
	
	private function _init(RocketUserDao $userDao) {
		$this->userDao = $userDao;
	}
	
	private function _onSerialize() {
		$this->userDao = null;
	}
	
	private function _onUnserialize(RocketUserDao $userDao) {
		$this->userDao = $userDao;
	}
	
	public function getNick() {
		return $this->nick;
	}
	
	public function setNick($nick) {
		$this->nick = $nick;
	}
	
	public function setRawPassword($rawPassword) {
		$this->rawPassword = $rawPassword;
	}
	
	public function getRawPassword() {
		return $this->rawPassword;
	}
	
	private function _validation() {
		
	}
	
	/**
	 * @param int $userId
	 * @return boolean
	 */
	public function loginByUserId($userId) {
		$user = $this->userDao->getUserById($userId);
		
		if ($user === null) {
			return false;
		}
		
		$this->currentUserId = $user->getId();
		$this->currentUser = $user;
		return true;
	}
	
	public function login(MessageContainer $messageContainer) {
		if ($this->userDao->getCountOfLatestFailedLoginsForCurrentIp() >= self::MAX_LOGIN_ATTEMPTIONS) {
			$messageContainer->addErrorCode('user_max_attemptions_reached_err');
			return false;
		} 
		
		$currentUser = null;
		if ($this->nick === null) {
			$messageContainer->addErrorCode('user_invalid_login_err');
			return false;
		}
		
		$currentUser = $this->userDao->getUserByNick($this->getNick());
		
		if ($currentUser === null || !HashUtils::compareHash((string) $this->rawPassword, $currentUser->getPassword())) {
			$this->userDao->createLogin($this->getNick(), $this->getRawPassword(), null);
			$messageContainer->addErrorCode('user_invalid_login_err');
			return false;
		}
		
		$this->userDao->createLogin($this->getNick(), $this->getRawPassword(), $currentUser);
		$this->rawPassword = null;
		$this->currentUserId = $currentUser->getId();
		$this->currentUser = $currentUser;
		$this->securityManager = null;
		return true;
	}
	
	public function logout() {
		$this->currentUser = null;
		$this->currentUserId = null;
	}
	
	public function hasCurrentUser() {
		return is_object($this->getCurrentUser());
	}
	
	public function getCurrentUser() {
		if ($this->currentUser !== null) {
			return $this->currentUser;
		}
		
		if ($this->currentUserId === null) {
			return null;
		}
		
		return $this->currentUser = $this->userDao->getUserById($this->currentUserId);
	}
	
	function temporalLogin(RocketUser $currentUser) {
		$this->currentUser = $currentUser;
	}
	
	/**
	 * @param SecurityManager|null $securityManager
	 */
	public function setSecurityManager(?SecurityManager $securityManager) {
		$this->securityManager = $securityManager;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return SecurityManager
	 */
	public function getSecurityManager(): SecurityManager {
		if (!$this->hasCurrentUser()) {
			throw new IllegalStateException('Not sign in');
		}
		
		if ($this->securityManager !== null) {
			return $this->securityManager;
		}
		
		return $this->securityManager = new RocketUserSecurityManager($this->getCurrentUser());
	}
}

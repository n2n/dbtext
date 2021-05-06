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

use rocket\user\bo\RocketUser;
use n2n\persistence\orm\EntityManager;
use n2n\context\RequestScoped;
use rocket\user\bo\Login;
use n2n\core\N2N;
use rocket\user\bo\RocketUserGroup;
use n2n\util\StringUtils;

class RocketUserDao implements RequestScoped {
	/**
	 * @var \n2n\persistence\orm\EntityManager
	 */
	private $em;
	/**
	 * @param \n2n\persistence\orm\EntityManager $em
	 */
	private function _init(EntityManager $em) {
		$this->em = $em;
	}	
	/**
	 * @return \rocket\user\bo\RocketUser[]
	 */
	public function getUsers() {
		return $this->em
				->createSimpleCriteria(RocketUser::getClass(), null, array('id' => 'ASC'))
				->toQuery()->fetchArray();
	}
	/**
	 * @param string $nick
	 * @param string $password
	 * @return \rocket\user\bo\RocketUser
	 */
	public function getUserByNick(string $nick) {
		return $this->em
				->createSimpleCriteria(RocketUser::getClass(), array('nick' => $nick))
				->toQuery()->fetchSingle();
	}
	/**
	 * @param string $nick
	 * @param string $password
	 * @return \rocket\user\bo\RocketUser
	 */
	public function getUserByNickAndPassword($nick, $password) {
		return $this->em
				->createSimpleCriteria(RocketUser::getClass(), array('nick' => $nick, 'password' => $password))
				->toQuery()->fetchSingle();
	} 
	/**
	 * @param int $id
	 * @return \rocket\user\bo\RocketUser
	 */
	public function getUserById($id) {
		return $this->em->find(RocketUser::getClass(), $id);
	}
	/**
	 * @param \rocket\user\bo\RocketUser $user
	 */
	public function saveUser(RocketUser $user) {
		$this->em->persist($user);
	}
	/**
	 * @param RocketUser $user
	 */
	public function deleteUser(RocketUser $user) {
		$this->em->remove($user);
	}
	
	public function saveRocketUserGroup(RocketUserGroup $userGroup) {
		$this->em->persist($userGroup);
	}
	
	/**
	 * @param int $id
	 * @return RocketUserGroup
	 */
	public function getRocketUserGroupById($id) {
		return $this->em->find(RocketUserGroup::getClass(), $id);
	}
	
	public function containsNick($nick): bool {
		return (bool) $this->em->createNqlCriteria('SELECT COUNT(u) FROM RocketUser u WHERE u.nick = :nick', 
						array('nick' => $nick))
				->toQuery()->fetchSingle();
	}
	
	public function getRocketUserGroups() {
		return $this->em->createSimpleCriteria(RocketUserGroup::getClass(), null, array('name' => 'ASC'))
				->toQuery()->fetchArray();
	}
	
	public function removeRocketUserGroup(RocketUserGroup $userGroup) {
		return $this->em->remove($userGroup);
	}
	
// 	public function removeUserSpecGrant(UserSpecGrant $userScriptGrant) {
// 		return $this->em->remove($userScriptGrant);
// 	}
	
	/**
	 * @param string $nick
	 * @param string $rawPassword
	 * @param RocketUser $user
	 */
	public function createLogin($nick, $rawPassword, RocketUser $user = null) {
		$login = new Login();
		$login->setNick(StringUtils::reduce($nick, 255));
// 		$login->setWrongPassword($user !== null ? null : StringUtils::reduce((string) $rawPassword, 255));
		$login->setPower($user !== null ? $user->getPower() : null);
		$login->setSuccessfull($user !== null);
		$login->setIp($_SERVER['REMOTE_ADDR']);
		$login->setDatetime(new \DateTime());
		$this->em->persist($login);
		return $login;
	}
	
	/**
	 * @return \rocket\user\bo\Login[]
	 */
	public function getSuccessfullLogins($limit = null, $num = null) {
		return $this->em->createSimpleCriteria(Login::getClass(), array('successfull' => true), 
				array('dateTime' => 'DESC'), $limit, $num)->toQuery()->fetchArray();
	}
	
	/**
	 * @return \rocket\user\bo\Login[]
	 */
	public function getFailedLogins() {
		return $this->em->createSimpleCriteria(Login::getClass(), array('successfull' => false), 
				array('dateTime' => 'DESC'))->toQuery()->fetchArray();
	}
	
	public function getCountOfLatestFailedLoginsForCurrentIp() {
		$oneHourBefore = new \DateTime();
		$oneHourBefore->sub(new \DateInterval('PT1H'));
		
		$criteria = $this->em->createCriteria();
		$criteria->select('COUNT(l)')
				->from(Login::getClass(), 'l')
				->where(array('l.ip' => $_SERVER['REMOTE_ADDR'], 'l.successfull' => false))
				->andMatch('l.dateTime', '>=', $oneHourBefore);
		return $criteria->toQuery()->fetchSingle();
	}
	
	public function deleteFailedLogins() {
		foreach ($this->getFailedLogins() as $login) {
			$this->em->remove($login);
		}
	}
}

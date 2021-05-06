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
namespace rocket\user\bo;

use n2n\impl\web\dispatch\map\val\ValEmail;
use n2n\reflection\annotation\AnnoInit;
use n2n\util\type\ArgUtils;
use n2n\reflection\ObjectAdapter;
use n2n\web\dispatch\Dispatchable;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use n2n\persistence\orm\annotation\AnnoManyToMany;
use n2n\web\dispatch\map\bind\MappingDefinition;
use n2n\l10n\DynamicTextCollection;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\impl\web\dispatch\map\val\ValNotEmpty;

class RocketUser extends ObjectAdapter implements Dispatchable, \JsonSerializable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('rocket_user'));
		$ai->c(new AnnoDispProperties('nick', 'firstname', 'lastname', 'email'));
		$ai->p('rocketUserGroups', new AnnoManyToMany(RocketUserGroup::getClass()));
	}
	
	const POWER_SUPER_ADMIN = 'superadmin';
	const POWER_ADMIN = 'admin';
	const POWER_NONE = 'none';
	
	private $id;
	private $nick;
	private $password;
	private $firstname;
	private $lastname;
	private $email;
	private $power = self::POWER_NONE;
	private $rocketUserGroups;
	
	public function __construct() {
		$this->rocketUserGroups = new \ArrayObject();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId(int $id) {
		$this->id = $id;
	}
	
	public function getNick() {
		return $this->nick;
	}
	
	public function setNick($nick) {
		$this->nick = $nick;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function setPassword($password) {
		$this->password = $password;
	}
	
	public function getFirstname() {
		return $this->firstname;
	}
	
	public function setFirstname($firstname) {
		$this->firstname = $firstname;
	}
	
	public function getLastname() {
		return $this->lastname;
	}
	
	public function setLastname($lastname) {
		$this->lastname = $lastname;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function setEmail($email) {
		$this->email = $email;
	}
	
	public function setPower(string $power) {
		ArgUtils::valEnum($power, self::getPowers());
		$this->power = $power;
	} 
	
	public function getPower(): string {
		return $this->power;
	}
	
	public function isSuperAdmin() {
		return $this->power == self::POWER_SUPER_ADMIN;
	}
	
	public function isAdmin() {
		return $this->power == self::POWER_ADMIN || $this->isSuperAdmin();
	}
	/**
	 * @param \rocket\user\bo\RocketUserGroup[] $userGroups
	 */
	public function setRocketUserGroups(\ArrayObject $userGroups) {
		$this->rocketUserGroups = $userGroups;
	}
	/**
	 * @return \rocket\user\bo\RocketUserGroup[]
	 */
	public function getRocketUserGroups() {
		return $this->rocketUserGroups;
	}
	
	public static function getPowers() {
		return array(self::POWER_SUPER_ADMIN, self::POWER_ADMIN, self::POWER_NONE);
	}
	
	public function equals($user) {
		return $user instanceof RocketUser && $this->getId() == $user->getId();
	}
	
	public function __toString(): string {
		$str = $this->getFirstname();
		if (null !== ($lastname = $this->getLastname())) {
			if ($str) $str .= ' ';
			$str .= $lastname;
		} 
		
		if (!$str) {
			$str = $this->getNick();
		}
		
		return $str;
	}
	
// 	public function createSecurityManager(N2nContext $n2nContext) {
// 		if ($this->isAdmin()) {
// 			return new FullAccessSecurityManager();
// 		}
		
// 		$securityManager = new ScriptSecurityManager($n2nContext);
// 		foreach ($this->getRocketUserGroups() as $userGroup) {
// 			$securityManager->addAccessibleLaunchPadIds($userGroup->getAccessibleLaunchPadIds());
			
// 			foreach ($userGroup->getUserSpecGrants() as $scriptGrant) {
// 				if ($scriptGrant->isFull()) {
// 					$securityManager->addFullAccessibleSpecId($scriptGrant->getScriptId());
// 				} else {
// 					$securityManager->addScriptGrant($scriptGrant->getScriptId(), $scriptGrant);
// 				}
// 			}
// 		}
		
// 		return $securityManager;
// 	}

	private function _mapping(MappingDefinition $md, DynamicTextCollection $dtc) {
		$md->getMappingResult()->setLabels(array(
				'nick' => $dtc->translate('user_nick_label'),
				'firstname' => $dtc->translate('user_firstname_label'),
				'lastname' => $dtc->translate('user_lastname_label'),
				'email' => $dtc->translate('user_email_label')));
	}
	
	private function _validation(BindingDefinition $bc) { 
		$bc->val('nick', new ValNotEmpty());
		$bc->val('email', new ValEmail());
	}
	
	function jsonSerialize() {
		return [
			'id' => $this->id,
			'username' => $this->nick,
			'email' => $this->email,
			'firstname' => $this->firstname,
			'lastname' => $this->lastname,
			'power' => $this->power
		];
	}
}

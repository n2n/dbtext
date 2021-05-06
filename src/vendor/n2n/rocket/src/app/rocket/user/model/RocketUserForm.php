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

use n2n\impl\web\dispatch\map\val\ValEnum;
use n2n\l10n\Message;
use n2n\web\dispatch\map\bind\BindingErrors;
use rocket\user\bo\RocketUser;
use n2n\web\dispatch\Dispatchable;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\impl\web\dispatch\map\val\ValNotEmpty;
use n2n\web\dispatch\map\bind\MappingDefinition;
use n2n\util\crypt\hash\HashUtils;
use n2n\l10n\DynamicTextCollection;

class RocketUserForm implements Dispatchable {
	protected $rocketUser;
	protected $rawPassword;
	protected $rawPassword2;
	protected $power;
	protected $rocketUserGroupIds = array();
		
	private $availableRocketUserGroups;
	private $maxPower = null;
	
	public function __construct(RocketUser $user, array $availableRocketUserGroups = null) {
		$this->rocketUser = $user;
		$this->power = $user->getPower();
		$this->setAvailableRocketUserGroups($availableRocketUserGroups);
		
		foreach ($this->rocketUser->getRocketUserGroups() as $userGroup) {
			$this->rocketUserGroupIds[$userGroup->getId()] = $userGroup->getId();
		}	
	}
	
	public function setMaxPower(string $maxPower) {
		$this->maxPower = $maxPower;
	}
	
	public function getPowerOptions() {
		if ($this->maxPower === null) return null;
		
		$powerOptions = array();
		
		switch ($this->maxPower) {
			case RocketUser::POWER_SUPER_ADMIN:
				$powerOptions[RocketUser::POWER_SUPER_ADMIN] = RocketUser::POWER_SUPER_ADMIN;
			case RocketUser::POWER_ADMIN:
				$powerOptions[RocketUser::POWER_ADMIN] = RocketUser::POWER_ADMIN;
			case RocketUser::POWER_NONE:
				$powerOptions[RocketUser::POWER_NONE] = RocketUser::POWER_NONE;
		}
		
		return $powerOptions;
	}
		
	public function setAvailableRocketUserGroups(array $availableRocketUserGroups = null) {
		if ($availableRocketUserGroups === null) {
			$this->availableRocketUserGroups = null;
			return;
		}
		
		$this->availableRocketUserGroups = array();
		foreach ($availableRocketUserGroups as $availableRocketUserGroup) {
			$this->availableRocketUserGroups[$availableRocketUserGroup->getId()] = $availableRocketUserGroup;
		}
	}
	
	public function getAvailableRocketUserGroups() {
		return $this->availableRocketUserGroups;
	}
	
	public function isNew() {
		return $this->rocketUser->getId() === null;
	}
	
	public function setRawPassword($rawPassword) {
		$this->rawPassword = $rawPassword;
	}
	
	public function getRawPassword() {
		return $this->rawPassword;
	}
	
	public function setRawPassword2($rawPassword2) {
		$this->rawPassword2 = $rawPassword2;
	}
	
	public function getRawPassword2() {
		return $this->rawPassword2;
	}
	
	public function setRocketUser(RocketUser $rocketUser) {
		$this->rocketUser = $rocketUser;
	}
	
	public function getRocketUser(): RocketUser {
		return $this->rocketUser;
	}
	
	public function setPower($power) {
		$this->power = $power;
	}
	
	public function getPower() {
		return $this->power;
	} 
	
	public function getRocketUserGroupIds() {
		return $this->rocketUserGroupIds;
	}
	
	public function setRocketUserGroupIds(array $userGroupIds) {
		$this->rocketUserGroupIds = $userGroupIds;
	}
	
	private function _mapping(MappingDefinition $md, DynamicTextCollection $dtc) {
		if ($this->maxPower === null) {
			$md->ignore('power');
		}
		
		if ($this->availableRocketUserGroups === null) {
			$md->ignore('rocketUserGroupIds');
		}
		
		$md->getMappingResult()->setLabels(array(
				'rawPassword' => $dtc->translate('user_password_label'),
				'rawPassword2' => $dtc->translate('user_password_confirmation_label'),
				'power' => $dtc->translate('user_power_label'),
				'rocketUserGroupIds' => $dtc->translate('user_assigned_groups_label')));
	}
	
	private function _validation(BindingDefinition $bd) { 
		if ($this->isNew()) {
			$bd->val('rawPassword', new ValNotEmpty());
		}
		
		$that = $this;
		$bd->closure(function($rocketUser, BindingErrors $be, RocketUserDao $userDao) use ($that) {
			if ($that->rocketUser->getNick() != $rocketUser->nick && $userDao->containsNick($rocketUser->nick)) {
				$be->addError('nick', Message::createCodeArg('user_taken_nick_err', array('nick' => $rocketUser->nick)));
			}
		});
		
		$bd->closure(function($rawPassword, $rawPassword2, BindingErrors $be) {
			if ($rawPassword !== $rawPassword2) {
				$be->addError('rawPassword', Message::createCodeArg('user_passwords_do_not_equal_err'));
			}
		});
		
		if ($this->maxPower !== null) {
			$bd->val('power', new ValEnum(array_keys((array) $this->getPowerOptions())));
		}
		
		if ($this->availableRocketUserGroups !== null) {
			$bd->val('rocketUserGroupIds', new ValEnum(array_keys($this->availableRocketUserGroups)));
		}
	}
	
	public function save() {
		if (null !== ($rawPassword = $this->getRawPassword())) {
			$this->rocketUser->setPassword(HashUtils::buildHash($rawPassword));
		}
		
		if ($this->maxPower !== null) {
			$this->rocketUser->setPower($this->getPower());
		}
		
		if ($this->availableRocketUserGroups !== null) {
			$userGroups = new \ArrayObject();
			foreach ($this->rocketUserGroupIds as $userGroupId) {
				$userGroups[] = $this->availableRocketUserGroups[$userGroupId];
			}
			$this->rocketUser->setRocketUserGroups($userGroups);
		}
	}
}

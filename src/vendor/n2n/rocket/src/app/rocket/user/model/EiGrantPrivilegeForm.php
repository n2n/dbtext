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

use n2n\web\dispatch\Dispatchable;
use rocket\user\bo\EiGrantPrivilege;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObject;
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\util\privilege\EiuPrivilegeForm;
use rocket\ei\util\filter\EiuFilterForm;
use rocket\ei\manage\security\privilege\data\PrivilegeSetting;

class EiGrantPrivilegeForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('eiuPrivilegeForm', new AnnoDispObject());
		$ai->p('restrictionEiuFilterForm', new AnnoDispObject(function (EiGrantPrivilegeForm $that) {
			return $that->eiuEngine->newSecurityFilterForm(
					$that->eiPrivilegesGrant->readRestrictionFilterSettingGroup());
		}));
	}
	
	/**
	 * @var EiGrantPrivilege
	 */
	private $eiPrivilegesGrant;
	/**
	 * @var EiuEngine
	 */
	private $eiuEngine;
	
	private $eiuPrivilegeForm;
	private $restrictionEiuFilterForm; 
	
	public function __construct(EiGrantPrivilege $eiGrantPrivilege, EiuEngine $eiuEngine) {
		$this->eiPrivilegesGrant = $eiGrantPrivilege;
		$this->eiuEngine = $eiuEngine;
//		$this->privilegeDefinition = $privilegeDefinition;
		
		if ($eiuEngine->hasPrivileges()) {
			$this->eiuPrivilegeForm = $eiuEngine->newPrivilegeForm($eiGrantPrivilege->getPrivilegeSetting());
		}
		
		if ($eiGrantPrivilege->isRestricted()) {
			$this->restrictionEiuFilterForm = $eiuEngine->newSecurityFilterForm(
					$eiGrantPrivilege->readRestrictionFilterSettingGroup());
		}
	}
	
	public function getEiGrantPrivilege() {
		return $this->eiPrivilegesGrant;
	}
	
	public function isEiuPrivilegeFormAvailable(): bool {
		return $this->eiuPrivilegeForm !== null;
	}
	
	public function getEiuPrivilegeForm() {
		return $this->eiuPrivilegeForm;
	}
	
	public function setEiuPrivilegeForm(?EiuPrivilegeForm $eiuPrivilegeForm) {
		$this->eiuPrivilegeForm = $eiuPrivilegeForm;
	
		if ($eiuPrivilegeForm === null) {
			$this->eiPrivilegesGrant->writeEiPrivilegeDataSet(new PrivilegeSetting());
			return;
		}
		
		$this->eiPrivilegesGrant->setPrivilegeSetting($eiuPrivilegeForm->getSetting());
		
	}
	
	public function isRestricted(): bool {
		return $this->eiPrivilegesGrant->isRestricted();
	}
	
	public function setRestricted(bool $restricted) {
		$this->eiPrivilegesGrant->setRestricted($restricted && $this->areRestrictionsAvailable());
	}
	
	public function getRestrictionEiuFilterForm() {
		return $this->restrictionEiuFilterForm;
	}
	
	public function setRestrictionEiuFilterForm(?EiuFilterForm $restrictionEiuFilterForm) {
		$this->restrictionEiuFilterForm = $restrictionEiuFilterForm;

		if ($restrictionEiuFilterForm === null) {
			$this->eiPrivilegesGrant->setRestricted(false);
		} else {
			$this->eiPrivilegesGrant->setRestricted(true);
			$this->eiPrivilegesGrant->writeRestrictionFilterData($restrictionEiuFilterForm->getSettings());
		}
	}
	
	private function _validation() {
	}
}

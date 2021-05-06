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
namespace rocket\user\model\security;

use rocket\ei\manage\security\filter\SecurityFilterDefinition;
use rocket\user\bo\EiGrantPrivilege;
use rocket\ei\manage\frame\CriteriaConstraint;
use rocket\ei\manage\entry\EiEntryConstraint;
use n2n\util\type\ArgUtils;
use rocket\user\bo\EiGrant;
use rocket\ei\manage\security\privilege\PrivilegeDefinition;
use rocket\ei\EiCommandPath;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\command\EiCommand;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\EiPropPath;

class EiGrantConstraintCache {
	/**
	 * @var EiGrant
	 */
	private $eiGrant;
	/**
	 * @var EiMask
	 */
	private $eiMask;
	/**
	 * @var SecurityFilterDefinition
	 */
	private $securityFilterDefinition;
	
	/**
	 * @var CriteriaConstraint[]
	 */
	private $criteriaConstraints = array();
	/**
	 * @var EiEntryConstraint[]
	 */
	private $eiEntryConstraints = array();
	
	/**
	 * @param EiGrant $eiGrant
	 * @param PrivilegeDefinition $privilegeDefinition
	 * @param SecurityFilterDefinition $securityFilterDefinition
	 */
	function __construct(EiGrant $eiGrant, EiMask $eiMask, ?SecurityFilterDefinition $securityFilterDefinition) {
		ArgUtils::assertTrue($eiGrant->isFull() === ($securityFilterDefinition === null));
		
		$this->eiGrant = $eiGrant;
		$this->eiMask = $eiMask;
		$this->securityFilterDefinition = $securityFilterDefinition;
		
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @return NULL|\rocket\user\model\security\EiCommandAccessResult
	 */
	function testEiCommand(EiCommand $eiCommand) {
		if (!$this->isEiCommandTestable($eiCommand)) {
			return null;
		}
		
		$eiGrantPrivileges = [];
		
		$eiCommandPath = EiCommandPath::from($eiCommand);
		foreach ($this->eiGrant->getEiGrantPrivileges() as $eiGrantPrivilege) {
			if ($eiCommand->isPrivileged() 
					&& !$eiGrantPrivilege->getPrivilegeSetting()->acceptsEiCommandPath($eiCommandPath)) {
					continue;
			}
			
			if (!$eiGrantPrivilege->isRestricted()) {
				return new EiCommandAccessResult(null, null);
			}
			
			$eiGrantPrivilege[] = $eiGrantPrivilege;
		}
		
		
		if (empty($eiGrantPrivileges)) {
			return null;
		}
			
		$criteriaConstraints = [];
		$eiEntryConstraints = [];
		foreach ($eiGrantPrivileges as $eiGrantPrivilege) {
			$criteriaConstraints[] = $this->getCriteriaConstraint($eiGrantPrivilege);
			$eiEntryConstraints[] = $this->getEiEntryConstraint($eiGrantPrivilege);
		}
		
		return new EiCommandAccessResult($criteriaConstraints, $eiEntryConstraints);
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @param EiEntry $eiEntry
	 * @return NULL|\rocket\user\model\security\EiEntryAccessResult
	 */
	function testEiEntry(EiCommand $eiCommand, EiEntry $eiEntry) {
		if (!$this->isEiCommandTestable($eiCommand)) {
			return null;
		}
		
		if ($this->eiGrant->isFull()) {
			return new EiEntryAccessResult(
					$this->eiMask->getEiPropCollection()->getPrivilegedEiPropPaths(),
					$this->eiMask->getEiCommandCollection()->getPrivilegedEiCommandPaths());
		}
		
		$accessibleEiPropPaths = [];
		$executableEiCommandPaths = [];
		$eiCommandPath = EiCommandPath::from($eiCommand);
		foreach ($this->eiGrant->getEiGrantPrivileges() as $eiGrantPrivilege) {
			if ($eiCommand->isPrivileged()
					&& !$eiGrantPrivilege->getPrivilegeSetting()->acceptsEiCommandPath($eiCommandPath)) {
				continue;
			}
					
			if ($eiGrantPrivilege->isRestricted() && !$this->getEiEntryConstraint($eiGrantPrivilege)->check($eiEntry)) {
				continue;
			}
			
			array_push($executableEiCommandPaths, ...$eiGrantPrivilege->getPrivilegeSetting()->getExecutableEiCommandPropPaths());
			array_push($accessibleEiPropPaths, ...$eiGrantPrivilege->getPrivilegeSetting()->getWritableEiPropPaths());
		}
		
		if (empty($accessibleEiPropPaths)) {
			return null;
		}
		
		return new EiEntryAccessResult($accessibleEiPropPaths);
	}
	
	/**
	 * @param EiCommand $eiCommand
	 */
	private function isEiCommandTestable($eiCommand) {
		$eiCommandMask = $eiCommand->getWrapper()->getEiCommandCollection()->getEiMask();
		
		return !$eiCommand->isPrivileged() || $eiCommandMask->equals($this->eiMask)
				|| $this->eiMask->getEiType()->isA($eiCommandMask->getEiType());
	}
	
// 	/**
// 	 * @return EiGrant 
// 	 */
// 	function getEiGrant() {
// 		return $this->eiGrant;
// 	}
	
// 	/**
// 	 * @return \rocket\ei\manage\security\filter\SecurityFilterDefinition
// 	 */
// 	function getSecurityFilterDefinition() {
// 		return $this->securityFilterDefinition;
// 	}

	/**
	 * @param EiGrantPrivilege $eiGrantPrivilege
	 * @return CriteriaConstraint
	 */
	private function getCriteriaConstraint(EiGrantPrivilege $eiGrantPrivilege) {
		ArgUtils::assertTrue($eiGrantPrivilege->isRestricted());
		
		$objHash = spl_object_hash($eiGrantPrivilege);
		
		if (isset($this->criteriaConstraints[$objHash])) {
			return $this->criteriaConstraints[$objHash];
		}
		
		return $this->criteriaConstraints[$objHash] = $this->securityFilterDefinition
				->toFilterDefinition()
				->createCriteriaConstraint($eiGrantPrivilege->readRestrictionFilterSettingGroup());
	}
	
	/**
	 * @param EiGrantPrivilege $eiGrantPrivilege
	 * @return \rocket\ei\manage\entry\EiEntryConstraint
	 */
	private function getEiEntryConstraint(EiGrantPrivilege $eiGrantPrivilege) {
		ArgUtils::assertTrue($eiGrantPrivilege->isRestricted());
		
		$objHash = spl_object_hash($eiGrantPrivilege);
		
		if (isset($this->eiEntryConstraints[$objHash])) {
			return $this->eiEntryConstraints[$objHash];
		}
		
		return $this->eiEntryConstraints[$objHash] = $this->securityFilterDefinition
				->createEiEntryConstraint($eiGrantPrivilege->readRestrictionFilterSettingGroup());
	}
}

class EiCommandAccessResult {
	private $criteriaConstraints;
	private $eiEntryConstraints;
	
	function __construct(?array $criteriaConstraints, ?array $eiEntryConstraints) {
		ArgUtils::assertTrue(($criteriaConstraints === null && $eiEntryConstraints === null)
				|| (!empty($criteriaConstraints) && !empty($eiEntryConstraints)));
		
		$this->criteriaConstraints = $criteriaConstraints;
		$this->eiEntryConstraints = $eiEntryConstraints;
	}
	
	function isRestricted() {
		return $this->criteriaConstraints !== null || $this->eiEntryConstraints !== null;
	}
	
	function getCriteriaConstraints() {
		IllegalStateException::assertTrue($this->criteriaConstraints !== null);
		return $this->criteriaConstraints;
	}
	
	function getEiEntryConstraints() {
		IllegalStateException::assertTrue($this->eiEntryConstraints !== null);
		return $this->eiEntryConstraints;
	}
}

class EiEntryAccessResult {
	/**
	 * @var EiPropPath[]
	 */
	private $writableEiPropPaths;
	/**
	 * @var EiCommandPath[]
	 */
	private $executableEiCommandPaths;
	
	/**
	 * @param EiPropPath[] $writableEiPropPaths
	 * @param EiCommandPath[] $executableEiCommandPaths
	 */
	function __construct(array $writableEiPropPaths, array $executableEiCommandPaths) {
		$this->writableEiPropPaths = $writableEiPropPaths;
		$this->executableEiCommandPaths = $executableEiCommandPaths;
	}
	
	/**
	 * @return \rocket\ei\EiPropPath[]
	 */
	function getWritableEiPropPaths() {
		return $this->writableEiPropPaths;
	}
	
	/**
	 * @return \rocket\ei\EiCommandPath[]
	 */
	function getExecutableEiCommandPaths() {
		return $this->executableEiCommandPaths;
	}
}

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

use rocket\user\bo\RocketUser;
use rocket\ei\component\command\EiCommand;
use rocket\ei\EiCommandPath;
use rocket\ei\EiPropPath;
use rocket\ei\manage\security\EiPermissionManager;
use rocket\spec\TypePath;
use rocket\user\bo\EiGrant;
use rocket\ei\manage\frame\Boundry;
use rocket\ei\manage\security\EiEntryAccess;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\security\EiExecution;
use rocket\ei\manage\critmod\filter\ComparatorConstraintGroup;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;
use rocket\ei\manage\entry\EiEntryConstraint;

class RocketUserEiPermissionManager implements EiPermissionManager {
	/**
	 * @var RocketUser
	 */
	private $rocketUser;
	/**
	 * @var ManageState
	 */
	private $manageState;
	private $eiGrantConstraintCaches = [];
	

	/**
	 * @param RocketUser $rocketUser
	 * @param ManageState $manageState
	 */
	public function __construct(RocketUser $rocketUser, ManageState $manageState) {
		$this->rocketUser = $rocketUser;
		$this->manageState = $manageState;
	}
	
	/**
	 * @param EiMask $eimask
	 * @return EiGrantConstraintCache|null
	 */
	private function getEiGrantConstraintCache($eiMask) {
		$eiTypePathStr = (string) $eiMask->getEiTypePath();
		if (isset($this->eiGrantConstraintCaches[$eiTypePathStr])) {
			return $this->eiGrantConstraintCaches[$eiTypePathStr];
		}
		
		$eiGrant = $this->findEiGrant($eiMask->getEiTypePath());
		
		if ($eiGrant === null) {
			return null;
		}
		
		return $this->eiGrantConstraintCaches[$eiTypePathStr] = new EiGrantConstraintCache($eiGrant,
				($eiGrant->isFull() ? null : $this->managedDef->getSecurityFilterDefinition($eiMask)));
	}

	/**
	 * @param TypePath $eiTypePath
	 * @return EiGrant|NULL
	 */
	private function findEiGrant($eiTypePath) {
		foreach ($this->rocketUser->getRocketUserGroups() as $rocketUserGroup) {
			foreach ($rocketUserGroup->getEiGrants() as $eiGrant) {
				if ($eiGrant->getEiTypePath()->equals($eiTypePath)) {
					return $eiGrant;
				}
			}
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiPermissionManager::isEiCommandAccessible()
	 */
	function isEiCommandAccessible(EiMask $contextEiMask, EiCommand $eiCommand): bool {
		if ($this->rocketUser->isAdmin()) return true;
		
		$eiMask = $eiCommand->getWrapper()->getEiCommandCollection()->getEiMask();
		$privilegeDefinition = $this->manageState->getDef()->getPrivilegeDefinition($eiMask);
		
		$eiGrant = $this->findEiGrant($eiMask->getEiTypePath());
		return null !== $eiGrant && ($eiGrant->isFull()
				|| !$privilegeDefinition->containsEiCommand($eiCommand)
				|| $eiGrant->containsEiCommandPath(EiCommandPath::from($eiCommand)));
	}
	
	function createEiExecution(EiMask $contextEiMask, EiCommand $eiCommand): EiExecution {
		if ($this->rocketUser->isAdmin()) {
			return new FullyGrantedEiExecution($eiCommand);
		}
		
		return $this->createRestrictedEiExecution($contextEiMask, $eiCommand);
		
		
// 		$eiMask = $eiCommand->getWrapper()->getEiCommandCollection()->getEiMask();
// 		$managedDef = $manageState->getDef();
		
		
		
		
// 		$constraintCache = new EiGrantConstraintCache($eiGrant,
// 				$managedDef->getPrivilegeDefinition($eiMask),
// 				$managedDef->getSecurityFilterDefinition($eiMask));
// 		$eiEntryAccessFactory = new RestrictedEiEntryAccessFactory($constraintCache);
// 		foreach ($eiMask->getEiType()->getAllSubEiTypes() as $subEiType) {
// 			$subEiMask = $eiMask->determineEiMask($subEiType);
// 			if (null !== ($subEiGrant = $this->findEiGrant($subEiMask->getEiTypePath()))) {
// 				$eiEntryAccessFactory->addSubEiGrant(new EiGrantConstraintCache($subEiGrant,
// 						$managedDef->getPrivilegeDefinition($subEiMask),
// 						$managedDef->getSecurityFilterDefinition($subEiMask)));
// 			}
// 		}
		
// 		$eiFrame->setEiEntryAccessFactory($eiEntryAccessFactory);
		
		
		
// 		return new RestrictedEiExecution($eiCommand, 
// 				$this->createCriteriaConstraint($eiCommand, $constraintCache), 
// 				$this->createEiEntryConstraint($eiCommand, $constraintCache),
// 				$eiEntryAccessFactory);
		
// 		$eiFrame->setEiExecution($ree);
// 		$eiFrame->getBoundry()->addCriteriaConstraint(Boundry::TYPE_SECURITY, $ree->getCriteriaConstraint());
// 		$eiFrame->getBoundry()->addEiEntryConstraint(Boundry::TYPE_SECURITY, $ree->getEiEntryConstraint());
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param EiCommand $eiCommand
	 * @return RestrictedEiExecution
	 */
	private function createRestrictedEiExecution($contextEiMask, $eiCommand) {
		$comparatorConstraints = [];
		$eiEntryConstraints = [];
		
		foreach ($contextEiMask->getEiType()->getAllSuperEiType(true) as $eiType) {
			$eiMask = $contextEiMask->determineEiMask($eiType);
			$eiGrantConstraintCache = $this->getEiGrantConstraintCache($eiMask);
			
			if ($eiGrantConstraintCache === null) {
				continue;
			}
			
			$eiCommandAccess = $eiGrantConstraintCache->testEiCommand($eiCommand);
			if ($eiCommandAccess === null) {
				continue;
			}
			
			if (!$eiCommandAccess->isRestricted()) {
				return new RestrictedEiExecution($eiCommand, null, null);
			}
			
			array_push($comparatorConstraints, ...$eiCommandAccess->getCriteriaConstraints());
			array_push($eiEntryConstraints, ...$eiCommandAccess->getEiEntryConstraints());
		}
		
		if (empty($comparatorConstraints) || empty($eiEntryConstraints)) {
			throw new InaccessibleEiCommandPathException($eiCommand . ' inaccessible.');
		}
		
		return new RestrictedEiExecution($eiCommand,
				new ComparatorConstraintGroup(false, $comparatorConstraints), 
				new EiEntryConstraintGroup(false, $eiEntryConstraints));		
	}
	
	private function createEiEntryAccessFactory($contextEiMask, $eiCommand) {
		$eiEntryAccessFactory = new RestrictedEiEntryAccessFactory();
		
		foreach ($contextEiMask->getEiType()->getAllSuperEiTypes(true) as $eiType) {
			$eiMask = $contextEiMask->determineEiMask($eiType);
			if (null !== ($eiGrantConstraintCache = $this->getEiGrantConstraintCache($eiMask))) {
				$eiEntryAccessFactory->addEiGrantConstraintCache($eiGrantConstraintCache);
			}
		}
		
		foreach ($contextEiMask->getEiType()->getAllSubEiTypes(false) as $eiType) {
			$eiMask = $contextEiMask->determineEiMask($eiType);
			if (null !== ($eiGrantConstraintCache = $this->getEiGrantConstraintCache($eiMask))) {
				$eiEntryAccessFactory->addEiGrantConstraintCache($eiGrantConstraintCache);
			}
		}
		
		return $eiEntryAccessFactory;
	}
}



class RestrictedEiEntryAccess implements EiEntryAccess {
	/**
	 * @var EiEntryConstraint
	 */
	private $eiEntryConstraint;
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
	function __construct(EiEntryConstraint $eiEntryConstraint, array $writableEiPropPaths, array $executableEiCommandPaths) {
		$this->eiEntryConstraint = $eiEntryConstraint;
		
		foreach ($writableEiPropPaths as $writableEiPropPath) {
			$this->writableEiPropPaths[(string) $writableEiPropPath] = $writableEiPropPath;
		}
		
		foreach ($executableEiCommandPaths as $executableEiCommandPath) {
			$this->executableEiCommandPaths[(string) $executableEiCommandPath] = $executableEiCommandPath;
		}
	}
	
	function getEiEntryConstraint(): ?EiEntryConstraint {
		return $this->eiEntryConstraint;
	}
	
	function isEiPropWritable(EiPropPath $eiPropPath): bool {
		return isset($this->writableEiPropPaths[(string) $eiPropPath]);
	}

	function isEiCommandExecutable(EiCommandPath $eiCommandPath): bool {
		return isset($this->executableEiCommandPaths[(string) $eiCommandPath]);
	}
}

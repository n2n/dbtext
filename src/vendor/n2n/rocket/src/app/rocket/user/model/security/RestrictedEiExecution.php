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

use rocket\ei\manage\security\EiEntryAccess;
use rocket\ei\manage\security\EiExecution;
use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\security\filter\SecurityFilterDefinition;
use rocket\ei\EiCommandPath;
use rocket\ei\manage\security\privilege\PrivilegeDefinition;
use rocket\ei\manage\frame\CriteriaConstraint;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\entry\EiEntryConstraint;
use rocket\ei\manage\critmod\filter\ComparatorConstraint;

class RestrictedEiExecution implements EiExecution {
	private $eiCommand;
	private $comparatorConstraint;
	private $eiEntryConstraint;
	private $restrictedEiEntryAccessFactory;

	/**
	 * @param EiCommand|null $eiCommand
	 * @param EiCommandPath $eiCommandPath
	 * @param array $eiGrantPrivileges
	 * @param PrivilegeDefinition $privilegeDefinition
	 * @param SecurityFilterDefinition $securityFilterDefinition
	 */
	function __construct(EiCommand $eiCommand, ?ComparatorConstraint $comparatorConstraint, ?EiEntryConstraint $eiEntryConstraint,
			RestrictedEiEntryAccessFactory $restrictedEiEntryAccessFactory) {
		$this->eiCommand = $eiCommand;
		$this->comparatorConstraint = $comparatorConstraint;
		$this->eiEntryConstraint = $eiEntryConstraint;
		$this->restrictedEiEntryAccessFactory = $restrictedEiEntryAccessFactory;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::getEiCommand()
	 */
	function getEiCommand(): EiCommand {
		return $this->eiCommand;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::getCriteriaConstraint()
	 */
	function getCriteriaConstraint(): ?CriteriaConstraint {
		return $this->comparatorConstraint;
	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\manage\security\EiExecution::getEiEntryConstraint()
// 	 */
// 	function getEiEntryConstraint(): ?EiEntryConstraint {
// 		return $this->eiEntryConstraint;
// 	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\security\EiExecution::createEiEntryAccess()
	 */
	function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
		return $this->restrictedEiEntryAccessFactory->createEiEntryAccess($this->eiEntryConstraint, $eiEntry);
	}
}

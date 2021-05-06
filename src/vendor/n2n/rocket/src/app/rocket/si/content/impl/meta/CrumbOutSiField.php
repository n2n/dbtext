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
namespace rocket\si\content\impl\meta;

use n2n\util\type\ArgUtils;
use rocket\si\content\impl\OutSiFieldAdapter;

class CrumbOutSiField extends OutSiFieldAdapter {
	private $groups = [];
	
	function __construct() {
	}
	
	/**
	 * @return \rocket\si\content\impl\meta\SiCrumbGroup[]
	 */
	function getGroups() {
		return $this->groups;
	}
	
	/**
	 * @param SiCrumbGroup[] $groups
	 * @return self
	 */
	function setGroups($groups) {
		ArgUtils::valArray($groups, SiCrumbGroup::class);
		$this->groups = array_values($groups);
		return $this;
	}
	
	/**
	 * @param SiCrumbGroup ...$group
	 * @return \rocket\si\content\impl\meta\CrumbOutSiField
	 */
	function addGroup(SiCrumbGroup ...$groups) {
		array_push($this->groups, ...$groups);
		return $this;
	}
	
	/**
	 * @param SiCrumbGroup[] $groups
	 * @return \rocket\si\content\impl\meta\CrumbOutSiField
	 */
	function addGroups(array $groups) {
		ArgUtils::valArray($groups, SiCrumbGroup::class);
		array_push($this->groups, ...$groups);
		return $this;
	}
	
	/**
	 * @param SiCrumb[] $crumbs
	 * @return \rocket\si\content\impl\meta\CrumbOutSiField
	 */
	function addNewGroup(array $crumbs) {
		return $this->addGroup(new SiCrumbGroup($crumbs));
	}
	
	/**
	 * @param SiCrumb ...$crumbs
	 * @return \rocket\si\content\impl\meta\CrumbOutSiField
	 */
	function addCrumbsAsGroup(SiCrumb ...$crumbs) {
		return $this->addGroup(new SiCrumbGroup($crumbs));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'crumb-out';
	}
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'crumbGroups' => $this->groups
		];
	}
}

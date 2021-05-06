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

use rocket\spec\Spec;
use rocket\user\bo\EiGrant;

class RocketUserGroupListModel {
	private $userGroups;
	private $spec;
	
	public function __construct(array $userGroups, Spec $spec) {
		$this->userGroups = $userGroups;
		$this->spec = $spec;
	}
	
	public function getRocketUserGroups() {
		return $this->userGroups;
	}
	
	/**
	 * @param EiGrant $eiGrant
	 * @return \n2n\l10n\Lstr
	 */
	public function prettyEiGrantName(EiGrant $eiGrant) {
		$eiTypePath = $eiGrant->getEiTypePath();
		$eiType = $this->spec->getEiTypeById($eiTypePath->getTypeId());
		
		if (null !== ($exId = $eiTypePath->getEiTypeExtensionId())) {
			return $eiType->getEiTypeExtensionCollection()->getById($exId)->getEiMask()->getLabelLstr();
		}
		
		return $eiType->getEiMask()->getLabelLstr();
	}
}

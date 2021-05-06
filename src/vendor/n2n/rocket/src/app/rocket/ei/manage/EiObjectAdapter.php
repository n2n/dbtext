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
namespace rocket\ei\manage;

use rocket\ei\manage\draft\DraftValueMap;
use rocket\si\content\SiEntryIdentifier;

abstract class EiObjectAdapter implements EiObject {
	public function getLiveObject() {
		return $this->getEiEntityObj()->getEntityObj();
	}

	public function getDraftValueMap(): DraftValueMap {
		return $this->getDraft()->getDraftValueMap();
	}

	public function equals($obj) {
		if (!($obj instanceof EiObject && $this->isDraft() === $obj->isDraft()
				&& $this->getEiEntityObj()->getId() === $obj->getEiEntityObj()->getId())) {
			return false;
		}

		if ($this->isDraft()) {
			return $this->getDraft()->getId() === $obj->getDraft()->getId();
		}

		return true;
	}
	
	public function createSiEntryIdentifier(): SiEntryIdentifier {
		$eiEntityObj = $this->getEiEntityObj();
		$pid = null;
		if ($eiEntityObj->hasId()) {
			$pid = $eiEntityObj->getPid();
		}
		
		$eiType = $eiEntityObj->getEiType();
		return new SiEntryIdentifier($eiType->getSupremeEiType()->getId(), $eiType->getId(), $pid);
	}
}

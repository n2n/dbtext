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

use rocket\ei\manage\draft\Draft;

class DraftEiObject extends EiObjectAdapter {
	private $draft;

	public function __construct(Draft $draft) {
		$this->draft = $draft;
	}

	public function getEiEntityObj(): EiEntityObj {
		return $this->draft->getEiEntityObj();
	}
	
	public function isNew(): bool {
		return $this->draft->isNew();
	}

	public function isDraft(): bool {
		return true;
	}

	public function getDraft(): Draft {
		return $this->draft;
	}
	
// 	public function toEntryNavPoint(): EntryNavPoint {
// 		$liveId = null;
// 		if ($this->getEiEntityObj()->isPersistent()) {
// 			$liveId = $this->getEiEntityObj()->getId();
// 		}
// 		return new EntryNavPoint($liveId, $this->draft->getId());
// 	}
}

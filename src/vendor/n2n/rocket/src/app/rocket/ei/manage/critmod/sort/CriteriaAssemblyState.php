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
namespace rocket\ei\manage\critmod\sort;

use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use rocket\ei\EiPropPath;
use n2n\util\ex\IllegalStateException;
class CriteriaAssemblyState {
	private $criteria;
	private $baseCp;
	private $cps = array();

	public function __construct(Criteria $criteria) {
		$this->criteria = $criteria;
	}

	public function getCriteria(): Criteria {
		return $this->criteria;
	}

	public function registerCp(EiPropPath $eiPropPath, CriteriaProperty $criteriaProperty) {
		if ($eiPropPath->isEmpty()) {
			IllegalStateException::assertTrue($this->baseCp === null);
			$this->baseCp = $criteriaProperty;
		}

		$eiPropPathStr = (string) $eiPropPath;
		IllegalStateException::assertTrue(!isset($this->cps[$eiPropPathStr]));
		$this->cps[$eiPropPathStr] = $criteriaProperty;
	}

	public function containsCpEiPropPath(EiPropPath $eiPropPath): bool {
		if ($eiPropPath->isEmpty()) {
			return $this->baseCp !== null;
		}

		return isset($this->cps[(string) $eiPropPath]);
	}

	public function getCp(EiPropPath $eiPropPath): CriteriaProperty {
		if ($eiPropPath->isEmpty()) {
			IllegalStateException::assertTrue($this->baseCp !== null);
			return $this->baseCp;
		}

		$eiPropPathStr = (string) $eiPropPath;
		IllegalStateException::assertTrue(isset($this->cps[$eiPropPathStr]));
		return $this->cps[$eiPropPathStr];
	}
}

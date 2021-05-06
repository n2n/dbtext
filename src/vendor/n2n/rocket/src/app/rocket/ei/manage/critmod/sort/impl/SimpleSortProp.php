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
namespace rocket\ei\manage\critmod\sort\impl;

use rocket\ei\manage\critmod\sort\SortProp;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\l10n\Lstr;
use n2n\l10n\N2nLocale;
use rocket\ei\manage\critmod\sort\SimpleSortConstraint;
use rocket\ei\manage\critmod\sort\SortConstraint;

class SimpleSortProp implements SortProp {
	protected $criteriaProperty;
	protected $labelLstr;
	
	public function __construct(CriteriaProperty $criteriaProperty, $labelLstr) {
		$this->criteriaProperty = $criteriaProperty;
		$this->labelLstr = Lstr::create($labelLstr);
	}
	
	public function getLabel(N2nLocale $n2nLocale): string {
		return $this->labelLstr->t($n2nLocale);
	}
	
	public function createSortConstraint(string $direction): SortConstraint {
		return new SimpleSortConstraint($this->criteriaProperty, $direction);
	}
}

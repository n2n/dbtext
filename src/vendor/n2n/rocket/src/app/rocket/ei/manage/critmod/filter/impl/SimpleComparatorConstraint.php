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
namespace rocket\ei\manage\critmod\filter\impl;

use rocket\ei\manage\critmod\filter\ComparatorConstraint;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\persistence\orm\criteria\item\CriteriaItem;

class SimpleComparatorConstraint implements ComparatorConstraint {
	private $ci1;
	private $operator;
	private $ci2;
	
	/**
	 * @param CriteriaItem $ci1
	 * @param string $operator
	 * @param CriteriaItem $ci2
	 */
	public function __construct(CriteriaItem $ci1, string $operator, CriteriaItem $ci2) {
		$this->ci1 = $ci1;
		$this->operator = $operator;
		$this->ci2 = $ci2;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\critmod\filter\ComparatorConstraint::applyToCriteriaComparator()
	 */
	public function applyToCriteriaComparator(CriteriaComparator $criteriaComparator, CriteriaProperty $alias) {
		$criteriaComparator->match($this->ci1, $this->operator, $this->ci2);
	}
}

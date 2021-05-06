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
namespace rocket\ei\manage\critmod\quick\impl;

use rocket\ei\manage\critmod\quick\QuickSearchProp;
use n2n\persistence\orm\criteria\item\CriteriaItem;
use rocket\ei\manage\critmod\filter\impl\SimpleComparatorConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;

class SimpleQuickSearchProp implements QuickSearchProp {
	private $criteriaItem;
	private $operator;
	
	public function __construct(CriteriaItem $criteriaItem, string $operator = CriteriaComparator::OPERATOR_LIKE) {
		$this->criteriaItem = $criteriaItem;
		$this->operator = $operator;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\critmod\quick\QuickSearchProp::createComparatorConstraint($queryStr)
	 */
	public function buildComparatorConstraint(string $queryStr) {
		return new SimpleComparatorConstraint($this->criteriaItem, $this->operator, CrIt::c($queryStr));
	}
}

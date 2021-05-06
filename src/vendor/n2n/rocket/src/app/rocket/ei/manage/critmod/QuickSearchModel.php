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
// namespace rocket\ei\manage\critmod;

// use n2n\util\type\ArgUtils;
// use rocket\ei\manage\critmod\quick\QuickSearchable;

// class QuickSearchModel {
// 	private $quickSearchables = array();
	
// 	public function addQuickSearchable(QuickSearchable $quickSearchable) {
// 		$this->qickSearchables[] = $quickSearchable;
// 	}
	
// 	public function createCriteriaConstraint($searchStr) {
// 		if (!mb_strlen($searchStr)) return null;
		
// 		$filterConstraint = new FilterCriteriaConstraint();
		
// 		$searchStrParts = preg_split('/[\s]+/', $searchStr);
// 		foreach ($searchStrParts as $key => $searchStrPart) {
// 			if (!mb_strlen($searchStrPart)) continue;
				
// 			$group = new CriteriaComparatorConstraintGroup(false);
				
// 			foreach ($this->qickSearchables as $quickSearchable) {
// 				$comparatorConstraint = $quickSearchable->createQuickSearchComparatorConstraint($searchStrPart);
// 				ArgUtils::valTypeReturn($comparatorConstraint, 'rocket\ei\manage\critmod\ComparatorConstraint', 
// 						$quickSearchable, 'createQuickSearchComparatorConstraint');
// 				$group->addComparatorConstraint($comparatorConstraint);
// 			}
			
// 			$filterConstraint->addComparatorConstraint($group);
// 		}
		
// 		if ($filterConstraint->isEmpty()) return null;
		
// 		return $filterConstraint;
// 	}
// }

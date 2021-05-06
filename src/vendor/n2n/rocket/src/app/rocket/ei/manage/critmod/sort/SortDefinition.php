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

use rocket\ei\EiPropPath;
use n2n\util\col\ArrayUtils;

class SortDefinition {
	private $sortProps = array();
	private $sortPropForks = array();
	
	public function putSortProp(EiPropPath $eiPropPath, SortProp $sortProp) {
// 		ArgUtils::assertTrue(!EiPropPath::constainsSpecialIdChars($id), 'Invalid id.');
		$this->sortProps[(string) $eiPropPath] = $sortProp;	
	}
	
	public function containsEiPropPath(EiPropPath $eiPropPath): bool {
		return isset($this->sortProps[(string) $eiPropPath]);
	}
	
	public function getSortProps(): array {
		return $this->sortProps;
	}
	
// 	public function setSortItems(array $sortProps) {
// 		$this->sortProps = $sortProps;
// 	}

	public function containsSortPropFork(EiPropPath $eiPropPath): bool {
		return isset($this->sortPropForks[(string) $eiPropPath]);
	}

	public function putSortPropFork(EiPropPath $eiPropPath, SortPropFork $sortPropFork) {
		$this->sortPropForks[(string) $eiPropPath] = $sortPropFork;
	}
	
	public function getSortPropForks(): array {
		return $this->sortPropForks;
	}
	
	public function getAllSortProps(): array {
		$sortProps = $this->sortProps;
		
		foreach ($this->sortPropForks as $forkId => $sortPropFork) {
			$forkEiPropPath = EiPropPath::create($forkId);
			foreach ($sortPropFork->getForkedSortDefinition()->getAllSortProps() as $id => $sortProp) {
				$forkEiPropPath->ext(EiPropPath::create($id));
			}
		}
		
		return $sortProps;
	}
	
	public function createCriteriaConstraint(SortSettingGroup $sortData) {
		$sortConstraints = array();
					
		foreach ($sortData->getSortSettings() as $sortItemData) {
			$sortConstraint = $this->buildSortCriteriaConstraint( 
					EiPropPath::create($sortItemData->getSortPropId())->toArray(), $sortItemData->getDirection());
			if ($sortConstraint !== null) {
				$sortConstraints[] = $sortConstraint;
			}
		}
		
		return new SortCriteriaConstraintGroup($sortConstraints);
	}
	
	protected function buildSortCriteriaConstraint(array $nextIds, string $direction) {
		$id = ArrayUtils::shift($nextIds, true);
		
		if (empty($nextIds)) {
			if (!isset($this->sortProps[$id])) return null;
			
			return $this->sortProps[$id]->createSortConstraint($direction);
		}		

		if (!isset($this->sortPropForks[$id])) return null;

		$forkedSortConstraint = $this->sortPropForks[$id]->getForkedSortDefinition()
				->buildSortCriteriaConstraint($nextIds, $direction);
		if ($forkedSortConstraint !== null) {
			return $this->sortPropForks[$id]->createSortConstraint($forkedSortConstraint);
		}
		
		return null;
	}
}

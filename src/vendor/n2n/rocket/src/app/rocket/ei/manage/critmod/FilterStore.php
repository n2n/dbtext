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

// use rocket\ei\manage\critmod\Filter;
// use n2n\context\RequestScoped;
// use n2n\persistence\orm\EntityManager;
// use n2n\persistence\orm\OrmUtils;
// use rocket\ei\manage\critmod\filter\data\FilterData;

// class FilterStore implements RequestScoped {
// 	private $em;
	
// 	private function _init(EntityManager $em) {
// 		$this->em = $em;
// 	}
	
// 	public function containsFilterName($eiTypeId, $filterName) {
// 		return OrmUtils::createCountCriteria($this->em, Filter::getClass(), array('name' => $filterName))
// 				->fetchSingle();
// 	}
	
// 	public function getFiltersById($eiTypeId) {
// 		return $this->em
// 				->createSimpleCriteria(Filter::getClass(), array('eiTypeId' => $eiTypeId))
// 				->toQuery()->fetchArray();
// 	}
		
// // 	public function getFilterNames(EiFrame $eiFrame) {
// // 		$scriptId = $eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getId();
// // 		if (isset($this->filterDatas[$scriptId])) {
// // 			return array_keys($this->filterDatas[$scriptId]);
// // 		}	
		
// // 		return array();
// // 	}

// 	public function createFilter($eiTypeId, $name, FilterData $filterData, array $orderDirections) {
// 		$filter = new Filter();
// 		$filter->setId($eiTypeId);
// 		$filter->setName($name);
// 		$filter->writeFilterData($filterData);
// 		$filter->setSortDirections($orderDirections);
// 		$this->em->persist($filter);
// 		$this->em->flush();
// 		return $filter;
// 	}

// 	public function mergeFilter(Filter $filter) {
// 		return $this->em->merge($filter);
// 	}
	
// 	public function removeFilter(Filter $filter) {
// 		$this->em->remove($filter);
// 	}
	
// // 	public function removeFilterDataByFilterName(EiFrame $eiFrame, $filterName) {
// // 		$scriptId = $eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getId();
		
// // 		if (isset($this->filterDatas[$scriptId])) {
// // 			unset($this->filterDatas[$scriptId][$filterName]);
// // 			$this->persist();
// // 		}
// // 	}
	
// // 	private function persist() {
// // 		IoUtils::putContentsSafe($this->filtersFilePath,
// // 				serialize($this->filterDatas));
// // 	}
// }

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
namespace rocket\ei\component;

use rocket\ei\component\prop\SortableEiProp;
use rocket\ei\component\prop\EiPropCollection;
use rocket\ei\component\modificator\EiModificatorCollection;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\component\prop\FilterableEiProp;
use n2n\core\container\N2nContext;
use rocket\ei\EiPropPath;
use rocket\ei\manage\critmod\sort\SortDefinition;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\critmod\sort\SortProp;
use rocket\ei\component\prop\SortableEiPropFork;
use rocket\ei\manage\critmod\sort\SortPropFork;
use rocket\ei\manage\critmod\filter\FilterProp;
use rocket\ei\component\prop\QuickSearchableEiProp;
use rocket\ei\manage\critmod\quick\QuickSearchDefinition;
use rocket\ei\util\Eiu;

class CritmodFactory {
	private $eiPropCollection;
	private $eiModificatorCollection;
	
	public function __construct(EiPropCollection $eiPropCollection, EiModificatorCollection $eiModificatorCollection) {
		$this->eiPropCollection = $eiPropCollection;
		$this->eiModificatorCollection = $eiModificatorCollection;
	}
	
// 	public static function createFilterModel(EiType $eiType, N2nContext $n2nContext) {
// 		return self::createFilterModelInstance($eiType, $n2nContext);
// 	}
	
// 	public static function createFilterModelFromEiFrame(EiFrame $eiFrame) {
// 		return self::createFilterModelInstance($eiFrame->getContextEiEngine()->getEiMask()->getEiType(), 
// 				$eiFrame->getN2nContext(), $eiFrame);
// 	}
	
	public function createFramedFilterDefinition(EiFrame $eiFrame): FilterDefinition {
		$eiu = new Eiu($eiFrame);
		
		$filterDefinition = new FilterDefinition();
		foreach ($this->eiPropCollection as $id => $eiProp) {
			if (!($eiProp instanceof FilterableEiProp)) continue;
			
			$filterProp = $eiProp->buildFilterProp($eiu);
			ArgUtils::valTypeReturn($filterProp, FilterProp::class, $eiProp, 'buildManagedFilterProp', true);
			
			if ($filterProp !== null) {
				$filterDefinition->putFilterProp($id, $filterProp);
			}
		}		
		return $filterDefinition;
	}
	
	public function createFilterDefinition(N2nContext $n2nContext): FilterDefinition {
		$eiu = new Eiu($n2nContext);
		
		$filterDefinition = new FilterDefinition();
		foreach ($this->eiPropCollection as $id => $eiProp) {
			if (!($eiProp instanceof FilterableEiProp)) continue;
			
			$filterProp = $eiProp->buildFilterProp($eiu);
			ArgUtils::valTypeReturn($filterProp, FilterProp::class, $eiProp, 'buildFilterProp', true);
			
			if ($filterProp !== null) {
				$filterDefinition->putFilterProp($id, $filterProp);
			}
		}
		return $filterDefinition;
	}
	
	public function createFramedSortDefinition(EiFrame $eiFrame): SortDefinition {
		$eiu = new Eiu($eiFrame);
		$sortDefinition = new SortDefinition();
		
		foreach ($this->eiPropCollection as $eiPropPathStr => $eiProp) {
			if ($eiProp instanceof SortableEiProp) {
				if (null !== ($sortProp = $eiProp->buildSortProp($eiu))) {
					ArgUtils::valTypeReturn($sortProp, SortProp::class, $eiProp, 'buildSortProp', true);
					$sortDefinition->putSortProp(EiPropPath::create($eiPropPathStr), $sortProp);
				}
			}
			
			if ($eiProp instanceof SortableEiPropFork) {
				if (null !== ($sortPropFork = $eiProp->buildSortPropFork($eiu))) {
					ArgUtils::valTypeReturn($sortPropFork, SortPropFork::class, $eiProp, 'buildSortPropFork', true);
					$sortDefinition->putSortPropFork(EiPropPath::create($eiPropPathStr), $sortPropFork);
				}
			}
		}
		
		return $sortDefinition;
	}
	
	public function createSortDefinition(N2nContext $n2nContext): SortDefinition {
		$eiu = new Eiu($n2nContext);
		$sortDefinition = new SortDefinition();
		
		foreach ($this->eiPropCollection as $eiPropPathStr => $eiProp) {
			if ($eiProp instanceof SortableEiProp) {
				if (null !== ($sortProp = $eiProp->buildSortProp($eiu))) {
					ArgUtils::valTypeReturn($sortProp, SortProp::class, $eiProp, 'buildSortProp', true);
					$sortDefinition->putSortProp(EiPropPath::create($eiPropPathStr), $sortProp);
				}
			}
			
			if ($eiProp instanceof SortableEiPropFork) {
				if (null !== ($sortPropFork = $eiProp->buildSortPropFork($eiu))) {
					ArgUtils::valTypeReturn($sortPropFork, SortPropFork::class, $eiProp, 'buildSortPropFork', true);
					$sortDefinition->putSortPropFork(EiPropPath::create($eiPropPathStr), $sortPropFork);
				}
			}
		}
		
		return $sortDefinition;
	}
	
	public function createFramedQuickSearchDefinition(EiFrame $eiFrame) {
		$eiu = new Eiu($eiFrame);
		$quickSearchDefinition = new QuickSearchDefinition($this->eiPropCollection->getEiMask());
	
		foreach ($this->eiPropCollection as $id => $eiProp) {
			if (!($eiProp instanceof QuickSearchableEiProp)) continue;
				
			if (null !== ($quickSearchField = $eiProp->buildQuickSearchProp($eiu))) {
				$quickSearchDefinition->putQuickSearchProp(EiPropPath::from($eiProp), $quickSearchField);
			}
		}
	
		return $quickSearchDefinition;
	}
	
// 	public static function createSortModelFromEiFrame(EiFrame $eiFrame) {
// 		return self::createSortModelInstance($eiFrame->getContextEiEngine()->getEiMask()->getEiType(), $eiFrame->getN2nContext());
// 	}
	
// 	public static function createSortModel() {
// 		$sortModel = new SortModel();
// 		foreach ($this->eiPropCollection as $id => $eiProp) {
// 			if (!($eiProp instanceof SortableEiProp)) continue;
			
// 			if (null !== ($sortItem = $eiProp->getSortItem())) {
// 				$sortModel->putSortItem($id, $eiProp->getSortItem());
// 			}
			
// 			if (null !== ($sortItemFork = $eiProp->getSortItemFork())) {
// 				$sortModel->putSortItemFork($id, $eiProp->getSortItemFork());
// 			}
// 		}
// 		return $sortModel;
// 	}
		
// 	public static function createQuickSearchableModel(EiFrame $eiFrame) {
// 		$quickSerachModel = new QuickSearchModel();
// 		foreach ($eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getEiPropCollection() as $field) {
// 			if ($field instanceof QuickSearchableEiProp) {
// 				$quickSerachModel->addQuickSearchable($field);
// 			}
// 		}
// 		return $quickSerachModel;
// 	}
}

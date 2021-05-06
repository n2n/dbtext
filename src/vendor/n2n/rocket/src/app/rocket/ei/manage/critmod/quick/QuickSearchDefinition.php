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
namespace rocket\ei\manage\critmod\quick;

use n2n\util\type\ArgUtils;
use rocket\ei\EiPropPath;
use rocket\ei\manage\critmod\filter\ComparatorConstraintGroup;
use rocket\ei\manage\DefPropPath;
use rocket\ei\mask\EiMask;
use n2n\util\thread\RecursionAsserters;

class QuickSearchDefinition {
	private $eiMask;
	private $quickSearchProps = [];
	private $quickSearchPropForks = [];
	
	function __construct(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param QuickSearchProp $quickSearchField
	 */
	function putQuickSearchProp(EiPropPath $eiPropPath, QuickSearchProp $quickSearchField) {
		$this->quickSearchProps[(string) $eiPropPath] = $quickSearchField;	
	}
	
	function putQuickSearchPropFork(EiPropPath $eiPropPath, QuickSearchPropFork $quickSearchPropFork) {
		$this->quickSearchPropForks[(string) $eiPropPath] = $quickSearchPropFork;
	}
	
	/**
	 * @return QuickSearchProp[]
	 */
	function getQuickSearchProps() {
		return $this->quickSearchProps;
	}
	
	/**
	 * @return QuickSearchPropFork[]
	 */
	function getQuickSearchPropForks() {
		return $this->quickSearchPropForks;
	}
	
	/**
	 * @param EiPropPath[] $eiPropPaths
	 * @return QuickSearchProp[]
	 */
	private function filterProps(array $eiPropPaths) {
		$quickSearchFields = array();
		foreach ($eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			if (isset($this->quickSearchProps[$eiPropPathStr])) {
				$quickSearchFields[] = $this->quickSearchProps[$eiPropPathStr];
			}
		}
		return $quickSearchFields;
	}
	
	const SEARCH_STR_WHITESPACS_SPLIT_LIMIT = 9;
	
	/**
	 * 
	 * @param string $searchStr
	 * @param DefPropPath[] $eiPropPaths
	 * @return null|\rocket\ei\manage\critmod\filter\ComparatorConstraintGroup
	 */
	public function buildCriteriaConstraint(string $searchStr, array $defPropPaths = null) {
		if (!RecursionAsserters::unique(self::class)->tryPush((string) $this->eiMask->getEiTypePath())) {
			return;
		}
		
		$quickSearchProps = null;
		if ($defPropPaths === null) {
			$quickSearchProps = $this->quickSearchProps;
		} else {
			ArgUtils::valArray($defPropPaths, DefPropPath::class);
			$quickSearchProps = $this->filterProps(array_map(function ($dpp) { 
				return $dpp->getFirstEiPropPath(); 
			}, $defPropPaths));
		} 
		
		if (empty($quickSearchProps)) {
			RecursionAsserters::unique(self::class)->pop((string) $this->eiMask->getEiTypePath());
			return null;
		}
		
		$comparatorConstraintGroup = new ComparatorConstraintGroup(true);
		
		foreach (preg_split('/\s+/', $searchStr, self::SEARCH_STR_WHITESPACS_SPLIT_LIMIT) as $searchStrPart) {
			if ($searchStrPart === '') {
				continue;
			}
			
			$queryComparatorConstraintGroup = new ComparatorConstraintGroup(false);
			foreach ($quickSearchProps as $quickSearchProp) {
				if (null !== ($comparatorConstraint = $quickSearchProp->buildComparatorConstraint($searchStrPart))) {
					$queryComparatorConstraintGroup->addComparatorConstraint($comparatorConstraint);
				}
			}
			
			if (!$queryComparatorConstraintGroup->isEmpty()) {
				$comparatorConstraintGroup->addComparatorConstraint($queryComparatorConstraintGroup);
			}
		}
		
		RecursionAsserters::unique(self::class)->pop((string) $this->eiMask->getEiTypePath());
		
		if ($comparatorConstraintGroup->isEmpty()) {
			return null;
		}
				
		return $comparatorConstraintGroup;
	}
}

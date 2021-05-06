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
namespace rocket\impl\ei\component\command\common\model\critmod;

use n2n\web\dispatch\Dispatchable;
use rocket\ei\manage\critmod\quick\QuickSearchDefinition;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\critmod\save\CritmodSaveDao;
use rocket\ei\util\frame\EiuFrame;
use rocket\spec\TypePath;
use rocket\ei\manage\frame\Boundry;

class QuickSearchForm implements Dispatchable {
	private $quickSearchDefinition;
	private $critmodSaveDao;
	private $categoryKey;
	
	protected $searchStr;
	
	public function __construct(QuickSearchDefinition $quickSearchDefinition,
			CritmodSaveDao $critmodSaveDao, string $stateKey, TypePath $eiTypePath) {
		$this->quickSearchDefinition = $quickSearchDefinition;
		$this->critmodSaveDao = $critmodSaveDao;
		$this->categoryKey = CritmodSaveDao::buildCategoryKey($stateKey, $eiTypePath);
		
		$this->searchStr = $this->critmodSaveDao->getQuickSearchString($this->categoryKey);
	}
	
	public function getSearchStr() {
		return $this->searchStr;
	}
	
	public function setSearchStr(string $searchStr = null) {
		$this->searchStr = $searchStr;
	}
	
	public function isActive(): bool {
		return $this->searchStr !== null;
	}
	
	private function _validation() {
		
	}
	
	public function search() {
		$this->critmodSaveDao->setQuickSearchString($this->categoryKey, $this->searchStr);
	}
	
	public function clear() {
		$this->searchStr = null;
		$this->critmodSaveDao->setQuickSearchString($this->categoryKey, $this->searchStr);
	}

	public function applyToEiFrame(EiFrame $eiFrame, bool $tmp) {
		if ($this->searchStr === null) return;
		
		if (null !== ($cc = $this->quickSearchDefinition->buildCriteriaConstraint($this->searchStr))) {
			$eiFrame->getBoundry()->addCriteriaConstraint(
					($tmp ? Boundry::TYPE_TMP_FILTER : Boundry::TYPE_HARD_FILTER),
					$cc);
		}
	}
	
	public static function create(EiuFrame $eiuFrame, CritmodSaveDao $critmodSaveDao, string $stateKey = null) {
		if ($stateKey === null) {
			$stateKey = uniqid();
		}
		
		return new QuickSearchForm($eiuFrame->getQuickSearchDefinition(), $critmodSaveDao, 
				$stateKey, $eiuFrame->getContextEiTypePath());
	}
}

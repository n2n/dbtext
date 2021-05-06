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
namespace rocket\core\model;

use rocket\user\model\LoginContext;
use n2n\context\Lookupable;
use n2n\core\container\N2nContext;
use rocket\core\model\launch\LaunchPad;

class TemplateModel implements Lookupable {
	private $currentUser;
	private $activeLaunchPadId;
	private $breadcrumbs;
	private $activeBreadcrumb;
	private $navArray = array();
	
	private function _init(LoginContext $loginContext, Rocket $rocket, RocketState $rocketState, 
			N2nContext $n2nContext) {
		$this->currentUser = $loginContext->getCurrentUser();
				
		$this->activeLaunchPadId = null;
		if (null !== ($activeLaunchPad = $rocketState->getActiveLaunchPad())) {
			$this->activeLaunchPadId = $activeLaunchPad->getId();
		}
		
		$this->breadcrumbs = $rocketState->getBreadcrumbs();
		if (sizeof($this->breadcrumbs)) {
			$this->activeBreadcrumb = array_pop($this->breadcrumbs);
		}
		
		$this->initNavArray($rocket, $n2nContext);
	}
	
	public function getCurrentUser() {
		return $this->currentUser;
	}
	
	public function getBreadcrumbs() {
		return $this->breadcrumbs;
	}
	
	public function getActiveBreadcrumb() {
		return $this->activeBreadcrumb;
	}
	
	private function initNavArray(Rocket $rocket, N2nContext $n2nContext) {
		$accessibleLaunchPadIds = $this->getAccesableLaunchPadIds();
		$this->navArray = array();
		
		foreach ($rocket->getLayout()->getMenuGroups() as $menuGroup) {
			$launchPads = $menuGroup->getLaunchPads();
			
			foreach ($launchPads as $key => $launchPad) {
				if (($accessibleLaunchPadIds !== null && !in_array($launchPad->getId(), $accessibleLaunchPadIds))
						|| !$launchPad->isAccessible($n2nContext)) {
					unset($launchPads[$key]);
				}
			}
			
			if (empty($launchPads)) continue;
			
			$open = (null !== $this->activeLaunchPadId) ? 
					$menuGroup->containsLaunchPadId($this->activeLaunchPadId) : false;
			$this->navArray[] = array('menuGroup' => $menuGroup,
					'open' => $open,
					'launchPads' => $launchPads);
		}
	}
	
	private function getAccesableLaunchPadIds() {
		if ($this->currentUser->isSuperAdmin()) return null;
		
		$accessibleLaunchPadIds = null;
		if (!$this->currentUser->isAdmin()) $accessibleLaunchPadIds = array();
		
		foreach ($this->currentUser->getRocketUserGroups() as $userGroup) {
			if (!$userGroup->isLaunchPadAccessRestricted()) {
				return null;
			}
			
			$accessibleLaunchPadIds = array_merge((array) $accessibleLaunchPadIds, 
					$userGroup->getaccessibleLaunchPadIds());
		}
		
		return $accessibleLaunchPadIds;
	}
	
	public function getNavArray() {
		return $this->navArray;
	}
	
	public function isLaunchPadActive(LaunchPad $launchPad) {
		return $this->activeLaunchPadId === $launchPad->getId();
	}
}

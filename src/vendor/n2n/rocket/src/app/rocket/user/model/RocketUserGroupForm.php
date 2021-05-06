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
namespace rocket\user\model;

use n2n\web\dispatch\Dispatchable;
use rocket\spec\Spec;
use n2n\reflection\annotation\AnnoInit;
use rocket\user\bo\RocketUserGroup;
use n2n\impl\web\dispatch\map\val\ValNotEmpty;
use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\map\val\ValArrayKeys;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\map\bind\MappingDefinition;
use n2n\l10n\DynamicTextCollection;
use rocket\core\model\launch\Layout;

class RocketUserGroupForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('name', 'launchPadRestrictionEnabled', 'accessibleLaunchPadIds'));
	}
	
	private $spec;
	private $layoutManager;
	private $userGroup;

	private $launchPadRestrictionEnabled = false;
	private $accessibleLaunchPadIds = array();
	
	public function __construct(RocketUserGroup $userGroup, Layout $layoutManager, Spec $spec, N2nContext $n2nContext) {
		$this->spec = $spec;
		$this->layoutManager = $layoutManager;
		$this->userGroup = $userGroup;
		
		$this->launchPadRestrictionEnabled = $userGroup->isLaunchPadAccessRestricted();
		if ($this->launchPadRestrictionEnabled) {
			$ids = $userGroup->getAccessibleLaunchPadIds();
			$this->accessibleLaunchPadIds = array_combine($ids, $ids);
		}
	}
	
	public function getRocketUserGroup() {
		return $this->userGroup;
	}
	
	public function getAccessibleLaunchPadIdOptions() {
		$launchPadIdOptions = array();
		foreach ($this->layoutManager->getMenuGroups() as $menuGroup) {
			foreach ($menuGroup->getLaunchPads() as $launchPad) {
				$launchPadIdOptions[$launchPad->getId()] = $menuGroup->getLabel() . ' > ' . $launchPad->getLabel(); 
			}
		}
		return $launchPadIdOptions;
	}
	
	private function _mapping(MappingDefinition $md, DynamicTextCollection $dtc) {
		$md->getMappingResult()->setLabel('name', $dtc->translate('common_name_label'));
	}
	
	private function _validation(BindingDefinition $bd) {
		$bd->val('name', new ValNotEmpty());
		$bd->val('accessibleLaunchPadIds', new ValArrayKeys(array_keys($this->getAccessibleLaunchPadIdOptions())));
	}
	
	public function isNew() {
		return null === $this->userGroup->getId();
	}
	
	public function getName() {
		return $this->userGroup->getName();
	}
	
	public function setName($name) {
		$this->userGroup->setName($name);
	}
	
	public function isLaunchPadRestrictionEnabled() {
		return $this->launchPadRestrictionEnabled;
	}
	
	public function setLaunchPadRestrictionEnabled($launchPadRestrictionEnabled) {
		$this->launchPadRestrictionEnabled = $launchPadRestrictionEnabled;
	}
	
	public function getaccessibleLaunchPadIds() {
		return $this->accessibleLaunchPadIds;
	}
	
	public function setaccessibleLaunchPadIds(array $accessibleLaunchPadIds) {
		$this->accessibleLaunchPadIds = $accessibleLaunchPadIds;
	}
		
	public function save() {
		if (!$this->launchPadRestrictionEnabled) {
			$this->userGroup->setAccessibleLaunchPadIds(null);
		} else {
			$this->userGroup->setAccessibleLaunchPadIds(array_keys($this->accessibleLaunchPadIds));
		}
	}
}

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

use rocket\user\bo\RocketUserGroup;
use rocket\ei\EiType;
use rocket\user\bo\EiGrant;
use rocket\custom\CustomType;
use rocket\user\bo\CustomGrant;
use rocket\user\bo\Grant;
use rocket\spec\TypePath;
use rocket\ei\EiTypeExtensionCollection;

class GroupGrantsViewModel {
	private $userGroup;
	private $eiTypeItems = array();
	private $customItems = array();
		
	public function __construct(RocketUserGroup $userGroup, array $eiTypes, array $customSpecs) {
		$this->userGroup = $userGroup;
				
		foreach ($eiTypes as $eiType) {
			if ($eiType->hasSuperEiType()) continue; 
			
			$this->applyEiTypeTree($eiType, 0);
		}
		
		foreach ($customSpecs as $customSpec) {
			$this->customItems[$customSpec->getId()] = new CustomTypeItem($customSpec, 
					$this->findCustomGrant($customSpec));
		}
	}
	
	/**
	 * @param TypePath $eiTypePath
	 * @return \rocket\user\bo\EiGrant|NULL
	 */
	private function findEiGrant(TypePath $eiTypePath) {
		foreach ($this->userGroup->getEiGrants() as $eiGrant) {
			if ($eiTypePath->equals($eiGrant->getEiTypePath())) {
				return $eiGrant;
			}
		}
		
		return null;
	}
	
	private function findCustomGrant(CustomType $customSpec) {
		$customSpecId = $customSpec->getId();
	
		foreach ($this->userGroup->getCustomGrants() as $customGrant) {
			if ($customSpecId === $customGrant->getCustomTypeId()) {
				return $customGrant;
			}
		}
	
		return null;
	}
	
	private function applyEiTypeTree(EiType $eiType, int $level) {
		$eiTypePath = $eiType->getEiMask()->getEiTypePath();
		$this->eiTypeItems[(string) $eiTypePath] = new EiTypeItem($level, $eiTypePath, 
				$eiType->getEiMask()->getLabelLstr(), $this->findEiGrant($eiTypePath));
		
		$this->applyEiTypeExtensions($eiType->getEiTypeExtensionCollection());
		
		$level++;
		foreach ($eiType->getSubEiTypes() as $subEiType) {
			$this->applyEiTypeTree($subEiType, $level);
		}
	}
	
	private function applyEiTypeExtensions(EiTypeExtensionCollection $collection) {
		$eiTypeExtensions = $collection->toArray();
		while (!empty($eiTypeExtensions)) {
			foreach ($eiTypeExtensions as $key => $eiTypeExtension) {
				$extendedEiTypePathStr = (string) $eiTypeExtension->getExtendedEiMask()->getEiTypePath();
				if (!isset($this->eiTypeItems[$extendedEiTypePathStr])) {
					continue;
				}
				
				$level = $this->eiTypeItems[$extendedEiTypePathStr]->getLevel() + 1;
				$eiTypePath = $eiTypeExtension->getEiMask()->getEiTypePath();
				$this->eiTypeItems[(string) $eiTypePath] = new EiTypeItem($level, $eiTypePath,
						$eiTypeExtension->getEiMask()->getLabelLstr());
				unset($eiTypeExtensions[$key]);
			}
		}
	}
	
	public function getGroupId() {
		return $this->userGroup->getId();
	}
	
	public function getRocketUserGroup() {
		return $this->userGroup;
	}
	
	/**
	 * @return EiTypeItem[]
	 */
	public function getEiTypeItems() {
		return $this->eiTypeItems;
	}
	
	/**
	 * @return CustomTypeItem[]
	 */
	public function getCustomItems() {
		return $this->customItems;
	}
}

class Item {
	private $grant;
	
	public function __construct(Grant $grant = null) {
		$this->grant = $grant;
	}
	
	public function isAccessible(): bool {
		return $this->grant !== null;
	}
	
	public function isFullyAccessible(): bool {
		return $this->grant !== null && $this->grant->isFull();
	}
}

class EiTypeItem extends Item {
	/**
	 * @var int
	 */
	private $level;
	/**
	 * @var TypePath
	 */
	private $eiTypePath;
	/**
	 * @var string
	 */
	private $label;
	
	public function __construct(int $level, TypePath $eiTypePath, string $label, EiGrant $eiGrant = null) {
		parent::__construct($eiGrant);
		$this->level = $level;
		$this->eiTypePath = $eiTypePath;
		$this->label = $label;
	}
	
	/**
	 * @return int
	 */
	public function getLevel(): int {
		return $this->level;
	}
	
	/**
	 * @return boolean
	 */
	public function isExtension() {
		return null !== $this->eiTypePath->getEiTypeExtensionId();
	}
	
	/**
	 * @return TypePath
	 */
	public function getEiTypePath(): TypePath {
		return $this->eiTypePath;
	}
	
	/**
	 * @return string
	 */
	public function getLabel(): string {
		return $this->label;
	}
}

class CustomTypeItem extends Item {
	private $customSpec;
	
	public function __construct(CustomType $customSpec, CustomGrant $customGrant = null) {
		parent::__construct($customGrant);
		$this->customSpec = $customSpec;
	}
	
	public function getCustomTypeId() {
		return $this->customSpec->getId();
	}
	
	public function getLabel(): string {
		return $this->customSpec->getLabel();
	}
}

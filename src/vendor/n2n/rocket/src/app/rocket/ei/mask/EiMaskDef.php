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
namespace rocket\ei\mask;

use n2n\persistence\orm\property\EntityProperty;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use rocket\ei\manage\critmod\sort\SortSettingGroup;

class EiMaskDef {
// 	const TYPE_CHANGE_MODE_DISABLED = 'disabled';
// 	const TYPE_CHANGE_MODE_REPLACE = 'replace';
// 	const TYPE_CHANGE_MODE_CHANGE = 'change';
	
	private $label;
	private $pluralLabel;
	private $iconType;
	private $draftingAllowed;
	private $draftHistorySize;
	private $identityStringPattern;
	private $previewControllerLookupId;
	private $filterSettingGroup;
	private $defaultSortSettingGroup;
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @param string $label
	 */
	public function setLabel(?string $label) {
		$this->label = $label;
	}
	
	/**
	 * @return string
	 */
	public function getPluralLabel() {
		return $this->pluralLabel;
	}
	
	/**
	 * @param string $pluralLabel
	 */
	public function setPluralLabel(?string $pluralLabel) {
		$this->pluralLabel = $pluralLabel;
	}
	
	/**
	 * @return string|null
	 */
	public function getIconType() {
		return $this->iconType;
	}
	
	public function setIconType(?string $iconType) {
		$this->iconType = $iconType;
	}
	
	/**
	 * @return string
	 */
	public function getIdentityStringPattern() {
		return $this->identityStringPattern;
	}

	/**
	 * @param string $identityStringPattern
	 */
	public function setIdentityStringPattern(string $identityStringPattern = null) {
		$this->identityStringPattern = $identityStringPattern;
	}
	
	/**
	 * @return boolean or null
	 */
	public function isDraftingAllowed() {
		return $this->draftingAllowed;
	}
	
	/**
	 * @param bool $draftingAllowed
	 */
	public function setDraftingAllowed(bool $draftingAllowed = null) {
		$this->draftingAllowed = $draftingAllowed;
	}
	
	/**
	 * @return int 
	 */
	public function getDraftHistorySize() {
		return $this->draftHistorySize;
	} 
	
	/**
	 * @param int $draftHistorySize
	 */
	public function setDraftHistorySize($draftHistorySize) {
		$this->draftHistorySize = $draftHistorySize;
	}
		
	/**
	 * @return \ReflectionClass
	 */
	public function getPreviewControllerLookupId() {
		return $this->previewControllerLookupId;
	}
	
	/**
	 * @param string $previewControllerLookupId
	 */
	public function setPreviewControllerLookupId($previewControllerLookupId) {
		$this->previewControllerLookupId = $previewControllerLookupId;
	}
	
// 	/**
// 	 * @param string $typeChangeMode
// 	 */
// 	public function setTypeChangeMode($typeChangeMode) {
// 		ArgUtils::valEnum($typeChangeMode, self::getTypeChangeModes(), null, true);
// 		$this->typeChangeMode = $typeChangeMode;
// 	}
	
// 	/**
// 	 * @return string[] 
// 	 */
// 	public static function getTypeChangeModes() {
// 		return array(self::TYPE_CHANGE_MODE_DISABLED, self::TYPE_CHANGE_MODE_REPLACE, 
// 				self::TYPE_CHANGE_MODE_CHANGE);
// 	}

	/**
	 * @return \rocket\ei\manage\critmod\filter\data\FilterSettingGroup
	 */
	public function getFilterSettingGroup() {
		return $this->filterSettingGroup;
	}
	
	/**
	 * @param FilterSettingGroup $filterSettingGroup
	 */
	public function setFilterSettingGroup(FilterSettingGroup $filterSettingGroup = null) {
		$this->filterSettingGroup = $filterSettingGroup;
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortSettingGroup
	 */
	public function getDefaultSortSettingGroup() {
		return $this->defaultSortSettingGroup;
	}
	
	/**
	 * @param SortSettingGroup $defaultSortSettingGroup
	 */
	public function setDefaultSortSettingGroup(SortSettingGroup $defaultSortSettingGroup = null) {
		$this->defaultSortSettingGroup = $defaultSortSettingGroup;
	}
	
	public static function buildEntityPropertyName(EntityProperty $entityProperty) {
		$propertyNames = array();
		do {
			$propertyNames[] = $entityProperty->getName();
		} while (null !== ($entityProperty = $entityProperty->getParent()));
		
		return (string) CrIt::p($propertyNames);
	}
}

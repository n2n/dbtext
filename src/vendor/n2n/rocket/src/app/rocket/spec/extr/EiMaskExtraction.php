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
namespace rocket\spec\extr;

use n2n\util\type\ArgUtils;
use rocket\ei\manage\critmod\sort\SortSettingGroup;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use rocket\ei\mask\model\DisplayScheme;

class EiMaskExtraction {
	private $label;
	private $pluralLabel;
	private $iconType;
	private $identityStringPattern;
	private $draftingAllowed;
	private $previewControllerLookupId;
	
	private $filterData;
	private $defaultSortSettingGroup;
	
	private $eiPropExtractions = array();
	private $eiCommandExtractions = array();
	
	private $displayScheme;
	
	private $overviewEiCommandId;
	private $entryDetailEiCommandId;
	private $genericEditEiCommandId;
	private $genericAddEiCommandId;
	
	/**
	 * @return string|null
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @param string|null $label
	 */
	public function setLabel(?string $label) {
		$this->label = $label;
	}
	
	/**
	 * @return string|null
	 */
	public function getPluralLabel() {
		return $this->pluralLabel;
	}
	
	/**
	 * @param string|null $pluralLabel
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
	
	/**
	 * @param string|null $iconType
	 */
	public function setIconType(?string $iconType) {
		$this->iconType = $iconType;
	}
	
	/**
	 * @return string|null
	 */
	public function getIdentityStringPattern() {
		return $this->identityStringPattern;
	}

	/**
	 * @param string|null $identityStringPattern
	 */
	public function setIdentityStringPattern($identityStringPattern) {
		$this->identityStringPattern = $identityStringPattern;
	}

	/**
	 * @return bool|null
	 */
	public function isDraftingAllowed() {
		return $this->draftingAllowed;
	}
	
	/**
	 * @param bool $draftingAllowed
	 */
	public function setDraftingAllowed(?bool $draftingAllowed) {
		$this->draftingAllowed = $draftingAllowed;
	}

	/**
	 * @return string|null
	 */
	public function getPreviewControllerLookupId() {
		return $this->previewControllerLookupId;
	}

	/**
	 * @param string|null $previewControllerLookupId
	 */
	public function setPreviewControllerLookupId(?string $previewControllerLookupId) {
		$this->previewControllerLookupId = $previewControllerLookupId;
	}

	/**
	 * @return \rocket\ei\manage\critmod\filter\data\FilterSettingGroup|null
	 */
	public function getFilterSettingGroup() {
		return $this->filterData;
	}
	
	/**
	 * @param FilterSettingGroup|null $filterData
	 */
	public function setFilterSettingGroup(?FilterSettingGroup $filterData) {
		$this->filterData = $filterData;
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortSettingGroup|null
	 */
	public function getDefaultSortSettingGroup() {
		return $this->defaultSortSettingGroup;
	}

	/**
	 * @param SortSettingGroup|null $defaultSortSettingGroup
	 */
	public function setDefaultSortSettingGroup(?SortSettingGroup $defaultSortSettingGroup) {
		$this->defaultSortSettingGroup = $defaultSortSettingGroup;
	}

	/**
	 * @return EiPropExtraction[]
	 */
	public function getEiPropExtractions() {
		return $this->eiPropExtractions;
	}
	
	public function addEiPropExtraction(EiPropExtraction $eiPropExtraction) {
		$this->eiPropExtractions[] = $eiPropExtraction;
	}
	
	/**
	 * @param EiPropExtraction[] $eiPropExtractions
	 */
	public function setEiPropExtractions(array $eiPropExtractions) {
		ArgUtils::valArray($eiPropExtractions, EiPropExtraction::class);
		$this->eiPropExtractions = $eiPropExtractions;	
	}
	
	/**
	 * @return EiComponentExtraction[]
	 */
	public function getEiCommandExtractions() {
		return $this->eiCommandExtractions;
	}
	
	/**
	 * @param EiComponentExtraction $configurableExtraction
	 */
	public function addEiCommandExtraction(EiComponentExtraction $configurableExtraction) {
		$this->eiCommandExtractions[] = $configurableExtraction;
	}
	
	/**
	 * @param EiComponentExtraction[] $eiCommandExtractions
	 */
	public function setEiCommandExtraction(array $eiCommandExtractions) {
		ArgUtils::valArray($eiCommandExtractions, EiComponentExtraction::class);
		$this->eiCommandExtractions = $eiCommandExtractions;
	}
		
	/**
	 * @return DisplayScheme
	 */
	public function getDisplayScheme() {
		return $this->displayScheme ?? $this->displayScheme = new DisplayScheme();
	}
	
	/**
	 * @param DisplayScheme $displayScheme
	 */
	public function setDisplayScheme(DisplayScheme $displayScheme) {
		$this->displayScheme = $displayScheme;
	}
	
	/**
	 * @return string|null
	 */
	public function getOverviewEiCommandId() {
		return $this->overviewEiCommandId;
	}

	/**
	 * @param string|null $overviewEiCommandId
	 */
	public function setOverviewEiCommandId(?string $overviewEiCommandId) {
		$this->overviewEiCommandId = $overviewEiCommandId;
	}

	/**
	 * @return string|null
	 */
	public function getGenericDetailEiCommandId() {
		return $this->entryDetailEiCommandId;
	}

	/**
	 * @param string|null $entryDetailEiCommandId
	 */
	public function setGenericDetailEiCommandId(?string $entryDetailEiCommandId) {
		$this->entryDetailEiCommandId = $entryDetailEiCommandId;
	}

	/**
	 * @return string|null
	 */
	public function getGenericEditEiCommandId() {
		return $this->genericEditEiCommandId;
	}

	/**
	 * @param string|null $genericEditEiCommandId
	 */
	public function setGenericEditEiCommandId(?string $genericEditEiCommandId) {
		$this->genericEditEiCommandId = $genericEditEiCommandId;
	}

	/**
	 * @return string|null
	 */
	public function getGenericAddEiCommandId() {
		return $this->genericAddEiCommandId;
	}

	/**
	 * @param string|null $genericAddEiCommandId
	 */
	public function setGenericAddEiCommandId(?string $genericAddEiCommandId) {
		$this->genericAddEiCommandId = $genericAddEiCommandId;
	}
}

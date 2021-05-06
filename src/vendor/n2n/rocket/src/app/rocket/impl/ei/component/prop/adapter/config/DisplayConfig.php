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
namespace rocket\impl\ei\component\prop\adapter\config;

use n2n\persistence\meta\structure\Column;
use n2n\util\type\ArgUtils;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\manage\gui\ViewMode;
use rocket\si\meta\SiStructureType;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\ei\manage\gui\GuiPropSetup;
use rocket\ei\manage\gui\GuiFieldAssembler;

class DisplayConfig extends PropConfigAdaption {
	private $compatibleViewModes;
	
	private $defaultDisplayedViewModes;
	private $siStructureType = SiStructureType::ITEM;
	
	private $siStructureTypeChoosable = true;
	private $defaultDisplayChoosable = true;
	
	/**
	 * @param int $compatibleViewModes
	 */
	public function __construct(int $compatibleViewModes) {
		$this->compatibleViewModes = $compatibleViewModes;
		$this->defaultDisplayedViewModes = $this->compatibleViewModes;
	}
	
	/**
	 * @param int $compatibleViewModes
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	function setCompatibleViewModes(int $compatibleViewModes) {
		$this->compatibleViewModes = $compatibleViewModes;
		$this->defaultDisplayedViewModes &= $compatibleViewModes; 
		return $this;
	}
	
	/**
	 * @param int $viewMode
	 * @return boolean
	 */
	public function isViewModeCompatible(int $viewMode) {
		return (boolean) ($viewMode & $this->compatibleViewModes);
	}
	
	/**
	 * @return boolean
	 */
	public function isCompactViewCompatible(): bool {
		return (boolean) (ViewMode::compact() & $this->compatibleViewModes);
	}
		
	/**
	 * @return boolean
	 */
	public function isBulkyViewCompatible(): bool {
		return (boolean) (ViewMode::bulky() & $this->compatibleViewModes);
	}
	
	/**
	 * @param int $viewModes
	 * @throws \InvalidArgumentException
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setDefaultDisplayedViewModes(int $viewModes) {
		if ($viewModes & ~$this->compatibleViewModes) {
			throw new \InvalidArgumentException('View mode not allowed.');
		}
		
		$this->defaultDisplayedViewModes = $viewModes;
		
		return $this;
	}
	
	/**
	 * @param int $viewModes
	 * @param bool $defaultDisplayed
	 * @throws \InvalidArgumentException
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function changeDefaultDisplayedViewModes(int $viewModes, bool $defaultDisplayed) {
	    ArgUtils::assertTrue((boolean) ($viewModes & ViewMode::all()), 'viewMode');
		
		if ($defaultDisplayed && ($viewModes & ~$this->compatibleViewModes)) {
			throw new \InvalidArgumentException('View mode not allowed.');
		}
		
		$this->changeDefaultDisplayed($viewModes, $defaultDisplayed);
		
		return $this;
	}
	
	private function changeDefaultDisplayed($viewModes, $defaultDisplayed) {
		if ($defaultDisplayed) {
			$this->defaultDisplayedViewModes |= $viewModes;
		} else {
			$this->defaultDisplayedViewModes &= ~$viewModes;
		}
	}
	
	public function isViewModeDefaultDisplayed($viewMode) {
		return (boolean) ($viewMode & $this->defaultDisplayedViewModes);
	}
	
	/**
	 * @param bool $defaultDisplayaed
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setListReadModeDefaultDisplayed(bool $defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::COMPACT_READ, $defaultDisplayaed);
		return $this;
	}
	
	/**
	 * @param bool $defaultDisplayaed
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setBulkyModeDefaultDisplayed(bool $defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::BULKY_READ, $defaultDisplayaed);
		return $this;
	}
	
	/**
	 * @param bool $defaultDisplayaed
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setEditModeDefaultDisplayed(bool $defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::BULKY_EDIT, $defaultDisplayaed);
		return $this;
	}
	
	/**
	 * @param bool $defaultDisplayaed
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setAddModeDefaultDisplayed(bool $defaultDisplayaed) {
		$this->changeDefaultDisplayed(ViewMode::BULKY_ADD, $defaultDisplayaed);
		return $this;
	}
	
	/**
	 * @param string $siStructureType
	 * @return \rocket\impl\ei\component\prop\adapter\config\DisplayConfig
	 */
	public function setSiStructureType(string $siStructureType) {
		ArgUtils::valEnum($siStructureType, SiStructureType::all());
		$this->siStructureType = $siStructureType;
		return $this;
	}
	
	public function getSiStructureType() {
		return $this->siStructureType;
	}
	
	/**
	 * @return bool
	 */
	public function isSiStructureTypeChoosable() {
		return $this->siStructureTypeChoosable;
	}

	/**
	 * @param boolean $siStructureTypeChoosable
	 */
	public function setSiStructureTypeChoosable(bool $siStructureTypeChoosable) {
		$this->siStructureTypeChoosable = $siStructureTypeChoosable;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isDefaultDisplayChoosable() {
		return $this->defaultDisplayChoosable;
	}

	/**
	 * @param boolean $defaultDisplayChoosable
	 */
	public function setDefaultDisplayChoosable(bool $defaultDisplayChoosable) {
		$this->defaultDisplayChoosable = $defaultDisplayChoosable;
		return $this; 	
	}
	
	const ATTR_DISPLAY_IN_OVERVIEW_KEY = 'displayInOverview';
	const ATTR_DISPLAY_IN_DETAIL_VIEW_KEY = 'displayInDetailView';
	const ATTR_DISPLAY_IN_EDIT_VIEW_KEY = 'displayInEditView';
	const ATTR_DISPLAY_IN_ADD_VIEW_KEY = 'displayInAddView';
	const ATTR_HELPTEXT_KEY = 'helpText';
	const ATTR_SI_STRUCTURE_TYPE_KEY = 'containerType';
	
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		return null;
	}
	
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_DISPLAY_IN_OVERVIEW_KEY)
				&& $this->isViewModeCompatible(ViewMode::compact())) {
			$this->changeDefaultDisplayedViewModes(
					ViewMode::compact(),
					$dataSet->reqBool(self::ATTR_DISPLAY_IN_OVERVIEW_KEY));
		}
		
		if ($dataSet->contains(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY)
				&& $this->isViewModeCompatible(ViewMode::BULKY_READ)) {
			$this->changeDefaultDisplayedViewModes(ViewMode::BULKY_READ,
					$dataSet->reqBool(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY));
		}
		
		if ($dataSet->contains(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY)
				&& $this->isViewModeCompatible(ViewMode::BULKY_EDIT)) {
			$this->changeDefaultDisplayedViewModes(ViewMode::BULKY_EDIT,
					$dataSet->reqBool(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY));
		}
		
		if ($dataSet->contains(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY)
				&& $this->isViewModeCompatible(ViewMode::BULKY_ADD)) {
			$this->changeDefaultDisplayedViewModes(ViewMode::BULKY_ADD,
					$dataSet->reqBool(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY));
		}
		
		if ($dataSet->contains(self::ATTR_SI_STRUCTURE_TYPE_KEY)) {
			$this->setSiStructureType(
					$dataSet->reqEnum(self::ATTR_SI_STRUCTURE_TYPE_KEY, SiStructureType::all()));
		}
	}
	
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($dataSet);
		
		if ($this->defaultDisplayChoosable) {
			if ($this->isCompactViewCompatible()) {
				$magCollection->addMag(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, new BoolMag('Show in overview',
						$lar->getBool(self::ATTR_DISPLAY_IN_OVERVIEW_KEY,
								$this->isViewModeDefaultDisplayed(ViewMode::BULKY_READ))));
			}
			
			if ($this->isViewModeCompatible(ViewMode::BULKY_READ)) {
				$magCollection->addMag(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY, new BoolMag('Show in detail view',
						$lar->getBool(self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY,
								$this->isViewModeDefaultDisplayed(ViewMode::BULKY_READ))));
			}
			
			if ($this->isViewModeCompatible(ViewMode::BULKY_EDIT)) {
				$magCollection->addMag(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, new BoolMag('Show in edit view',
						$lar->getBool(self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY,
								$this->isViewModeDefaultDisplayed(ViewMode::BULKY_EDIT))));
			}
			
			if ($this->isViewModeCompatible(ViewMode::BULKY_ADD)) {
				$magCollection->addMag(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY, new BoolMag('Show in add view',
						$lar->getBool(self::ATTR_DISPLAY_IN_ADD_VIEW_KEY,
								$this->isViewModeDefaultDisplayed(ViewMode::BULKY_ADD))));
			}
		}
		
		if ($this->siStructureTypeChoosable) {
			$types = SiStructureType::all();
			$magCollection->addMag(self::ATTR_SI_STRUCTURE_TYPE_KEY, new EnumMag('Container type',
					array_combine($types, $types), $this->getSiStructureType(), true));
		}
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		if (!$this->defaultDisplayChoosable) {
			$dataSet->remove(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY,
					self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, self::ATTR_DISPLAY_IN_ADD_VIEW_KEY);
		} else {
			$dataSet->appendAll($magCollection->readValues([self::ATTR_DISPLAY_IN_OVERVIEW_KEY, 
					self::ATTR_DISPLAY_IN_DETAIL_VIEW_KEY, self::ATTR_DISPLAY_IN_EDIT_VIEW_KEY, 
					self::ATTR_DISPLAY_IN_ADD_VIEW_KEY], true), true);
		}
		
		if (!$this->siStructureTypeChoosable) {
			$dataSet->remove(self::ATTR_SI_STRUCTURE_TYPE_KEY);
		} else {
			$dataSet->set(self::ATTR_SI_STRUCTURE_TYPE_KEY, $magCollection->readValue(self::ATTR_SI_STRUCTURE_TYPE_KEY));
		}
	}

// 	/**
// 	 * @param int $viewMode
// 	 * @return DisplayDefinition|null
// 	 */
// 	function toDisplayDefinition(int $viewMode, string $label, string $helpText = null) {
// 		if (!$this->isViewModeCompatible($viewMode)) return null;
		
// 		return new DisplayDefinition($this->siStructureType,
// 				$this->isViewModeDefaultDisplayed($viewMode), $label, $helpText);
// 	}
	
	/**
	 * @param Eiu $eiu
	 * @return GuiPropSetup|null
	 */
	function buildGuiPropSetup(Eiu $eiu, GuiFieldAssembler $guiFieldAssembler) {
		$viewMode = $eiu->guiFrame()->getViewMode();
		
		if (!$this->isViewModeCompatible($viewMode)) {
			return null;
		}
		
		return $eiu->factory()->newGuiPropSetup($guiFieldAssembler)
				->setDefaultDisplayed($this->isViewModeDefaultDisplayed($viewMode))
				->setSiStructureType($this->getSiStructureType())
				->toGuiPropSetup();
	}
}

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
namespace rocket\ei\mask\model;

use rocket\ei\manage\DefPropPath;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\EiGuiFrame;

class DisplayStructure {
	/**
	 * @var DisplayItem[]
	 */
	private $displayItems = array();
	
	/**
	 * @param DefPropPath $defPropPath
	 * @param string $type
	 * @param string $label
	 * @param string $moduleNamespace
	 */
	public function addDefPropPath(DefPropPath $defPropPath, string $type) {
		$this->displayItems[] = DisplayItem::create($defPropPath, $type);
	}
	
	/**
	 * @param DisplayStructure $displayStructure
	 * @param string $type
	 * @param string $label
	 * @param string $moduleNamespace
	 */
	public function addDisplayStructure(DisplayStructure $displayStructure, string $type/*, bool $autonomic = false*/, string $label = null, 
			string $helpText = null, string $moduleNamespace = null) {
		$this->displayItems[] = DisplayItem::createFromDisplayStructure($displayStructure, $type/*, $autonomic*/, $label, $helpText, $moduleNamespace);
	}
	
	/**
	 * @param DisplayItem $displayItem
	 */
	public function addDisplayItem(DisplayItem $displayItem) {
		$this->displayItems[] = $displayItem;
	}
	
	/**
	 * @return DisplayItem[]
	 */
	public function getDisplayItems() {
		return $this->displayItems;
	}
	
	/**
	 * @return int
	 */
	public function size() {
		return count($this->displayItems);
	}
	
	/**
	 * @param DisplayItem[] $displayItems
	 */
	public function setDisplayItems(array $displayItems) {
		ArgUtils::valArray($displayItems, DisplayItem::class);
		$this->displayItems = $displayItems;
	}
	
// 	public function containsAsideGroup() {
// 		foreach ($this->orderItems as $orderItem) {
// 			if ($orderItem->isSection() && $orderItem->getGuiSection()->getType() == GuiSection::ASIDE) {
// 				return true;
// 			}
// 		}
		
// 		return false;
// 	}
	
	public function getAllDefPropPaths() {
		$defPropPaths = array();
		foreach ($this->displayItems as $displayItem) {
			if ($displayItem->hasDisplayStructure()) {
				$defPropPaths = array_merge($defPropPaths, $displayItem->getDisplayStructure()->getAllDefPropPaths());
			} else{
				$defPropPaths[] = $displayItem->getDefPropPath();
			}
		}
		return $defPropPaths;
	}
		
// 	/**
// 	 * @return \rocket\ei\mask\model\DisplayStructure
// 	 */
// 	public function groupedItems() {
// 		$displayStructure = new DisplayStructure();
		
// 		$curDisplayStructure = null;
// 		foreach ($this->displayItems as $displayItem) {
// 			if ($displayItem->getSiStructureType() === SiStructureType::PANEL 
// 					&& $this->containsNonGrouped($displayItem)) {
// 				$displayStructure->addDisplayItem($displayItem->copy(SiStructureType::SIMPLE_GROUP));
// 				$curDisplayStructure = null;
// 				continue;
// 			}
			
// 			if ($displayItem->getSiStructureType() !== SiStructureType::ITEM) {
// 				$displayStructure->addDisplayItem($displayItem);
// 				$curDisplayStructure = null;
// 				continue;
// 			}
			
// 			if ($curDisplayStructure === null) {
// 				$curDisplayStructure = new DisplayStructure();
// 				$displayStructure->addDisplayStructure($curDisplayStructure, SiStructureType::SIMPLE_GROUP);
// 			}
			
// 			$curDisplayStructure->addDisplayItem($displayItem);
// 		}
			
// 		return $displayStructure;
// 	}
	
// 	/**
// 	 * @param DisplayItem $displayItem
// 	 * @return boolean
// 	 */
// 	private function containsNonGrouped(DisplayItem $displayItem) {
// 		if (!$displayItem->hasDisplayStructure()) return false;
		
// 		foreach ($displayItem->getDisplayStructure()->getDisplayItems() as $displayItem) {
// 			if ($displayItem->isGroup()) continue;
			
// 			if ($displayItem->getSiStructureType() === SiStructureType::PANEL
// 					&& !$this->containsNonGrouped($displayItem)) {
// 				continue;
// 			}
			
// 			return true;
// 		}
		
// 		return false;
// 	}
	
// 	public function whitoutAutonomics() {
// 		$displayStructure = new DisplayStructure();
		
// 		$this->roAutonomics($this->displayItems, $displayStructure, $displayStructure);
				
// 		return $displayStructure;
// 	}
	
// 	private function roAutonomics(array $displayItems, DisplayStructure $ds, DisplayStructure $autonomicDs) {
// 		foreach ($displayItems as $displayItem) {
// 			$groupType = $displayItem->getSiStructureType();
			
// 			if (!$displayItem->hasDisplayStructure()) {
// 				if ($groupType == SiStructureType::AUTONOMIC_GROUP) {
// 					$autonomicDs->addDefPropPath($displayItem->getDefPropPath(), SiStructureType::SIMPLE_GROUP, $displayItem->getLabel(), 
// 							$displayItem->getModuleNamespace());
// 				} else if ($displayItem->getSiStructureType() == $groupType) {
// 					$ds->displayItems[] = $displayItem;
// 				} else {
// 					$ds->addDefPropPath($displayItem->getDefPropPath(), $groupType, $displayItem->getLabel(), $displayItem->getModuleNamespace());	
// 				}
// 				continue;
// 			}
			
// 			$newDisplayStructure = new DisplayStructure();
// 			$this->roAutonomics($displayItem->getDisplayStructure()->getDisplayItems(), $newDisplayStructure, 
// 					($displayItem->getSiStructureType() == SiStructureType::MAIN_GROUP ? $newDisplayStructure : $autonomicDs));
			
// 			if ($displayItem->getSiStructureType() == SiStructureType::AUTONOMIC_GROUP) {
// 				$autonomicDs->addDisplayStructure($newDisplayStructure, SiStructureType::SIMPLE_GROUP, 
// 						$displayItem->getLabel(), $displayItem->getHelpText(), $displayItem->getModuleNamespace());	
// 			} else {
// 				$ds->addDisplayStructure($newDisplayStructure, $displayItem->getSiStructureType(), $displayItem->getLabel(), 
// 						$displayItem->getHelpText(), $displayItem->getModuleNamespace());
// 			}
// 		}
// 	}
	
// 	public function withContainer(string $type, string $label, string $helpText = null,  array $attrs = null) {
// 		if (count($this->displayItems) != 1 
// 				|| $this->displayItems[0]->getType() != $type) {
// 			$ds = new DisplayStructure();
// 			$ds->addDisplayStructure($this, $type, $label, $helpText, $attrs);
// 			return $ds;
// 		}
		
// 		if ($this->displayItems[0]->getLabel() == $label 
// 				&& $this->displayItems[0]->getHelpText() == $helpText 
// 				&& $this->displayItems[0]->getAttrs() === $attrs) {
// 			return $this;
// 		}
		
// 		$ds = new DisplayStructure();
// 		$ds->addDisplayItem($this->displayItems[0]->copy($type, $label, $helpText, $attrs));
// 		return $ds;
// 	}

// 	public function withoutSubStructures() {
// 		$displayStructure = new DisplayStructure();
	
// 		$this->stripSubStructures($displayStructure, $this->displayItems);
	
// 		return $displayStructure;
// 	}
	
// 	/**
// 	 * @param DisplayStructure $displayStructure
// 	 * @param DisplayItem[] $displayItems
// 	 */
// 	private function stripSubStructures(DisplayStructure $displayStructure, array $displayItems) {
// 		foreach ($displayItems as $displayItem) {
// 			if (!$displayItem->hasDisplayStructure()) {
// 				$displayStructure->displayItems[] = $displayItem;
// 				continue;
// 			}
				
// 			$this->stripSubStructures($displayStructure, $displayItem->getDisplayStructure()->getDisplayItems());			
// 		}
// 	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return boolean
	 */
	public function containsDefPropPathPrefix(DefPropPath $defPropPath) {
		return $this->containsLevelDefPropPathPrefix($defPropPath) 
				|| $this->containsSubDefPropPathPrefix($defPropPath);
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return boolean
	 */
	public function containsLevelDefPropPathPrefix(DefPropPath $defPropPath) {
		foreach ($this->displayItems as $displayItem) {
			if ($displayItem->hasDisplayStructure()) continue;
			
			if ($displayItem->getDefPropPath()->startsWith($defPropPath, false)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return boolean
	 */
	public function containsSubDefPropPathPrefix(DefPropPath $defPropPath) {
		foreach ($this->displayItems as $displayItem) {
			if (!$displayItem->hasDisplayStructure()) continue;
			
			if ($displayItem->getDisplayStructure()->containsDefPropPathPrefix($defPropPath)) {
				return true;
			}
		}
		
		return false;
	}
	
// 	public function purified(EiGuiFrame $eiGuiFrame) {
// 		return $this->rPurifyDisplayStructure($this, $eiGuiFrame);
// 	}
	
// 	/**
// 	 * @param DisplayStructure $displayStructure
// 	 * @param EiGuiFrame $eiGuiFrame
// 	 * @return \rocket\ei\mask\model\DisplayStructure
// 	 */
// 	private function rPurifyDisplayStructure($displayStructure, $eiGuiFrame) {
// 		$purifiedDisplayStructure = new DisplayStructure();
		
// 		foreach ($displayStructure->getDisplayItems() as $displayItem) {
// 			if ($displayItem->hasDisplayStructure()) {
// 				$purifiedDisplayStructure->addDisplayStructure(
// 						$this->rPurifyDisplayStructure($displayItem->getDisplayStructure(), $eiGuiFrame),
// 						$displayItem->getSiStructureType(), $displayItem->getLabel(), $displayItem->getHelpText(), 
// 						$displayItem->getModuleNamespace());
// 				continue;
// 			}
			
// 			$guiPropAssembly = null;
// 			try {
// 				$guiPropAssembly = $eiGuiFrame->getDisplayDefintion($displayItem->getDefPropPath());
// 			} catch (UnresolvableDefPropPathException $e) {
// 				continue;
// 			}
			
// 			$purifiedDisplayStructure->addDefPropPath($displayItem->getDefPropPath(),
// 					$displayItem->getSiStructureType() ?? $guiPropAssembly->getDisplayDefinition()->getSiStructureType());
// 		}
		
// 		return $purifiedDisplayStructure;
// 	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @return DisplayStructure
	 */
	public static function fromEiGuiFrame(EiGuiFrame $eiGuiFrame) {
		$displayStructure = new DisplayStructure();
		
		foreach ($eiGuiFrame->getGuiPropAssemblies() as $guiPropAssembly) {
			$displayStructure->addDefPropPath($guiPropAssembly->getDefPropPath(), 
					$guiPropAssembly->getDisplayDefinition()->getSiStructureType());
		}
		
		return $displayStructure;
	}
}
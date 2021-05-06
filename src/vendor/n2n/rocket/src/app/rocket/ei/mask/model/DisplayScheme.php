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

use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\ViewMode;

class DisplayScheme {
	private $overviewDisplayStructure;
	private $bulkyDisplayStructure;
	private $detailDisplayStructure;
	private $editDisplayStructure;
	private $addDisplayStructure;
	
	private $partialControlOrder;
	private $overallControlOrder;
	private $entryControlOrder;

	/**
	 * @return \rocket\ei\mask\model\DisplayStructure|null
	 */
	public function getOverviewDisplayStructure() {
		return $this->overviewDisplayStructure;
	}
	
	/**
	 * @param DisplayStructure|null $overviewDisplayStructure
	 */
	public function setOverviewDisplayStructure(DisplayStructure $overviewDisplayStructure = null) {
		$this->overviewDisplayStructure = $overviewDisplayStructure;
	}
	
	/**
	 * @return \rocket\ei\mask\model\DisplayStructure|null
	 */
	public function getBulkyDisplayStructure() {
		return $this->bulkyDisplayStructure;
	}
	
	/**
	 * @param DisplayStructure|null $bulkyDisplayStructure
	 */
	public function setBulkyDisplayStructure(DisplayStructure $bulkyDisplayStructure = null) {
		$this->bulkyDisplayStructure = $bulkyDisplayStructure;
	}
	
	/**
	 * @return \rocket\ei\mask\model\DisplayStructure|null
	 */
	public function getDetailDisplayStructure() {
		return $this->detailDisplayStructure;
	}
	
	/**
	 * @param DisplayStructure|null $detailDisplayStructure
	 */
	public function setDetailDisplayStructure(DisplayStructure $detailDisplayStructure = null) {
		$this->detailDisplayStructure = $detailDisplayStructure;
	}
	
	/**
	 * @return \rocket\ei\mask\model\DisplayStructure|null
	 */
	public function getEditDisplayStructure() {
		return $this->editDisplayStructure;
	}
	
	/**
	 * @param DisplayStructure|null $editDisplayStructure
	 */
	public function setEditDisplayStructure(DisplayStructure $editDisplayStructure = null) {
		$this->editDisplayStructure = $editDisplayStructure;
	}
	
	/**
	 * @return \rocket\ei\mask\model\DisplayStructure|null
	 */
	public function getAddDisplayStructure() {
		return $this->addDisplayStructure;
	}
	
	/**
	 * @param DisplayStructure|null $addDisplayStructure
	 */
	public function setAddDisplayStructure(DisplayStructure $addDisplayStructure = null) {
		$this->addDisplayStructure = $addDisplayStructure;
	}
	
// 	const BUTTON_ID_PARTIAL_SEPARATOR = '?PARTIAL?';
// 	const BUTTON_ID_OVERALL_SEPARATOR = '?OVERALL?';
// 	const BUTTON_ID_ENTRY_SEPARATOR = '?ENTRY?';
	
	
	/**
	 * @return \rocket\ei\mask\model\ControlOrder|null
	 */
	public function getPartialControlOrder() {
		return $this->partialControlOrder;
	}
	
	/**
	 * @param ControlOrder|null $partialControlOrder
	 */
	public function setPartialControlOrder(ControlOrder $partialControlOrder = null) {
		$this->partialControlOrder = $partialControlOrder;
	}
	
	/**
	 * @return \rocket\ei\mask\model\ControlOrder|null
	 */
	public function getOverallControlOrder() {
		return $this->overallControlOrder;
	}
	
	/**
	 * @param ControlOrder|null $overallControlOrder
	 */
	public function setOverallControlOrder(ControlOrder $overallControlOrder = null) {
		$this->overallControlOrder = $overallControlOrder;
	}
	
	/**
	 * @return \rocket\ei\mask\model\ControlOrder|null
	 */
	public function getEntryGuiControlOrder() {
		return $this->entryControlOrder;
	}
	
	/**
	 * @param ControlOrder|null $entryControlOrder
	 */
	public function setEntryGuiControlOrder(ControlOrder $entryControlOrder = null) {
		$this->entryControlOrder = $entryControlOrder;
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @return GuiDefinition $guiDefinition
	 */
	public function initEiGuiFrame(EiGuiFrame $eiGuiFrame, GuiDefinition $guiDefinition) {
		$displayStructure = null;
		switch ($eiGuiFrame->getViewMode()) {
			case ViewMode::BULKY_READ:
				$displayStructure = $this->getDetailDisplayStructure() ?? $this->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_EDIT:
				$displayStructure = $this->getEditDisplayStructure() ?? $this->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_ADD:
				$displayStructure = $this->getAddDisplayStructure() ?? $this->getBulkyDisplayStructure();
				break;
			case ViewMode::COMPACT_READ:
			case ViewMode::COMPACT_EDIT:
			case ViewMode::COMPACT_ADD:
				$displayStructure = $this->getOverviewDisplayStructure();
				break;
		}
		
		$commonEiGuiSiFactory = new CommonEiGuiSiFactory($eiGuiFrame);
		
		if ($displayStructure === null) {
			$eiGuiFrame->init($commonEiGuiSiFactory);
			
			$displayStructure = DisplayStructure::fromEiGuiFrame($eiGuiFrame);
		} else {
			$eiGuiFrame->init($commonEiGuiSiFactory, 
					$guiDefinition->filterDefPropPaths($displayStructure->getAllDefPropPaths()));
			$displayStructure = $displayStructure->purified($eiGuiFrame);
		}
		
		$commonEiGuiSiFactory->setDisplayStructure($displayStructure->groupedItems());
	}
	
// 	/**
// 	 * @param N2nLocale $n2nLocale
// 	 * @return array
// 	 */
// 	public static function buildPartialControlMap(EiMask $eiDef, N2nLocale $n2nLocale) {
// 		$labels = array();
	
// 		foreach ($eiDef->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof PartialControlComponent)) continue;
				
// 			foreach ($eiCommand->getPartialControlOptions($n2nLocale) as $controlId => $label) {
// 				$labels[ControlOrder::buildControlId($eiCommandId, $controlId)] = $label;
// 			}
// 		}
	
// 		if ($this->partialControlOrder === null) return $labels;
		
// 		return $this->partialControlOrder->sort($labels);
// 	}
// 	/**
// 	 * @param N2nLocale $n2nLocale
// 	 * @return array
// 	 */
// 	public static function buildOverallControlMap(EiMask $eiDef, N2nLocale $n2nLocale) {
// 		$labels = array();
	
// 		foreach ($this->eiType->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof OverallControlComponent)) continue;
				
// 			foreach ($eiCommand->getOverallControlOptions($n2nLocale) as $controlId => $label) {
// 				$labels[ControlOrder::buildControlId($eiCommandId, $controlId)] = $label;
// 			}
// 		}
	
// 		if ($this->overallControlOrder === null) return $labels;
		
// 		return $this->overallControlOrder->sort($labels);
// 	}
// 	/**
// 	 * @param N2nLocale $n2nLocale
// 	 * @return array
// 	 */
// 	public static function buildEntryGuiControlMap(EiMask $eiDef, N2nLocale $n2nLocale) {
// 		$labels = array();
	
// 		foreach ($this->eiType->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof EntryGuiControlComponent)) continue;
				
// 			foreach ($eiCommand->getEntryGuiControlOptions($n2nLocale) as $controlId => $label) {
// 				$labels[ControlOrder::buildControlId($eiCommandId, $controlId)] = $label;
// 			}
// 		}
		
// 		if ($this->entryControlOrder === null) return $labels;
	
// 		return $this->entryControlOrder->sort($labels);
// 	}
}

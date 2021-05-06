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
namespace rocket\ei\manage\gui;

use rocket\ei\manage\frame\EiFrame;
use rocket\si\content\impl\basic\CompactEntrySiGui;
use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\si\content\impl\basic\CompactExplorerSiGui;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\si\content\SiPartialContent;
use rocket\ei\manage\gui\control\GuiControl;
use rocket\ei\manage\api\ZoneApiControlCallId;
use n2n\util\type\ArgUtils;

class EiGuiUtil {
	private $eiGui;
	private $eiFrame;
	
	function __construct(EiGui $eiGui, EiFrame $eiFrame) {
		$this->eiGui = $eiGui;
		$this->eiFrame = $eiFrame;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiGui
	 */
	function createCompactEntrySiGui(bool $entrySiControlsIncluded = true) {
		$siComp = new CompactEntrySiGui($this->eiFrame->createSiFrame(), 
				$this->eiGui->getEiGuiModel()->createSiDeclaration($this->eiFrame),
				$this->eiGui->createSiEntry($this->eiFrame, $entrySiControlsIncluded));
		
// 		if ($generalSiControlsIncluded) {
// 			$siComp->setControls($this->eiGui->getEiGuiFrame()->createGeneralSiControls($this->eiFrame));
// 		}
		
		return $siComp;
	}
	
	/**
	 * @param bool $controlsIncluded
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiGui
	 */
	function createBulkyEntrySiGui(bool $generalSiControlsIncluded, bool $entrySiControlsIncluded, array $zoneGuiControls) {
		$siComp = new BulkyEntrySiGui($this->eiFrame->createSiFrame(), $this->eiGui->getEiGuiModel()->createSiDeclaration($this->eiFrame),
				$this->eiGui->createSiEntry($this->eiFrame, $entrySiControlsIncluded));
		
		$siComp->setEntryControlsIncluded($entrySiControlsIncluded);
		
		$siControls = [];
		if ($generalSiControlsIncluded) {
			$siControls = $this->eiGui->getEiGuiModel()->createGeneralSiControls($this->eiFrame);
		}
		
		$siComp->setControls(array_merge($this->createZoneSiControls($zoneGuiControls), $siControls));
		
		return $siComp;
	}
	
	/**
	 * @param GuiControl[] $zoneGuiControls
	 */
	private function createZoneSiControls(array $zoneGuiControls) {
		ArgUtils::valArray($zoneGuiControls, GuiControl::class);
		
		return array_map(function ($guiControl) {
			return $guiControl->toZoneSiControl($this->eiFrame->getN2nContext()->getHttpContext()->getRequest()->getUrl(), new ZoneApiControlCallId([$guiControl->getId()]));
		}, $zoneGuiControls);
	}
	
	function createCompactExplorerSiGui(int $pageSize, bool $entrySiControlsIncluded, bool $generalSiControlsIncluded, array $zoneGuiControls) {
		$eiFrameUtil = new EiFrameUtil($this->eiFrame);
				
		$siDeclaration = $this->eiGui->getEiGuiModel()->createSiDeclaration($this->eiFrame);
		$siPartialContent = new SiPartialContent($eiFrameUtil->count(), 
				$this->eiGui->createSiEntries($this->eiFrame, $entrySiControlsIncluded));
		$siComp = new CompactExplorerSiGui($this->eiFrame->createSiFrame(), $pageSize, $siDeclaration, $siPartialContent);
		
		$siControls = [];
		if ($generalSiControlsIncluded) {
			$siControls = $this->eiGui->getEiGuiModel()->createGeneralSiControls($this->eiFrame);
		}
		
		$siComp->setControls(array_merge($siControls, ...$this->createZoneSiControls($zoneGuiControls)));
		
		return $siComp;
	}
}
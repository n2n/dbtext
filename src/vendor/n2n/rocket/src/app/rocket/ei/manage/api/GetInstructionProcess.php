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
namespace rocket\ei\manage\api;

use rocket\si\api\SiGetResponse;
use rocket\ei\manage\frame\EiFrame;
use rocket\si\api\SiGetInstruction;
use rocket\si\api\SiGetResult;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\si\api\SiPartialContentInstruction;
use rocket\si\content\SiEntry;
use rocket\ei\manage\DefPropPath;
use n2n\util\ex\IllegalStateException;

class GetInstructionProcess {
	private $instruction;
	private $util;
	private $apiUtil;
	private $eiFrameUtil;
	
	function __construct(SiGetInstruction $instruction, EiFrame $eiFrame) {
		$this->instruction = $instruction;
		$this->util = new ProcessUtil($eiFrame);
		$this->apiUtil = new ApiUtil($eiFrame);
		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
	}
	
	/**
	 * @return SiGetResponse 
	 */
	function exec() {
		if (null !== ($entryId = $this->instruction->getEntryId())) {
			return $this->handleEntryId($entryId);
		}
		
		if ($this->instruction->isNewEntryRequested()) {
			return $this->handleNewEntry();
		}
		
		if (null !== ($spci = $this->instruction->getPartialContentInstruction())) {
			return $this->handlePartialContent($spci);
		}
		
		throw new IllegalStateException();
	}
	
	/**
	 * @return NULL|DefPropPath[]
	 */
	private function parseDefPropPaths() {
		$propIds = $this->instruction->getPropIds();
		
		if ($propIds === null) {
			return null;
		}
		
		return array_map(function ($propId) {
			return DefPropPath::create($propId);
		}, $propIds);
	}
	
	/**
	 * @param string $entryId
	 * @return \rocket\si\api\SiGetResult
	 */
	private function handleEntryId(string $entryId) {
		$defPropPaths = $this->parseDefPropPaths();
		
		$eiGui = $this->util->lookupEiGuiByPid($entryId, $this->instruction->getStyle()->isBulky(), 
				$this->instruction->getStyle()->isReadOnly(), $defPropPaths);
// 		$eiGui = $this->eiFrameUtil->createEiGuiFromEiObject($eiObject, 
// 				, null, $defPropPaths,
// 				$this->instruction->isDeclarationRequested());
		$eiFrame = $this->eiFrameUtil->getEiFrame();
		
		$getResult = new SiGetResult();
		$getResult->setEntry($eiGui->createSiEntry($eiFrame, $this->instruction->areEntryControlsIncluded()));
		
		if ($this->instruction->areGeneralControlsIncluded()) {
			$getResult->setGeneralControls($eiGui->getEiGuiModel()->createGeneralSiControls($eiFrame));
		}
		
		if ($this->instruction->isDeclarationRequested()) {
			$getResult->setDeclaration($eiGui->getEiGuiModel()->createSiDeclaration($eiFrame));
		}
		
		return $getResult;
	}
	
	/**
	 * @return \rocket\si\api\SiGetResult
	 */
	private function handleNewEntry() {
		$defPropPaths = $this->parseDefPropPaths();
		
		$eiGui = $this->eiFrameUtil->createNewEiGui(
				$this->instruction->getStyle()->isBulky(), $this->instruction->getStyle()->isReadOnly(), $defPropPaths,
				$this->instruction->getTypeIds(), $this->instruction->isDeclarationRequested());
		$eiFrame = $this->eiFrameUtil->getEiFrame();
		
		$getResult = new SiGetResult();
		$getResult->setEntry($eiGui->createSiEntry($eiFrame, $this->instruction->areEntryControlsIncluded()));
		
		if ($this->instruction->areGeneralControlsIncluded()) {
			$getResult->setGeneralControls($eiGui->getEiGuiModel()->createGeneralSiControls($eiFrame));
		}
		
		if ($this->instruction->isDeclarationRequested()) {
			$getResult->setDeclaration($eiGui->getEiGuiModel()->createSiDeclaration($eiFrame));
		}
		
		return $getResult;
	}
	
	/**
	 * @param SiEntry $siEntry
	 * @param EiEntryGui[] $eiEntryGuis
	 * @return \rocket\si\api\SiGetResult
	 */
	private function createEntryResult(SiEntry $siEntry, array $eiEntryGuis) {
		$result = new SiGetResult();
		$result->setEntry($siEntry);
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		if ($this->instruction->isBulky()) {
			$result->setDeclaration($this->apiUtil->createMultiBuildupSiDeclaration($eiEntryGuis));
		} else {
			$result->setDeclaration($this->apiUtil->createMultiBuildupSiDeclaration($eiEntryGuis));
		}
		
		return $result;
	}
	
	private function handlePartialContent(SiPartialContentInstruction $spci) {
		$num = $this->eiFrameUtil->count($spci->getQuickSearchStr());
		$eiGui = $this->eiFrameUtil->lookupEiGuiFromRange($spci->getFrom(), $spci->getNum(),
				$this->instruction->getStyle()->isBulky(), $this->instruction->getStyle()->isReadOnly(), $this->parseDefPropPaths(),
				$spci->getQuickSearchStr());
		
		$result = new SiGetResult();
		$result->setPartialContent($this->apiUtil->createSiPartialContent($spci->getFrom(), $num, $eiGui));
		
		if ($this->instruction->areGeneralControlsIncluded()) {
			$result->setGeneralControls($eiGui->getEiGuiModel()->createGeneralSiControls($this->eiFrameUtil->getEiFrame()));
		}
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		$result->setDeclaration($eiGui->getEiGuiModel()->createSiDeclaration($this->eiFrameUtil->getEiFrame()));
		
		return $result;
	}
	
	
	
}
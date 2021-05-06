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

use rocket\si\api\SiValResponse;
use rocket\ei\manage\frame\EiFrame;
use rocket\si\api\SiValResult;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\si\api\SiPartialContentInstruction;
use rocket\si\content\SiEntry;
use rocket\si\api\SiValInstruction;
use rocket\si\input\SiEntryInput;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\ViewMode;
use rocket\si\api\SiValGetResult;
use rocket\si\api\SiValGetInstruction;
use rocket\ei\manage\frame\EiEntryGuiResult;
use rocket\ei\manage\gui\EiGui;

class ValInstructionProcess {
	private $instruction;
	private $util;
	private $apiUtil;
	private $eiFrameUtil;
	
	private $eiEntry = null;
	private $eiEntryGuiResults = null;
	
	function __construct(SiValInstruction $instruction, EiFrame $eiFrame) {
		$this->instruction = $instruction;
		$this->util = new ProcessUtil($eiFrame);
		$this->apiUtil = new ApiUtil($eiFrame);
		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
	}
	
	function clear() {
		$this->eiEntry = null;
		$this->eiEntryGuiResults = [];
	}
	
	/**
	 * @return SiValResponse 
	 */
	function exec() {
		IllegalStateException::assertTrue($this->eiEntry === null);
		
		$entryInput = $this->instruction->getEntryInput();
		
		$eiGui = $this->util->determineEiGuiOfInput($entryInput);
		$this->eiEntry = $eiGui->getEiEntryGui()->getSelectedTypeDef()->getEiEntry();

		$result = new SiValResult($this->util->handleEntryInput($entryInput, $eiGui->getEiEntryGui()));
// 		$result->setEntryError();
		
		foreach ($this->instruction->getGetInstructions() as $key => $getInstruction) {
			$result->putGetResult($key, $this->handleGetInstruction($getInstruction));
		}
		
		$this->clear();
		
		return $result;
	}
	
	/**
	 * @param SiValGetInstruction $getInstruction
	 * @return SiValGetResult
	 */
	private function handleGetInstruction($getInstruction) {
		$eiGui = $this->util->determineEiGuiOfEiEntry($this->eiEntry, $this->instruction->getEntryInput()->getTypeId(), 
					$getInstruction->getStyle()->isBulky(), $getInstruction->getStyle()->isReadOnly());
		$eiFrame = $this->eiFrameUtil->getEiFrame();
		
		$result = new SiValGetResult();
		$result->setEntry($eiGui->createSiEntry($eiFrame, $getInstruction->areControlsIncluded()));
		
		if ($getInstruction->isDeclarationRequested()) {
			$result->setDeclaration($eiGui->getEiGuiModel()->createSiDeclaration($eiFrame));
		}
		
		return $result;
	}
	
	/**
	 * @param EiGui $eiGui
	 */
	private function registerEiGui($eiGui) {
		$this->eiEntryGuiResults[$eiGui->getEiGuiModel()->getViewMode()] = $eiGui;
	}
	
	/**
	 * @param bool $bulky
	 * @param bool $readOnly
	 * @return EiEntryGuiResult
	 */
	private function obtainEiEntryGuiResult(bool $bulky, bool $readOnly) {
		$viewMode = ViewMode::determine($bulky, $readOnly, $this->eiEntry->isNew());
		if (isset($this->eiEntryGuiResults[$viewMode])) {
			return $this->eiEntryGuiResults[$viewMode];
		}
		
		$eiEntryGuiResult = $this->eiFrameUtil->createEiEntryGui($this->eiEntry, $bulky, $readOnly, null, true);
		$this->registerEiGui($eiEntryGuiResult);
		return $eiEntryGuiResult;
	}
	
	/**
	 * @param string $entryId
	 * @return \rocket\si\api\SiValResult
	 */
	private function handleEntryInput(SiEntryInput $entryInput) {
		
	}
	
	/**
	 * @return \rocket\si\api\SiValResult
	 */
	private function handleNewEntry() {
		$eiEntryGuiMulti = $this->eiFrameUtil->createNewEiEntryGuiMulti(
				$this->instruction->isBulky(), $this->instruction->isReadOnly());
				
		return $this->createEntryResult($eiEntryGuiMulti->createSiEntry(), $eiEntryGuiMulti->getEiEntryGuis());	
	}
	
	/**
	 * @param SiEntry $siEntry
	 * @param EiEntryGui[] $eiEntryGuis
	 * @return \rocket\si\api\SiValResult
	 */
	private function createEntryResult(SiEntry $siEntry, array $eiEntryGuis) {
		$result = new SiValResult();
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
		$num = $this->eiFrameUtil->count();
		$eiGuiFrame = $this->eiFrameUtil->lookupEiGuiFrameFromRange($spci->getFrom(), $spci->getNum(),
				$this->instruction->isBulky(), $this->instruction->isReadOnly());
		
		$result = new SiValResult();
		$result->setPartialContent($this->apiUtil->createSiPartialContent($spci->getFrom(), $num, $eiGuiFrame));
		
		if (!$this->instruction->isDeclarationRequested()) {
			return $result;
		}
		
		if ($this->instruction->isBulky()) {
			$result->setDeclaration($this->apiUtil->createSiDeclaration($eiGuiFrame));
		} else {
			$result->setDeclaration($this->apiUtil->createSiDeclaration($eiGuiFrame));
		}
		
		return $result;
	}
}
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

use rocket\ei\IdPath;
use rocket\ei\manage\gui\control\GuiControl;
use n2n\web\http\BadRequestException;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\si\control\SiCallResponse;
use rocket\si\input\SiInputFactory;
use n2n\util\type\attrs\AttributesException;
use rocket\ei\mask\EiMask;
use rocket\ei\EiException;
use rocket\ei\manage\entry\UnknownEiObjectException;
use rocket\ei\UnknownEiTypeException;
use rocket\ei\manage\security\InaccessibleEiEntryException;
use n2n\web\http\ForbiddenException;
use rocket\si\input\SiInput;
use n2n\util\type\ArgUtils;
use rocket\si\input\SiInputError;

class ZoneApiControlProcess /*extends IdPath*/ {
	private $eiFrameUtil;
	private $eiEntryGui;
	private $guiControl;
	
	function __construct(EiFrame $eiFrame) {
		$this->eiFrameUtil = new EiFrameUtil($eiFrame);
	}
	
	function provideEiEntryGui(EiEntryGui $eiEntryGui) {
		$this->eiEntryGui = $eiEntryGui;
	}
	
	/**
	 * @param ZoneApiControlCallId $zoneControlCallId
	 * @param GuiControl[] $availableGuiControls
	 * @throws BadRequestException
	 * @return \rocket\ei\manage\gui\control\GuiControl
	 */
	function determineGuiControl(ZoneApiControlCallId $zoneControlCallId, array $availableGuiControls) {
		ArgUtils::valArray($availableGuiControls, GuiControl::class);
		
		$ids = $zoneControlCallId->toArray();
		
		$id = array_shift($ids);
		foreach ($availableGuiControls as $guiControl) {
			if ($guiControl->getId() !== $id) {
				continue;
			}
			
			while (!empty($ids) && $guiControl !== null) {
				$id = array_shift($ids);
				$guiControl = $guiControl->getChilById($id);
			}
			
			if ($guiControl !== null) {
				$this->guiControl = $guiControl;
				return;
			}
		}
		
		throw new BadRequestException('No control found for ZoneControlCalId: ' . $zoneControlCallId);
	}
	
	
	private function createEiGuiModel(EiMask $eiMask, int $viewMode) {
		try {
			return $this->eiFrameUtil->getEiFrame()->getManageState()->getEiGuiModelCache()->obtainEiGuiModel($eiMask, $viewMode, null);
		} catch (EiException $e) {
			throw new BadRequestException(null, 0, $e);
		}
	}
	
	/**
	 * @param array $data
	 * @throws BadRequestException
	 * @return SiCallResponse|null
	 */
	function handleInput(array $data) {
		if (!$this->guiControl->isInputHandled()) {
			throw new BadRequestException('No input SiControl executed with input.');
		}
		
		$inputFactory = new SiInputFactory();
		
		try {
			return $this->applyInput($inputFactory->create($data));
		} catch (AttributesException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (UnknownEiObjectException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (UnknownEiTypeException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (InaccessibleEiEntryException $e) {
			throw new ForbiddenException(null, null, $e);
		}
	}
	
	private $inputEiEntries = [];
	
	/**
	 * @param SiInput $siInput
	 * @return SiInputError|null
	 * @throws UnknownEiObjectException
	 * @throws UnknownEiTypeException
	 * @throws InaccessibleEiEntryException
	 * @throws \InvalidArgumentException
	 */
	private function applyInput($siInput) {
		$errorEntries = [];
		
		foreach ($siInput->getEntryInputs() as $key => $entryInput) {
			$eiGuiModel = null;
			$eiEntryGui = null;
			$eiEntry = null;
			if ($this->eiEntryGui !== null) {
				$eiEntryGui = $this->eiEntryGui;
				$eiEntryGui->handleSiEntryInput($entryInput);
				$eiEntry = $eiEntryGui->getSelectedEiEntry();
				$eiGuiModel = $this->eiEntryGui->getEiGui()->getEiGuiModel();
			} else {
				$eiObject = null;
				if (null !== $entryInput->getIdentifier()->getId()) {
					$eiObject = $this->eiFrameUtil->lookupEiObject($entryInput->getIdentifier()->getId());
				} else {
					$eiObject = $this->eiFrameUtil->createNewEiObject($entryInput->getTypeId());
				}
				
				$eiEntry = $this->eiFrameUtil->getEiFrame()->createEiEntry($eiObject);
				$eiGuiModel = $this->createEiGuiModel($eiEntry->getEiMask(), $this->eiGuiModel->getViewMode());
				$eiEntryGui = $eiGuiModel->createEiEntryGui($this->eiFrame, [$eiEntry], $this->eiGui);
				$eiEntryGui->handleSiEntryInput($entryInput);
			}
			
			$eiEntryGui->save();
			
			$this->inputEiEntries[] = $eiEntry;
			
			if ($eiEntry->validate()) {
				continue;
			}
			
			$errorEntries[$key] = $eiGuiModel->createSiEntry($this->eiFrameUtil->getEiFrame(), $eiEntryGui, false);
		}
		
		if (empty($errorEntries)) {
			return null;
		}
		
		return new SiInputError($errorEntries);
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	private function getEiGuiModel() {
		return $this->eiEntryGui->getEiGui()->getEiGuiModel();
	}
	
	/**
	 * @return SiCallResponse
	 */
	function callGuiControl() {
		return $this->guiControl->handle($this->eiFrameUtil->getEiFrame(), $this->getEiGuiModel(), $this->inputEiEntries);
	}
	
}
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
namespace rocket\ei\util\control;

use rocket\ei\manage\entry\EiEntry;
use rocket\si\control\SiControl;
use rocket\si\control\SiCallResponse;
use rocket\si\control\impl\ApiCallSiControl;
use rocket\si\control\SiButton;
use rocket\ei\manage\api\ApiControlCallId;
use rocket\ei\util\Eiu;
use rocket\ei\manage\ManageState;
use n2n\util\uri\Url;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\manage\frame\EiFrame;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\control\GuiControl;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\api\ZoneApiControlCallId;
use rocket\ei\manage\api\ApiController;

class EiuCallbackGuiControl implements GuiControl {
	private $id;
	private $eiuFrame;
	private $callback;
	private $siButton;
	private $inputHandled = false;
	
	/**
	 * @param string $id
	 * @param \Closure $callback
	 * @param SiButton $siButton
	 */
	function __construct(string $id, EiuFrame $eiuFrame, \Closure $callback, SiButton $siButton) {
		$this->id = $id;
		$this->eiuFrame = $eiuFrame;
		$this->callback = $callback;
		$this->siButton = $siButton;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::getId()
	 */
	function getId(): string {
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::isInputHandled()
	 */
	function isInputHandled(): bool {
		return $this->inputHandled;
	}
	
	/**
	 * @param bool $inputHandled
	 * @return \rocket\ei\util\control\EiuCallbackGuiControl
	 */
	function setInputHandled(bool $inputHandled) {
		$this->inputHandled = $inputHandled;
		return $this;
	}
	
	function getChilById(string $id): ?GuiControl {
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::toCmdSiControl()
	 */
	function toCmdSiControl(ApiControlCallId $siApiCallId): SiControl {
		return new ApiCallSiControl(
				$this->eiuFrame->getApiControlUrl($siApiCallId->getGuiControlPath()->getEiCommandPath()), 
				$siApiCallId, $this->siButton, $this->inputHandled);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::toZoneSiControl()
	 */
	function toZoneSiControl(Url $zoneUrl, ZoneApiControlCallId $zoneControlCallId): SiControl {
		return new ApiCallSiControl($zoneUrl, $zoneControlCallId, $this->siButton, $this->inputHandled);
	}
	
	/**
	 * @param Eiu $eiu
	 * @return SiCallResponse
	 */
	private function execCall(Eiu $eiu, ?array $inputEius) {
		$sifControlResponse = null;
		$callback = $this->callback;
		if ($inputEius === null) {
			$sifControlResponse = $callback($eiu);
		} else {
			$sifControlResponse = $callback($eiu, $inputEius);
		}
		ArgUtils::valTypeReturn($sifControlResponse, EiuControlResponse::class, null, $callback, true);
		
// 		$mmi = new MagicMethodInvoker($eiu->getN2nContext());
// 		$mmi->setMethod(new \ReflectionFunction($this->callback));
// 		$mmi->setClassParamObject(Eiu::class, $eiu);
// 		$mmi->setClassParamObject($className, $obj)
// 		$mmi->setReturnTypeConstraint(TypeConstraints::type(EiuControlResponse::class, true));
		
// 		$eiuControlResponse = $mmi->invoke();
		if ($sifControlResponse === null) {
			$sifControlResponse = $eiu->factory()->newControlResponse();
		}
		
		return $sifControlResponse->toSiCallResponse($eiu->lookup(ManageState::class)->getEiLifecycleMonitor());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::handle()
	 */
	function handle(EiFrame $eiFrame, EiGuiModel $eiGuiModel, array $inputEiEntries): SiCallResponse {
		ArgUtils::valArray($inputEiEntries, EiEntry::class);
		
		$inputEius = array_map(function ($inputEiEntry) use ($eiFrame) { 
			return new Eiu($eiFrame, $inputEiEntry); 
		}, $inputEiEntries);
		
		return $this->execCall(new Eiu($eiFrame, $eiGuiModel), $inputEius);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::handleEntry()
	 */
	function handleEntry(EiFrame $eiFrame, EiGuiModel $eiGuiModel, EiEntry $eiEntry): SiCallResponse {
		return $this->execCall(new Eiu($eiFrame, $eiGuiModel, $eiEntry), null);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::handleEntries()
	 */
	function handleEntries(EiFrame $eiFrame, EiGuiModel $eiGuiModel, array $eiEntries): SiCallResponse {
	}
}

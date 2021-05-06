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
use rocket\si\control\SiButton;
use rocket\ei\manage\api\ApiControlCallId;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\gui\control\GuiControl;
use rocket\si\control\impl\GroupSiControl;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ei\manage\api\ZoneApiControlCallId;
use n2n\util\uri\Url;
use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\gui\control\GuiControlPath;

class EiuGroupGuiControl implements GuiControl {
	private $id;
	private $siButton;
	private $childrean = [];
	
	function __construct(string $id, SiButton $siButton) {
		$this->id = $id;
		$this->siButton = $siButton;
	}
	
	function getId(): string {
		return $this->id;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::isInputHandled()
	 */
	public function isInputHandled(): bool {
		return false;
	}
	
	/**
	 * @param GuiControl $guiControl
	 * @return \rocket\ei\util\control\EiuGroupGuiControl
	 */
	function add(GuiControl ...$guiControls) {
		foreach ($guiControls as $guiControl) {
			$this->childrean[$guiControl->getId()] = $guiControl;
		}
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::toCmdSiControl()
	 */
	function toCmdSiControl(ApiControlCallId $siApiCallId): SiControl {
		return new GroupSiControl($this->siButton, 
				array_map(function ($child) use ($siApiCallId) {
					return $child->toCmdSiControl($siApiCallId->guiControlPathExt($child->getId()));
				}, $this->childrean));;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\control\GuiControl::toZoneSiControl()
	 */
	function toZoneSiControl(Url $zoneUrl, ZoneApiControlCallId $zoneControlCallId): SiControl {
		return new GroupSiControl($this->siButton,
				array_map(function ($child) use ($zoneUrl, $zoneControlCallId) {
					return $child->toZoneSiControl($zoneUrl, $zoneControlCallId->guiControlPathExt($child->getId()));
				}, $this->childrean));
	}
	
	function getChilById(string $id): ?GuiControl {
		return $this->childrean[$id] ?? null;
	}
	
	public function handleEntries(EiFrame $eiFrame, EiGuiModel $eiGuiModel, array $eiEntries): SiCallResponse {
		throw new UnsupportedOperationException('no input handled');
	}

	public function handle(EiFrame $eiFrame, EiGuiModel $eiGuiModel, array $inputEiEntries): SiCallResponse {
		throw new UnsupportedOperationException('no input handled');
	}

	public function handleEntry(EiFrame $eiFrame, EiGuiModel $eiGuiModel, EiEntry $eiEntry): SiCallResponse {
		throw new UnsupportedOperationException('no input handled');
	}
}
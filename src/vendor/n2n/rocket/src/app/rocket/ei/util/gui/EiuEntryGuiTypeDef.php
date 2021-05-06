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
namespace rocket\ei\util\gui;

use rocket\ei\util\EiuAnalyst;
use rocket\si\input\SiEntryInput;
use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\util\EiuPerimeterException;
use rocket\si\content\impl\basic\CompactEntrySiGui;
use rocket\ei\manage\gui\EiEntryGuiTypeDef;
use rocket\ei\manage\DefPropPath;

class EiuEntryGuiTypeDef {
	/**
	 * @var EiEntryGuiTypeDef $eiEntryGuiTypeDef
	 */
	private $eiEntryGuiTypeDef;
	/**
	 * @var EiuEntryGui|null
	 */
	private $eiuEntryGui;
	/**
	 * @var EiuAnalyst
	 */
	private $eiuAnalyst;
	
	/**
	 * @param EiEntryGuiTypeDef $eiEntryGuiTypeDef
	 * @param EiuEntryGui|null $eiuEntryGui
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(EiEntryGuiTypeDef $eiEntryGuiTypeDef, ?EiuEntryGui $eiuEntryGui, EiuAnalyst $eiuAnalyst) {
		$this->eiEntryGuiTypeDef = $eiEntryGuiTypeDef;
		$this->eiuEntryGui = $eiuEntryGui;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiEntryGuiTypeDef
	 */
	function getEiEntryGuiTypeDef() {
		return $this->eiEntryGuiTypeDef;
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 * @return EiuEntryGuiTypeDef
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		$this->getEiEntryGuiMulti()->handleSiEntryInput($siEntryInput);
		return $this;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuEntryGui[]
	 */
	function entryGuis() {
		$eiuEntryGuis = [];
		foreach ($this->getEiEntryGuiMulti()->getEiEntryGuis() as $eiTypeId => $eiEntryGui) {
			$eiuEntryGuis[$eiTypeId] = new EiuEntryGui($eiEntryGui, null, null, $this->eiuAnalyst);
		}
		return $eiuEntryGuis;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	function selectedEntryGui() {
		return new EiuEntryGui($this->eiEntryGuiMultiResult->getSelectedEiEntryGui(), null, null, $this->eiuAnalyst);
	}
	
	/**
	 * @param bool $siControlsIncluded
	 * @throws EiuPerimeterException
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiGui
	 */
	function createBulkyEntrySiGui(bool $siControlsIncluded) {
		if (!ViewMode::isBulky($this->getEiEntryGuiMulti()->getViewMode())) {
			throw new EiuPerimeterException('EiEntryGuiMulti is not bulky.');
		}
		
		return new BulkyEntrySiGui($this->eiEntryGuiMultiResult->createSiDeclaration(),
				$this->eiEntryGuiMultiResult->createSiEntry($siControlsIncluded));
	}
	
	/**
	 * @param bool $siControlsIncluded
	 * @throws EiuPerimeterException
	 * @return \rocket\si\content\impl\basic\CompactEntrySiGui
	 */
	function createCompactEntrySiGui(bool $siControlsIncluded) {
		if (!ViewMode::isCompact($this->getEiEntryGuiMulti()->getViewMode())) {
			throw new EiuPerimeterException('EiEntryGuiMulti is not compact.');
		}
		
		return new CompactEntrySiGui($this->eiEntryGuiMultiResult->createSiDeclaration(),
				$this->eiEntryGuiMultiResult->createSiEntry($siControlsIncluded));
	}
	
	/**
	 * @param DefPropPath|string $defPropPath
	 * @return EiuGuiField
	 */
	function field($defPropPath) {
		return new EiuGuiField(DefPropPath::create($defPropPath), $this, $this->eiuAnalyst);
	}
}

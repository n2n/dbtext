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

use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\DefPropPath;
use rocket\ei\EiType;
use rocket\si\input\SiEntryInput;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\attrs\AttributesException;
use rocket\ei\mask\EiMask;

class EiEntryGuiTypeDef {
	/**
	 * @var EiEntryGui
	 */
	private $eiEntryGui;
	/**
	 * @var EiMask
	 */
	private $eiMask;
	/**
	 * @var EiEntry
	 */
	private $eiEntry;
	/**
	 * @var GuiFieldMap
	 */
	private $guiFieldMap;
	/**
	 * @var EiEntryGuiListener[]
	 */
	private $eiEntryGuiListeners = array();
	
	/**
	 * @param int $viewMode
	 * @param int|null $treeLevel
	 */
	public function __construct(EiEntryGui $eiEntryGui, EiMask $eiMask, EiEntry $eiEntry) {
		$this->eiEntryGui = $eiEntryGui;
		$this->eiMask = $eiMask;
		$this->eiEntry = $eiEntry;
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @return EiEntryGui
	 */
	function getEiEntryGui() {
		return $this->eiEntryGui;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiEntry
	 */
	function getEiEntry() {
		return $this->eiEntry;
	}
	
	/**
	 * @return GuiFieldMap
	 */
	public function getGuiFieldMap() {
		$this->ensureInitialized();
		
		return $this->guiFieldMap;
	}
	
	function getGuiFieldByDefPropPath(DefPropPath $defPropPath) {
		$guiFieldMap = $this->guiFieldMap;
		
		$eiPropPaths = $defPropPath->toArray();
		
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			try {
				$guiField = $guiFieldMap->getGuiField($eiPropPath);
			} catch (GuiException $e) { }
			
			if (empty($eiPropPaths)) {
				return $guiField;
			}
			
			$guiFieldMap = $guiField->getForkGuiFieldMap();
			if ($guiFieldMap === null) {
				break;
			}
		}
		
		throw new GuiException('No GuiField with EiPropPath \'' . $defPropPath . '\' for \'' . $this . '\' registered');
	}
	
	function init(GuiFieldMap $guiFieldMap) {
		$this->ensureNotInitialized();
		
		$this->guiFieldMap = $guiFieldMap;
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->finalized($this);
		}
	}
	
	
	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->guiFieldMap !== null;
	}
	
	private function ensureInitialized() {
		if ($this->isInitialized()) return;
		
		throw new IllegalStateException('EiEntryGui not yet initlized.');
	}
	
	private function ensureNotInitialized() {
		if (!$this->isInitialized()) return;
		
		throw new IllegalStateException('EiEntryGui already initialized.');
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 * @throws IllegalStateException
	 * @throws \InvalidArgumentException
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		if ($this->eiMask->getEiType()->getId() != $siEntryInput->getTypeId()) {
			throw new \InvalidArgumentException('EiType missmatch.');
		}
		
		if ($this->eiEntry->getPid() !== $siEntryInput->getIdentifier()->getId()) {
			throw new \InvalidArgumentException('EiEntry id missmatch.');
		}
		
		foreach ($this->guiFieldMap->getAllGuiFields() as $defPropPathStr => $guiField) {
			$siField = $guiField->getSiField();
			
			if ($siField == null || $siField->isReadOnly()
					|| !$siEntryInput->containsFieldName($defPropPathStr)) {
				continue;
			}
			
			try {
				$siField->handleInput($siEntryInput->getFieldInput($defPropPathStr)->getData());
			} catch (AttributesException $e) {
				throw new \InvalidArgumentException(null, 0, $e);
			}
		}
	}
	
	public function save() {
		$this->ensureInitialized();
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->onSave($this);
		}
		
		$this->getGuiFieldMap()->save();
		
		foreach ($this->eiEntryGuiListeners as $eiEntryGuiListener) {
			$eiEntryGuiListener->saved($this);
		}
	}
	
	
	public function registerEiEntryGuiListener(EiEntryGuiListener $eiEntryGuiListener) {
		$this->eiEntryGuiListeners[spl_object_hash($eiEntryGuiListener)] = $eiEntryGuiListener;
	}
	
	public function unregisterEiEntryGuiListener(EiEntryGuiListener $eiEntryGuiListener) {
		unset($this->eiEntryGuiListeners[spl_object_hash($eiEntryGuiListener)]);
	}
	
	public function __toString() {
		return 'EiEntryGuiTypeDef of ' . $this->eiEntry;
	}
}
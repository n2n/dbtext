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

use rocket\ei\manage\gui\EiEntryGui;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\ei\manage\gui\EiEntryGuiListener;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\gui\GuiException;
use n2n\web\dispatch\mag\MagWrapper;
use rocket\ei\manage\gui\EiFieldAbstraction;
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\util\Eiu;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\util\EiuAnalyst;
use n2n\l10n\N2nLocale;
use rocket\si\input\SiEntryInput;
use rocket\ei\util\entry\EiuEntry;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\ei\util\spec\EiuType;
use n2n\util\ex\IllegalStateException;

class EiuEntryGui {
	private $eiEntryGui;
	private $eiuGui;
	private $eiuGuiFrame;
	private $eiuEntry;
	private $eiuAnalyst;
	
	function __construct(EiEntryGui $eiEntryGui, EiuGui $eiuGui, EiuAnalyst $eiuAnalyst) {
		$this->eiEntryGui = $eiEntryGui;
		$this->eiuGui = $eiuGui;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
// 	private function getEiGuiModel() {
// 		return $this->eiuGui->getEiGuiModel() ?? $this->eiuAnalyst->getEiGuiModel(true);
// 	}
	
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	function gui() {
		return $this->eiuGui;
	}
	
	/**
	 * @return int
	 */
	function getViewMode() {
		return $this->eiEntryGui->getEiGui()->getEiGuiModel()->getViewMode();
	}
	
	/**
	 * @see EiEntryGui::getGuiIdsPaths()
	 * @return \rocket\ei\manage\DefPropPath[]
	 */
	function getDefPropPaths() {
		return $this->eiEntryGui->getGuiFieldDefPropPaths();	
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return bool
	 */
	function containsDefPropPath(DefPropPath $defPropPath) {
		return $this->eiEntryGui->containsDisplayable($defPropPath);
	}
	
	/**
	 * @param DefPropPath|string $eiPropPath
	 * @param bool $required
	 * @throws GuiException
	 * @return string|null
	 */
	function getFieldLabel($eiPropPath, N2nLocale $n2nLocale = null, bool $required = false) {
		return $this->guiFrame()->getPropLabel($eiPropPath, $n2nLocale, $required);
	}
	
	/**
	 * @return boolean
	 */
	function isTypeSelected() {
		return $this->eiEntryGui->isTypeDefSelected();
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuType
	 * @throws IllegalStateException if $required true and not type is selected.
	 */
	function selectedType(bool $required = true) {
		if (!$this->isTypeSelected()) {
			if (!$required) return null;			
		}
		
		return new EiuType($this->eiEntryGui->getSelectedTypeDef()->getEiMask()->getEiType(), $this->eiuAnalyst);
	}
	
	/**
	 * @return boolean
	 */
	function isCompact() {
		$viewMode = $this->getViewMode();
		return $viewMode & ViewMode::compact();
	}
	
	/**
	 * @return boolean
	 */
	function isBulky() {
		return (bool) ($this->getViewMode() & ViewMode::bulky());
	}
	
	/**
	 * @return boolean
	 */
	function isReadOnly() {
		return (bool) ($this->getViewMode() & ViewMode::read());
	}
	
	/**
	 * @return EiEntryGui 
	 */
	function getEiEntryGui() {
		return $this->eiEntryGui;
	}
	
// 	/**
// 	 * @param DefPropPath|string $defPropPath
// 	 * @return SiField[]
// 	 */
// 	function getSiField($defPropPath) {
// 		$defPropPath = DefPropPath::create($defPropPath);
// 		return $this->eiEntryGui->getGuiFieldByDefPropPath($defPropPath)->getSiField();
// 	}
	
// 	/**
// 	 * @param DefPropPath|string $defPropPath
// 	 * @return SiField[]
// 	 */
// 	function getContextSiFields($defPropPath) {
// 		$defPropPath = DefPropPath::create($defPropPath);
// 		return $this->eiEntryGui->getGuiFieldByDefPropPath($defPropPath)->getContextSiFields();
// 	}
	
	/**
	 * @return boolean
	 */
	function isReady() {
		return $this->eiEntryGui->isInitialized();
	}
	
	/**
	 * @param \Closure $closure
	 */
	function whenReady(\Closure $closure) {
		$listener = new ClosureGuiListener(new Eiu($this), $closure);
		
		if ($this->isReady()) {
			$listener->finalized($this->eiEntryGui);
		} else {
			$this->eiEntryGui->registerEiEntryGuiListener($listener);
		}
	}
	
	/**
	 * @param \Closure $closure
	 */
	function onSave(\Closure $closure) {
		$this->eiEntryGui->registerEiEntryGuiListener(new ClosureGuiListener(new Eiu($this), null, $closure));
	}
	
	/**
	 * @param \Closure $closure
	 */
	function onSaved(\Closure $closure) {
		$this->eiEntryGui->registerEiEntryGuiListener(new ClosureGuiListener(new Eiu($this), null, null, $closure));
	}
	
	/**
	 * @return boolean
	 */
	function hasForkMags() {
		foreach ($this->eiEntryGui->getGuiFieldForkAssemblies() as $guiFieldForkAssembly) {
			if (!empty($guiFieldForkAssembly->getMagAssemblies())) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param DefPropPath|string $eiPropPath
	 * @param bool $required
	 * @throws GuiException
	 * @return MagWrapper
	 */
	function getMagWrapper($defPropPath, bool $required = false) {
		$magWrapper = null;
		
		try {
			$magAssembly = $this->eiEntryGui->getGuiFieldAssembly(DefPropPath::create($defPropPath))->getMagAssembly();
			if ($magAssembly !== null) {
				return $magAssembly->getMagWrapper();
			}
			
			throw new GuiException('No GuiField with DefPropPath \'' . $defPropPath . '\' is not editable.');
		} catch (GuiException $e) {
			if ($required) throw $e;
			return null;
		}
	}
	
	function getSubMagWrappers($prefixDefPropPath, bool $checkOnEiPropPathLevel = true) {
		$prefixDefPropPath = DefPropPath::create($prefixDefPropPath);
		
		$magWrappers = [];
		foreach ($this->eiEntryGui->filterGuiFieldAssemblies($prefixDefPropPath, $checkOnEiPropPathLevel)
				as $key => $guiFieldAssembly) {
			if (null !== ($magAssembly = $guiFieldAssembly->getMagAssembly())) {
				$magWrappers[$key] = $magAssembly->getMagWrapper();	
			}
		}
		
		return $magWrappers;
	}
	
	/**
	 * @param DefPropPath|string $eiPropPath
	 * @param bool $required
	 * @throws GuiException
	 * @return EiFieldAbstraction
	 */
	function getEiFieldAbstraction($defPropPath, bool $required = false) {
		return $this->eiuEntry->getEiFieldAbstraction($defPropPath, $required);
	}
	
// 	/**
// 	 * 
// 	 */
// 	protected function triggerWhenReady() {
// 		if (empty($this->whenReadyClosures)) return;
		
// 		$n2nContext = null;
// 		if ($this->eiuEntry !== null && null !== ($eiuFrame = $this->eiuEntry->getEiuFrame(false))) {
// 			$n2nContext = $eiuFrame->getN2nContext();
// 		}
// 		$invoker = new MagicMethodInvoker($n2nContext);
// 		$invoker->setClassParamObject(EiuEntryGui::class, $this);
// 		while (null !== ($closure = array_shift($this->whenReadyClosures))) {
// 			$invoker->invoke(null, $closure);
// 		}
// 	}

	/**
	 * @param PropertyPath|null $propertyPath
	 */
	function setContextPropertyPath(PropertyPath $propertyPath = null) {
		$this->eiEntryGui->setContextPropertyPath($propertyPath);
	}
	
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath|null
	 */
	function getContextPropertyPath() {
		return $this->eiEntryGui->getContextPropertyPath();
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	function entry() {
		if ($this->eiuEntry === null) {
			$eiEntryTypeDef = $this->getEiEntryGui()->getSelectedTypeDef();
			$this->eiuEntry = new EiuEntry($eiEntryTypeDef->getEiEntry(), null, null, $this->eiuAnalyst);
		}
		
		return $this->eiuEntry;
	}
	
	/**
	 * @param DefPropPath|string $defPropPath
	 * @return \rocket\ei\util\entry\EiuField
	 */
	function field($defPropPath) {
		return new EiuGuiField(DefPropPath::create($defPropPath), $this, $this->eiuAnalyst);
	}
	
	/**
	 * @param SiEntryInput $siEntryInput
	 * @throws CorruptedSiInputDataException
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	function handleSiEntryInput(SiEntryInput $siEntryInput) {
		$this->eiEntryGui->handleSiEntryInput($siEntryInput);
		return $this;
	}
	
	/**
	 * 
	 */
	function save() {
		$this->eiEntryGui->save();
	}
	
	/**
	 * @param bool $siControlsIncluded
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry(bool $siControlsIncluded) {
		return $this->eiEntryGui->getEiGui()->getEiGuiModel()->createSiEntry($this->eiuAnalyst->getEiFrame(true), 
				$this->eiEntryGui, $siControlsIncluded);
	}
	
	
// 	/**
// 	 * @param SiEntryInput $siEntryInput
// 	 * @throws IllegalStateException
// 	 * @throws \InvalidArgumentException
// 	 */
// 	function handleSiEntryInput(SiEntryInput $siEntryInput) {
// 		$this->eiEntryGui->handleSiEntryInput($siEntryInput);
// 	}
	
// 	function getEiMask() {
// 		if ($this->eiMask !== null) {
// 			return $this->eiMask;
// 		}
		
// 		throw new IllegalStateException('No EiMask available.');
// 	}
	
// 	/**
// 	 * @param EntryGuiModel $entryGuiModel
// 	 * @param EiFrame $eiFrame
// 	 * @return EiuEntryGui
// 	 */
// 	public static function from(EntryGuiModel $entryGuiModel, $eiFrame) {
// 		$entryGuiUtils = new EiuEntryGui($entryGuiModel, 
// 				new EiuEntry($entryGuiModel, $eiFrame));
// 		$entryGuiUtils->eiEntryGui = $entryGuiModel->getEiEntryGui();
// 		return $entryGuiUtils;
// 	}
}


class ClosureGuiListener implements EiEntryGuiListener {
	private $eiu;
	private $whenReadyClosure;
	private $onSaveClosure;
	private $savedClosure;

	/**
	 * @param Eiu $eiu
	 * @param \Closure $whenReadyClosure
	 * @param \Closure $onSaveClosure
	 * @param \Closure $savedClosure
	 */
	function __construct(Eiu $eiu, \Closure $whenReadyClosure = null, \Closure $onSaveClosure = null,
			\Closure $savedClosure = null) {
		$this->eiu = $eiu;
		$this->whenReadyClosure = $whenReadyClosure;
		$this->onSaveClosure = $onSaveClosure;
		$this->savedClosure = $savedClosure;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiEntryGuiListener::finalized()
	 */
	function finalized(EiEntryGui $eiEntryGui) {
		if ($this->whenReadyClosure === null) return;
		
		$this->call($this->whenReadyClosure);
		
		if ($this->onSaveClosure === null || $this->savedClosure === null) {
			$eiEntryGui->unregisterEiEntryGuiListener($this);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiEntryGuiListener::onSave()
	 */
	function onSave(EiEntryGui $eiEntryGui) {
		if ($this->onSaveClosure !== null) {
			$this->call($this->onSaveClosure);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\EiEntryGuiListener::saved()
	 */
	function saved(EiEntryGui $eiEntryGui) {
		if ($this->savedClosure !== null) {
			$this->call($this->savedClosure);
		}
	}

	/**
	 * @param \Closure $closure
	 */
	private function call($closure) {
		$mmi = new MagicMethodInvoker($this->eiu->frame()->getEiFrame()->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $this->eiu);
		$mmi->invoke(null, new \ReflectionFunction($closure));
	}
}

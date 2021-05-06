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

use n2n\core\container\N2nContext;
use n2n\util\StringUtils;
use n2n\util\type\ArgUtils;
use rocket\ei\EiCommandPath;
use rocket\ei\EiPropPath;
use rocket\ei\IdPath;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\entry\UnknownEiFieldExcpetion;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\gui\control\GuiControl;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\ei\mask\EiMask;
use rocket\ei\mask\model\DisplayItem;
use rocket\ei\mask\model\DisplayStructure;
use rocket\ei\util\Eiu;
use rocket\si\meta\SiStructureType;

class GuiDefinition {
	/**
	 * @var EiMask
	 */
	private $eiMask;
	/**
	 * @var GuiPropWrapper[]
	 */
	private $guiPropWrappers = array();
	/**
	 * @var EiPropPath[]
	 */
	private $eiPropPaths = array();
	/**
	 * @var GuiCommand[]
	 */
	private $guiCommands;
	
	function __construct(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @param string $id
	 * @param GuiProp $guiProp
	 * @param EiPropPath $defPropPath
	 * @throws GuiException
	 */
	function putGuiProp(EiPropPath $eiPropPath, GuiProp $guiProp) {
		$eiPropPathStr = (string) $eiPropPath;
		
		if (isset($this->guiPropWrappers[$eiPropPathStr])) {
			throw new GuiException('GuiProp for EiPropPath \'' . $eiPropPathStr . '\' is already registered');
		}
		
		$this->guiPropWrappers[$eiPropPathStr] = new GuiPropWrapper($this, $eiPropPath, $guiProp);
		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 */
	function removeGuiProp(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		
		unset($this->guiPropWrappers[$eiPropPathStr]);
		unset($this->eiPropPaths[$eiPropPathStr]);
	}
		
	/**
	 * @param DefPropPath $defPropPath
	 */
	function removeGuiPropByPath(DefPropPath $defPropPath) {
		$guiDefinition = $this;
		$eiPropPaths = $defPropPath->toArray();
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			if (empty($eiPropPaths)) {
				$guiDefinition->removeGuiProp($eiPropPath);
				return;
			}
			
			$guiDefinition = $guiDefinition->getGuiPropFork($eiPropPath)->getForkedGuiDefinition();
		
			if ($guiDefinition === null) {
				return;
			}
		}
	}
	
	
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws GuiException
	 * @return GuiPropWrapper
	 */
	function getGuiPropWrapper(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->guiPropWrappers[$eiPropPathStr])) {
			throw new GuiException('No GuiProp with id \'' . $eiPropPathStr . '\' registered');
		}
		
		return $this->guiPropWrappers[$eiPropPathStr];
	}

	/**
	 * @return GuiPropWrapper[]
	 */
	function getGuiPropWrappers() {
		return $this->guiPropWrappers;
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return boolean
	 */
	function containsGuiProp(DefPropPath $defPropPath) {
		$eiPropPaths = $defPropPath->toArray();
		$guiDefinition = $this;
		while (null !== ($eiPropPath = array_shift($eiPropPaths))) {
			if (empty($eiPropPaths)) {
				return $guiDefinition->containsEiPropPath($eiPropPath);
			}
			
			$guiDefinition = $guiDefinition->getGuiPropFork($eiPropPath)->getForkedGuiDefinition();
		}
		
		return true;
	}
	
// 	/**
// 	 * @param string $eiPropPath
// 	 * @param GuiPropFork $guiPropFork
// 	 */
// 	function putGuiPropFork(EiPropPath $eiPropPath, GuiPropFork $guiPropFork) {
// 		$eiPropPathStr = (string) $eiPropPath;
		
// 		$this->guiPropForkWrappers[$eiPropPathStr] = new GuiPropForkWrapper($this, $eiPropPath, $guiPropFork);
// 		$this->eiPropPaths[$eiPropPathStr] = $eiPropPath;
// 	}
	
// 	/**
// 	 * @param string $id
// 	 * @return boolean
// 	 */
// 	function containsLevelGuiPropForkId(string $id) {
// 		return isset($this->guiPropForkWrappers[$id]);
// 	}
	
// 	/**
// 	 * @param string $id
// 	 * @throws GuiException
// 	 * @return GuiPropFork
// 	 */
// 	function getGuiPropFork(EiPropPath $eiPropPath) {
// 		$eiPropPathStr = (string) $eiPropPath;
// 		if (!isset($this->guiPropForkWrappers[$eiPropPathStr])) {
// 			throw new GuiException('No GuiPropFork with id \'' . $eiPropPathStr . '\' registered.');
// 		}
		
// 		return $this->guiPropForkWrappers[$eiPropPathStr];
// 	}
	
// 	function getAllGuiProps() {
// 		return $this->buildGuiProps(array());
// 	}
	
// 	protected function buildGuiProps(array $baseEiPropPaths) {
// 		$guiProps = array();
		
// 		foreach ($this->eiPropPaths as $eiPropPath) {
// 			$eiPropPathStr = (string) $eiPropPath;
			
// 			if (isset($this->guiPropWrappers[$eiPropPathStr])) {
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
// 				$guiProps[(string) new DefPropPath($currentEiPropPaths)] = $this->guiPropWrappers[$eiPropPathStr];
// 			}
				
// 			if (isset($this->guiPropForkWrappers[$eiPropPathStr])) {
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
					
// 				$guiProps = array_merge($guiProps, $this->guiPropForkWrappers[$eiPropPathStr]->getForkedGuiDefinition()
// 						->buildGuiProps($currentEiPropPaths));
// 			}
// 		}
		
// 		return $guiProps;
// 	}
	
// 	/**
// 	 * @param DefPropPath[] $defPropPaths
// 	 * @return DefPropPath[]
// 	 */
// 	function filterDefPropPaths(array $defPropPaths) {
// 		return array_filter($defPropPaths, function (DefPropPath $defPropPath) {
// 			return $this->containsGuiProp($defPropPath);
// 		});
// 	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return bool
	 */
	function containsEiPropPath(EiPropPath $eiPropPath) {
		return isset($this->eiPropPaths[(string) $eiPropPath]);
	}
	
	/**
	 * @return \rocket\ei\manage\DefPropPath[]
	 */
	function getDefPropPaths() {
		$defPropPaths = array();
		
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			$defPropPath = new DefPropPath([$eiPropPath]);

			$defPropPaths[] = $defPropPath;
			
			foreach ($this->guiPropWrappers[$eiPropPathStr]->getForkedDefPropPaths() 
					as $forkedDefPropPath) {
				$defPropPaths[] = $defPropPath->ext($forkedDefPropPath);			
			}
		}
		
		return $defPropPaths;
	}
	
// 	function assembleDefaultGuiProps() {
// 		$guiPropAssemblies = [];
// 		$this->composeGuiPropAssemblies($guiPropAssemblies, []);
// 		return $guiPropAssemblies;
// 	}
	
// 	function assembleGuiProps(EiGuiFrame $eiGuiFrame, array $defPropPaths) {
// 		ArgUtils::valArray($defPropPaths, DefPropPath::class);
		
// // 		$eiu = new Eiu($eiGuiFrame);
		
// 		$guiPropAssemblies = [];
		
// 		foreach ($defPropPaths as $defPropPath) {
// 			$guiProp = $this->getGuiPropByDefPropPath($defPropPath);
			
// 			$displayDefinition = $guiProp->getDisplayDefinition();
// 			if ($displayDefinition === null) {
// 				continue;
// 			}
			
// 			$guiPropAssemblies[(string) $defPropPath] = new GuiPropAssembly($defPropPath, $displayDefinition);
// 		}
		
// 		return $guiPropAssemblies;
// 	}
	
	
// 	/**
// 	 * @param array $baseEiPropPaths
// 	 * @param Eiu $eiu
// 	 * @param int $minTestLevel
// 	 */
// 	protected function composeGuiPropAssemblies(array &$guiPropAssemblies, array $baseEiPropPaths) {
// 		foreach ($this->eiPropPaths as $eiPropPath) {
// 			$eiPropPathStr = (string) $eiPropPath;
			
// 			$displayDefinition = null;
// 			if (isset($this->guiPropWrappers[$eiPropPathStr])
// 					&& null !== ($displayDefinition = $this->guiPropWrappers[$eiPropPathStr]->getDisplayDefinition())
// 					&& $displayDefinition->isDefaultDisplayed()) {
						
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
				
// 				$defPropPath = new DefPropPath($currentEiPropPaths);
// 				$guiPropAssemblies[(string) $defPropPath] = new GuiPropAssembly($defPropPath, $displayDefinition);
// 			}
			
// 			if (isset($this->guiPropForkWrappers[$eiPropPathStr])
// 					&& null !== ($forkedGuiDefinition = $this->guiPropForkWrappers[$eiPropPathStr]->getForkedGuiDefinition())) {
// 				$currentEiPropPaths = $baseEiPropPaths;
// 				$currentEiPropPaths[] = $eiPropPath;
// 				$forkedGuiDefinition->composeGuiPropAssemblies($guiPropAssemblies, $currentEiPropPaths);
// 			}
// 		}
// 	}
	
// 	function createDefaultDisplayStructure(EiGuiFrame $eiGuiFrame) {
// 		$displayStructure = new DisplayStructure();
// 		$this->composeDisplayStructure($displayStructure, array(), new Eiu($eiGuiFrame));
// 		return $displayStructure;
// 	}
	

	
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return \rocket\ei\manage\gui\GuiPropWrapper
	 * @throws GuiException
	 */
	function getGuiPropWrapperByDefPropPath(DefPropPath $defPropPath) {
		$guiPropWrapper = $this->getGuiPropWrapper($defPropPath->getFirstEiPropPath());
		
		if (!$defPropPath->hasMultipleEiPropPaths()) {
			return $guiPropWrapper;
		}
		
		try {
			return $guiPropWrapper->getForkedGuiPropWrapper($defPropPath->getShifted());
		} catch (UnresolvableDefPropPathException $e) {
			throw new UnresolvableDefPropPathException('DefPropPath could not be resolved: ' . $defPropPath);
		}

	}
	
	

	
	/**
	 * @param EiEntry $eiEntry
	 * @param DefPropPath $defPropPath
	 * @throws UnknownEiFieldExcpetion
	 * @return \rocket\ei\manage\gui\EiFieldAbstraction|null
	 */
	function determineEiFieldAbstraction(N2nContext $n2nContext, EiEntry $eiEntry, DefPropPath $defPropPath) {
		$eiPropPaths = $defPropPath->toArray();
		$id = array_shift($eiPropPaths);
		if (empty($eiPropPaths)) {
			return $eiEntry->getEiFieldWrapper($id);
		}
		
		$guiPropFork = $this->getGuiPropFork($id);
		return $guiPropFork->determineEiFieldAbstraction(new Eiu($n2nContext, $eiEntry), new DefPropPath($eiPropPaths));
	}
	
	/**
	 * @return GuiPropForkWrapper[]
	 */
	function getGuiPropForkWrappers() {
		return $this->guiPropForkWrappers;
	}
		
	/**
	 * @param string $id
	 * @param GuiProp $guiProp
	 * @param EiPropPath $defPropPath
	 * @throws GuiException
	 */
	function putGuiCommand(EiCommandPath $eiCommandPath, GuiCommand $guiCommand) {
		$eiCommandPathStr = (string) $eiCommandPath;
		
		if (isset($this->guiCommand[$eiCommandPathStr])) {
			throw new GuiException('GuiCommand for EiCommandPath \'' . $eiCommandPathStr . '\' is already registered');
		}
		
		$this->guiCommands[$eiCommandPathStr] = $guiCommand;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param GuiControlPath $guiControlPath
	 * @return GuiControl
	 * @throws UnknownGuiControlException
	 */
	function createEntryGuiControl(EiFrame $eiFrame, EiGuiFrame $eiGuiFrame, EiEntry $eiEntry, GuiControlPath $guiControlPath): GuiControl {
		if ($guiControlPath->size() != 2) {
			throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
		}
		
		$eiu = new Eiu($eiFrame, $eiGuiFrame, $eiEntry);
		$cmdId = $guiControlPath->getFirstId();
		$controlId = $guiControlPath->getLastId();
		
		foreach ($this->guiCommands as $id => $guiCommand) {
			if ($cmdId != $id) {
				continue;
			}
			
			$guiControls = $this->extractEntryGuiControls($guiCommand, $id, $eiu);
			if (isset($guiControls[$controlId])) {
				return $guiControls[$controlId];
			}
		}
		
		throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @return GuiControl[]
	 */
	function createEntryGuiControls(EiFrame $eiFrame, EiGuiFrame $eiGuiFrame, EiEntry $eiEntry): array {
		$eiu = new Eiu($eiFrame, $eiGuiFrame, $eiEntry);
		
		$guiControls = [];
		foreach ($this->guiCommands as $id => $guiCommand) {
			foreach ($this->extractEntryGuiControls($guiCommand, $id, $eiu) as $entryGuiControl) {
				$guiControlPath = new GuiControlPath([$id, $entryGuiControl->getId()]);
				
				$guiControls[(string) $guiControlPath] = $entryGuiControl;
			}
		}
		return $guiControls;
	}
	
	/**
	 * @param GuiCommand $guiCommand
	 * @param string $guiCommandId
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\gui\control\GuiControl[]
	 */
	private function extractEntryGuiControls(GuiCommand $guiCommand, string $guiCommandId, Eiu $eiu) {
		$entryGuiControls = $guiCommand->createEntryGuiControls($eiu);
		ArgUtils::valArrayReturn($entryGuiControls, $guiCommand, 'createEntryGuiControls', GuiControl::class);
		
		return $this->mapGuiControls($entryGuiControls, $guiCommand, GuiControl::class);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param GuiControlPath $guiControlPath
	 * @return GuiControl
	 * @throws UnknownGuiControlException
	 */
	function createGeneralGuiControl(EiFrame $eiFrame, EiGuiFrame $eiGuiFrame, GuiControlPath $guiControlPath) {
		if ($guiControlPath->size() < 2) {
			throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
		}
		
		$eiu = new Eiu($eiFrame, $eiGuiFrame);
		$ids = $guiControlPath->toArray();
		$cmdId = array_shift($ids);
		$controlId = array_shift($ids);
		
		foreach ($this->guiCommands as $id => $guiCommand) {
			if ($cmdId != $id) {
				continue;
			}
			
			$guiControls = $this->extractGeneralGuiControls($guiCommand, $id, $eiu);
			if (null !== ($guiControl = $this->findGuiControl($guiControls[$controlId] ?? null, $ids))) {
				return $guiControl;
			}
		}
		
		throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
	}
	
	private function findGuiControl($guiControl, $ids) {
		if (empty($ids) || $guiControl === null) {
			return $guiControl;
		}
		
		$id = array_shift($ids);
		return $this->findGuiControl($guiControl->getChildById($id), $ids);
	}
	
	
	/**
	 * @param EiFrame $eiFrame
	 * @return GuiControl[]
	 */
	function createGeneralGuiControls(EiFrame $eiFrame, EiGuiFrame $eiGuiFrame): array {
		$eiu = new Eiu($eiFrame, $eiGuiFrame);
		
		$siControls = [];
		foreach ($this->guiCommands as $id => $guiCommand) {
			foreach ($this->extractGeneralGuiControls($guiCommand, $id, $eiu) as $generalGuiControl) {
				$guiControlPath = new GuiControlPath([$id, $generalGuiControl->getId()]);
				$siControls[(string) $guiControlPath] = $generalGuiControl;
			}
		}
		return $siControls;
	}
	
	/**
	 * @param GuiCommand $guiCommand
	 * @param string $guiCommandId
	 * @param Eiu $eiu
	 * @return \rocket\ei\manage\gui\control\GuiControl[]
	 */
	private function extractGeneralGuiControls(GuiCommand $guiCommand, string $guiCommandId, Eiu $eiu) {
		$generalGuiControls = $guiCommand->createGeneralGuiControls($eiu);
		ArgUtils::valArrayReturn($generalGuiControls, $guiCommand, 'extractGeneralGuiControls', GuiControl::class);
		
		return $this->mapGuiControls($generalGuiControls, $guiCommand, GuiControl::class);
	}
	
// 	/**
// 	 * @param EiFrame $eiFrame
// 	 * @param GuiControlPath $guiControlPath
// 	 * @return GuiControl
// 	 * @throw UnknownGuiControlException
// 	 */
// 	function createSelectionGuiControl(EiFrame $eiFrame, GuiControlPath $guiControlPath) {
// 		if ($guiControlPath->size() != 2) {
// 			throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
// 		}
		
// 		$eiu = new Eiu($eiFrame);
// 		$ids = $guiControlPath->toArray();
// 		$cmdId = array_shift($ids);
// 		$controlId = array_shift($ids);
		
// 		foreach ($this->guiCommands as $id => $guiCommand) {
// 			if ($cmdId != $id) {
// 				continue;
// 			}
			
// 			$guiControls = $this->extractSelectionGuiControls($guiCommand, $id, $eiu);
// 			if (null !== ($guiControl = $this->findGuiControl($guiControls[$controlId] ?? null, $ids))) {
// 				return $guiControl;
// 			}
// 		}
		
// 		throw new UnknownGuiControlException('Unknown GuiControlPath ' . $guiControlPath);
// 	}
	
// 	/**
// 	 * @param EiFrame $eiFrame
// 	 * @return GuiControl[]
// 	 */
// 	function createSelectionGuiControls(EiFrame $eiFrame): array {
// 		$eiu = new Eiu($eiFrame);
		
// 		$guiControls = [];
// 		foreach ($this->guiCommands as $id => $guiCommand) {
// 			foreach ($this->extractSelectionGuiControls($guiCommand, $id, $eiu) as $selectionGuiControl) {
// 				$guiControlPath = new GuiControlPath([$id, $selectionGuiControl->getId()]);
				
// 				$guiControls[(string) $guiControlPath] = $selectionGuiControl;
// 			}
// 		}
// 		return $guiControls;
// 	}
	
// 	/**
// 	 * @param GuiCommand $guiCommand
// 	 * @param string $guiCommandId
// 	 * @param Eiu $eiu
// 	 * @return \rocket\ei\manage\gui\control\GuiControl[]
// 	 */
// 	private function extractSelectionGuiControls(GuiCommand $guiCommand, string $guiCommandId, Eiu $eiu) {
// 		$selectionGuiControls = $guiCommand->createSelectionGuiControls($eiu);
// 		ArgUtils::valArrayReturn($selectionGuiControls, $guiCommand, 'createSelectionGuiControls', GuiControl::class);
		
// 		return $this->mapGuiControls($selectionGuiControls, $guiCommand, GuiControl::class);
// 	}
	
	/**
	 * @param GuiControl[] $guiControls
	 * @return GuiControl[]
	 */
	private function mapGuiControls($guiControls, $guiCommand, $guiControlClassName) {
		$mappedGuiControls = [];
		
		foreach ($guiControls as $guiControl) {
			$id = $guiControl->getId();
			
			if (!IdPath::isIdValid($id)) {
				throw new \InvalidArgumentException(StringUtils::strOf($guiCommand) . ' returns '
						. $guiControlClassName . ' with illegal id: ' . $id);
			}
			
			if (isset($mappedGuiControls[$id])) {
				throw new \InvalidArgumentException(StringUtils::strOf($guiCommand) . ' returns multiple '
						. $guiControlClassName . ' objects with id: ' . $id);
			}
			
			$mappedGuiControls[$id] = $guiControl;
		}
		
		return $mappedGuiControls;
	}
	
	/**
	 * @return Lstr[] 
	 */
	function getLabelLstrs() {
		return $this->buildLabelLstrs([]);
	}
	
	/**
	 * @param EiPropPath[] $contextEiPropPaths
	 * @return Lstr[]
	 */
	private function buildLabelLstrs(array $contextEiPropPaths) {
		$labelLstrs = array();
		
		foreach ($this->eiPropPaths as $eiPropPath) {
			$eiPropPathStr = (string) $eiPropPath;
			
			if (isset($this->guiPropWrappers[$eiPropPathStr])) {
				$currentEiPropPaths = $contextEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				$labelLstrs[(string) new DefPropPath($currentEiPropPaths)] = $this->guiPropWrappers[$eiPropPathStr]->getEiProp()->getLabelLstr();
			}
			
			if (isset($this->guiPropForkWrappers[$eiPropPathStr])) {
				$currentEiPropPaths = $contextEiPropPaths;
				$currentEiPropPaths[] = $eiPropPath;
				
				$labelLstrs = array_merge($labelLstrs, $this->guiPropForkWrappers[$eiPropPathStr]
						->getForkedGuiDefinition()->buildLabelLstrs($currentEiPropPaths));
			}
		}
		
		return $labelLstrs;
	}
	
// 	/**
// 	 * @param EiFrame $eiFrame
// 	 * @param int $viewMode
// 	 * @param DefPropPath[]|null $defPropPaths
// 	 * @return \rocket\ei\manage\gui\EiGuiModel
// 	 */
// 	function createEiGuiModel(N2nContext $n2nContext, int $viewMode, array $defPropPaths = null) {
// 		$eiGuiFrame = new EiGuiFrame($this, $viewMode);
		
		
		
		
// 		return new EiGuiModel($guiStructureDeclarations, $eiGuiFrame);
// 	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	function createEiGuiFrame(N2nContext $n2nContext, EiGuiModel $eiGuiModel, ?array $defPropPaths, 
			bool $guiStructureDeclarationsRequired) {
		ArgUtils::assertTrue($this->eiMask->isA($eiGuiModel->getContextEiMask()));
		
		$eiGuiFrame = new EiGuiFrame($eiGuiModel, $this, null);
		
		$eiGuiModel->putEiGuiFrame($eiGuiFrame);
				
		$guiStructureDeclarations = null;
		if (is_array($defPropPaths) && empty($defPropPaths)) {
			throw new \Exception();
		}
		if ($defPropPaths === null) {
			$guiStructureDeclarations = $this->initEiGuiFrameFromDisplayScheme($n2nContext, $eiGuiFrame);
		} else {
			$guiStructureDeclarations = $this->semiAutoInitEiGuiFrame($n2nContext, $eiGuiFrame, $defPropPaths);
		}
		
		if (!$guiStructureDeclarationsRequired) {
			return $eiGuiFrame;
		}
		
// 		if (ViewMode::isBulky($eiGuiModel->getViewMode())) {
// 			$guiStructureDeclarations = $this->groupGsds($guiStructureDeclarations);
// 		}
		
		$eiGuiFrame->setGuiStructureDeclarations($guiStructureDeclarations);
		return $eiGuiFrame;
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param DefPropPath[] $defPropPaths
	 * @return DefPropPath[]
	 */
	private function filterDefPropPaths($eiGuiFrame, $defPropPaths) {
		$filteredDefPropPaths = [];
		foreach ($defPropPaths as $key => $defPropPath) {
			if ($this->containsGuiProp($defPropPath)) {
				$filteredDefPropPaths[$key] = $defPropPath;
			}
		}
		return $filteredDefPropPaths;
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 */
	private function initEiGuiFrame($eiGuiFrame) {
		$this->eiMask->getEiModificatorCollection()->setupEiGuiFrame($eiGuiFrame);
		
		$eiGuiFrame->markInitialized();
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @return GuiStructureDeclaration[]
	 */
	private function initEiGuiFrameFromDisplayScheme(N2nContext $n2nContext, EiGuiFrame $eiGuiFrame) {
		$displayScheme = $this->eiMask->getDisplayScheme();
		
		$displayStructure = null;
		switch ($eiGuiFrame->getEiGuiModel()->getViewMode()) {
			case ViewMode::BULKY_READ:
				$displayStructure = $displayScheme->getDetailDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_EDIT:
				$displayStructure = $displayScheme->getEditDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::BULKY_ADD:
				$displayStructure = $displayScheme->getAddDisplayStructure() ?? $displayScheme->getBulkyDisplayStructure();
				break;
			case ViewMode::COMPACT_READ:
			case ViewMode::COMPACT_EDIT:
			case ViewMode::COMPACT_ADD:
				$displayStructure = $displayScheme->getOverviewDisplayStructure();
				break;
		}
		
		if ($displayStructure === null) {
			return $this->autoInitEiGuiFrame($n2nContext, $eiGuiFrame);
		} 
		
		return $this->nonAutoInitEiGuiFrame($n2nContext, $eiGuiFrame, $displayStructure);
	}
	
	/**
	 * @param N2nContext $n2nContext;
	 * @param EiGuiFrame $eiGuiFrame
	 * @param DisplayStructure $displayStructure
	 * @return GuiStructureDeclaration[]
	 */
	private function nonAutoInitEiGuiFrame($n2nContext, $eiGuiFrame, $displayStructure) {
		$assemblerCache = new EiFieldAssemblerCache($n2nContext, $eiGuiFrame, $displayStructure->getAllDefPropPaths());
		$guiStructureDeclarations = $this->assembleDisplayStructure($assemblerCache, $eiGuiFrame, $displayStructure);
		$this->initEiGuiFrame($eiGuiFrame);
		return $guiStructureDeclarations;
	}
	
// 	/**
// 	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
// 	 */
// 	private function groupGsds(array $guiStructureDeclarations) {
// 		$groupedGsds = [];
		
// 		$curUngroupedGsds = [];
		
// 		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
// 			if ($guiStructureDeclaration->getSiStructureType() === SiStructureType::ITEM
// 					|| ($guiStructureDeclaration->getSiStructureType() === SiStructureType::PANEL
// 							&& $this->containsNonGrouped($guiStructureDeclaration))) {
// 				$curUngroupedGsds[] = $guiStructureDeclaration;
// 				continue;
// 			}
					
// 			$this->appendToGoupedGsds($curUngroupedGsds, $groupedGsds);
// 			$curUngroupedGsds = [];
			
// 			$groupedGsds[] = $guiStructureDeclaration;
// 		}
		
// 		$this->appendToGoupedGsds($curUngroupedGsds, $groupedGsds);
		
// 		return $groupedGsds;
// 	}
	
		/**
		 * @param GuiStructureDeclaration $guiStructureDeclaration
		 * @return boolean
		 */
		private function containsNonGrouped(GuiStructureDeclaration $guiStructureDeclaration) {
			if (!$guiStructureDeclaration->hasChildrean()) return false;
	
			foreach ($guiStructureDeclaration->getChildren() as $guiStructureDeclaration) {
				if (SiStructureType::isGroup($guiStructureDeclaration->getSiStructureType())
						|| ($guiStructureDeclaration->getSiStructureType() === SiStructureType::PANEL
								&& !$this->containsNonGrouped($guiStructureDeclaration))) {
					continue;
				}
	
				return true;
			}
	
			return false;
		}
	
	/**
	 * @param GuiStructureDeclaration[] $curNonGroups
	 * @param GuiStructureDeclaration[] $groupedGsds
	 */
	function appendToGoupedGsds($curUngroupedGsds, &$groupedGsds) {
		if (empty($curUngroupedGsds)) {
			return;
		}
		
		$groupedGsds[] = GuiStructureDeclaration::createGroup($curUngroupedGsds, SiStructureType::SIMPLE_GROUP, null);
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param DefPropPath[] $possibleDefPropPaths
	 * @return GuiStructureDeclaration[]
	 */
	private function semiAutoInitEiGuiFrame($n2nContext, $eiGuiFrame, $possibleDefPropPaths) {
		$assemblerCache = new EiFieldAssemblerCache($n2nContext, $eiGuiFrame, $possibleDefPropPaths);
		$guiStructureDeclarations = $this->assembleSemiAutoGuiStructureDeclarations($assemblerCache, $eiGuiFrame, $possibleDefPropPaths, true);
		$this->initEiGuiFrame($eiGuiFrame);
		return $guiStructureDeclarations;
	}
	
	/**
	 * @param EiFieldAssemblerCache $assemblerCache
	 * @param EiGuiFrame $eiGuiFrame
	 * @param DefPropPath[]
	 * @param string $siStructureType
	 * @return GuiStructureDeclaration[]
	 */
	private function assembleSemiAutoGuiStructureDeclarations($assemblerCache, $eiGuiFrame, $defPropPaths, $siStructureTypeRequired) {
		$guiStructureDeclarations = [];
		
		foreach ($defPropPaths as $defPropPath) {
			$displayDefinition = $assemblerCache->assignDefPropPath($defPropPath);
			
			if ($displayDefinition === null) {
				continue;
			}
			
			$siStructureType = !$siStructureTypeRequired ? null : ($displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
			
			$guiStructureDeclarations[] = GuiStructureDeclaration::createField($defPropPath, $siStructureType, 
					$displayDefinition->getOverwriteLabel(), $displayDefinition->getOverwriteHelpText());
		}
			
		return $guiStructureDeclarations;
	}
	
	
	
// 	/**
// 	 * @param DefPropPath $defPropPath
// 	 * @param EiGuiFrame $eiGuiFrame
// 	 * @param bool $defaultDisplayedRequired
// 	 * @return DisplayDefinition|null
// 	 */
// 	private function buildDisplayDefinition($defPropPath, $eiGuiFrame, $defaultDisplayedRequired) {
// 		$eiPropPathStr = (string) $defPropPath->getFirstEiPropPath();
		
// 		if (!$defPropPath->hasMultipleEiPropPaths()) {
// 			if (!isset($this->guiPropWrappers[$eiPropPathStr])) {
// 				return null;
// 			}
			
// 			return $this->guiPropWrappers[$eiPropPathStr]->buildDisplayDefinition($eiGuiFrame, $defaultDisplayedRequired);
// 		}
		
// 		if (!isset($this->guiPropWrappers[$eiPropPathStr])) {
// 			return null;
// 		}
		
// 		return $this->guiPropWrappers[$eiPropPathStr]
// 				->buildForkDisplayDefinition($defPropPath->getShifted(), $eiGuiFrame, $defaultDisplayedRequired);
// 	}
	
	/**
	 * @param EiFieldAssemblerCache $assemblerCache
	 * @param EiGuiFrame $eiGuiFrame
	 * @param DisplayStructure $displayStructure
	 */
	private function assembleDisplayStructure($assemblerCache, $eiGuiFrame, $displayStructure) {
		$guiStructureDeclarations = [];
		
		foreach ($displayStructure->getDisplayItems() as $displayItem) {
			if ($displayItem->hasDisplayStructure()) {
				$guiStructureDeclarations[] = GuiStructureDeclaration::createGroup(
						$this->assembleDisplayStructure($assemblerCache, $eiGuiFrame, $displayItem->getDisplayStructure()),
						$displayItem->getSiStructureType(), $displayItem->getLabel(), $displayItem->getHelpText());
				continue;
			}
			
			$defPropPath = $displayItem->getDefPropPath();
			$displayDefinition = $assemblerCache->assignDefPropPath($defPropPath);
			if (null === $displayDefinition) {
				continue;
			}
			
			$guiStructureDeclarations[] = GuiStructureDeclaration::createField($defPropPath,
					$displayItem->getSiStructureType() ?? $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
		}
		
		return $guiStructureDeclarations;
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param EiGuiFrame $eiGuiFrame
	 */
	private function autoInitEiGuiFrame($n2nContext, $eiGuiFrame) {
// 		$n2nLocale = $eiGuiFrame->getEiFrame()->getN2nContext()->getN2nLocale();
		
		$guiStructureDeclarations = [];
		foreach ($this->guiPropWrappers as $guiPropWrapper) {
			$eiPropPath = $guiPropWrapper->getEiPropPath();
			$guiPropSetup = $guiPropWrapper->buildGuiPropSetup($n2nContext, $eiGuiFrame, null);
			
			if ($guiPropSetup === null) {
				continue;
			}
			
			$eiGuiFrame->putGuiFieldAssembler($eiPropPath, $guiPropSetup->getGuiFieldAssembler());
			
			$defPropPath = new DefPropPath([$eiPropPath]);
			
			$displayDefinition = $guiPropSetup->getDisplayDefinition();
			if (null !== $displayDefinition && $displayDefinition->isDefaultDisplayed()) {
				$eiGuiFrame->putDisplayDefintion($defPropPath, $displayDefinition);
				$guiStructureDeclarations[(string) $defPropPath] = GuiStructureDeclaration
						::createField($defPropPath, $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
			}
			
			foreach ($guiPropWrapper->getForkedDefPropPaths() as $forkedDefPropPath) {
				$absDefPropPath = $defPropPath->ext($forkedDefPropPath);
				$displayDefinition = $guiPropSetup->getForkedDisplayDefinition($forkedDefPropPath);
				
				if ($displayDefinition === null/* || !$displayDefinition->isDefaultDisplayed()*/) {
					continue;
				}
				$eiGuiFrame->putDisplayDefintion($absDefPropPath, $displayDefinition);
				
				$guiStructureDeclarations[(string) $absDefPropPath] = GuiStructureDeclaration
						::createField($absDefPropPath, $displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
			}
		}
		
		$this->initEiGuiFrame($eiGuiFrame);
		
		return $guiStructureDeclarations;
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @param DisplayDefinition $displayDefinition
	 * @param DisplayItem $displayItem
	 */
	private function createGuiStructureDeclaration($defPropPath, $displayDefinition, $displayItem) {
		if ($displayItem === null) {
			
		}
		
		return GuiStructureDeclaration::createField($defPropPath,
				$displayDefinition->getSiStructureType() ?? SiStructureType::ITEM);
	}
	
	
	
	
// 	/**
// 	 * @param DefPropPath $defPropPath
// 	 * @param DisplayItem|null $displayItem
// 	 * @return GuiStructureDeclaration
// 	 */
// 	private function createFieldDeclaration($defPropPath, $eiProp, $displayItem) {
// 		$guiProp = $this->getGuiPropWrapper($eiPropPath);
// 	}
	
// 	private $guiDefinitionListeners = array();
	
// 	function registerGuiDefinitionListener(GuiDefinitionListener $guiDefinitionListener) {
// 		$this->guiDefinitionListeners[spl_object_hash($guiDefinitionListener)] = $guiDefinitionListener;
// 	}
	
// 	function unregisterGuiDefinitionListener(GuiDefinitionListener $guiDefinitionListener) {
// 		unset($this->guiDefinitionListeners[spl_object_hash($guiDefinitionListener)]);
// 	}
	
// 	/**
// 	 * @return GuiDefinitionListener[]
// 	 */
// 	function getGuiDefinitionListeners() {
// 		return $this->guiDefinitionListeners;
// 	}
}


class EiFieldAssemblerCache {
	private $n2nContext;
	private $eiGuiFrame;
	private $displayStructure;
	/**
	 * @var DefPropPath[]
	 */
	private $possibleDefPropPaths = [];
	/**
	 * @var DefPropPath[]
	 */
	private $defPropPaths = [];
	/**
	 * @var GuiPropSetup[]
	 */
	private $guiPropSetups = [];
	
	/**
	 * @param N2nContext $n2nContext
	 * @param EiGuiFrame $eiGuiFrame
	 * @param array $possibleDefPropPaths
	 */
	function __construct(N2nContext $n2nContext, EiGuiFrame $eiGuiFrame, array $possibleDefPropPaths) {
		$this->n2nContext = $n2nContext;
		$this->eiGuiFrame = $eiGuiFrame;
		$this->possibleDefPropPaths = $possibleDefPropPaths;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return GuiPropSetup|null
	 */
	private function assemble(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		
		if (array_key_exists($eiPropPathStr, $this->guiPropSetups)) {
			return $this->guiPropSetups[$eiPropPathStr];
		}
		
		$guiDefinition = $this->eiGuiFrame->getGuiDefinition();
		
		if (!$guiDefinition->containsEiPropPath($eiPropPath)) {
			$this->guiPropSetups[$eiPropPathStr] = null;
			return null;
		}
		
		$guiPropWrapper = $this->eiGuiFrame->getGuiDefinition()->getGuiPropWrapper($eiPropPath);
		$guiPropSetup = $guiPropWrapper->buildGuiPropSetup($this->n2nContext, $this->eiGuiFrame, 
				$this->filterForkedDefPropPaths($eiPropPath));
		$this->eiGuiFrame->putGuiFieldAssembler($eiPropPath, $guiPropSetup->getGuiFieldAssembler());
		$this->guiPropSetups[$eiPropPathStr] = $guiPropSetup;
		
		return $guiPropSetup;
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @return DisplayDefinition|null
	 */
	function assignDefPropPath(DefPropPath $defPropPath) {
		$guiPropSetup = $this->assemble($defPropPath->getFirstEiPropPath());
		
		if ($guiPropSetup === null) {
			return null;
		}
		
		$displayDefinition = null;
		if (!$defPropPath->hasMultipleEiPropPaths()) {
			$displayDefinition = $guiPropSetup->getDisplayDefinition();
		} else {
			$displayDefinition = $guiPropSetup->getForkedDisplayDefinition($defPropPath->getShifted());
		}
		
		if ($displayDefinition !== null) {
			$this->eiGuiFrame->putDisplayDefintion($defPropPath, $displayDefinition);
		}
		
		return $displayDefinition;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return DefPropPath[]
	 */
	private function filterForkedDefPropPaths($eiPropPath) {
		$forkedDefPropPaths = [];
		foreach ($this->possibleDefPropPaths as $possibleDefPropPath) {
			if ($possibleDefPropPath->hasMultipleEiPropPaths() 
					&& $possibleDefPropPath->getFirstEiPropPath()->equals($eiPropPath)) {
				$forkedDefPropPaths[] = $possibleDefPropPath->getShifted();
			}
		}
		return $forkedDefPropPaths;
	}
	
	/**
	 * @return \rocket\ei\manage\DefPropPath[]
	 */
	function getDefPropPaths() {
		return $this->defPropPaths;
	}
}

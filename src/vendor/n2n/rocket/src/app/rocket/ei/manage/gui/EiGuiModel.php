<?php
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\ei\manage\DefPropPath;
use rocket\si\meta\SiDeclaration;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\security\InaccessibleEiEntryException;
use rocket\ei\manage\entry\EiEntry;
use rocket\si\content\SiEntry;
use rocket\ei\EiType;
use rocket\ei\manage\gui\control\GuiControlPath;
use rocket\ei\manage\gui\control\UnknownGuiControlException;
use rocket\si\meta\SiStyle;

class EiGuiModel {
	/**
	 * @var EiMask
	 */
	private $contextEiMask;
	/**
	 * @var int
	 */
	private $viewMode;
	/**
	 * @var EiGuiFrame
	 */
	private $eiGuiFrames = [];
	
	/**
	 * @param EiMask $eiMask
	 * @param EiGuiFrame $eiGuiFrame
	 */
	function __construct(EiMask $contextEiMask, int $viewMode) {
		$this->contextEiMask = $contextEiMask;
		ArgUtils::valEnum($viewMode, ViewMode::getAll());
		$this->viewMode = $viewMode;
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	function getContextEiMask() {
		return $this->contextEiMask;
	}
	
	/**
	 * @return int
	 */
	function getViewMode() {
		return $this->viewMode;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	function getEiGuiFrames() {
		return $this->eiGuiFrames;
	}
	
	/**
	 * @return boolean
	 */
	function hasEiGuiFrames() {
		return !empty($this->eiGuiFrames);
	}
	
	/**
	 * @return EiType[]
	 */
	function getEiTypes() {
		return array_map(
				function ($arg) { return $arg->getGuiDefinition()->getEiMask()->getEiType(); }, 
				$this->eiGuiFrames);
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function putEiGuiFrame(EiGuiFrame $eiGuiFrame) {
		$eiType = $eiGuiFrame->getGuiDefinition()->getEiMask()->getEiType();
		
		ArgUtils::assertTrue($eiType->isA($eiGuiFrame->getGuiDefinition()->getEiMask()->getEiType()));
		
		$this->eiGuiFrames[$eiType->getId()] = $eiGuiFrame;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	function hasMultipleEiGuiFrames() {
		return count($this->eiGuiFrames) > 1;
	}
	
	/**
	 * @return boolean
	 */
	function hasSingleEiGuiFrame() {
		return count($this->eiGuiFrames) === 1;
	}
	
	
	/**
	 * @return DefPropPath[]
	 */
	function getDefPropPaths() {
		$this->ensureInit();
		
		$defPropPaths = [];
		foreach ($this->guiStructureDeclarations as $guiStructureDeclaration) {
			$defPropPaths = array_merge($defPropPaths, $guiStructureDeclaration->getAllDefPropPaths());
		}
		return $defPropPaths;
	}
	
	/**
	 * @return \rocket\si\meta\SiDeclaration
	 */
	function createSiDeclaration(EiFrame $eiFrame) {
		$n2nLocale = $eiFrame->getN2nContext()->getN2nLocale();
		$siDeclaration = new SiDeclaration(ViewMode::createSiStyle($this->viewMode));
		
		foreach ($this->eiGuiFrames as $eiGuiFrame) {
			$siDeclaration->addTypeDeclaration($eiGuiFrame->createSiMaskDeclaration($n2nLocale));
		}
		
		return $siDeclaration;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntryGui $eiEntryGui
	 * @param bool $siControlsIncluded
	 * @return \rocket\si\content\SiEntry
	 */
	function createSiEntry(EiFrame $eiFrame, EiEntryGui $eiEntryGui, bool $siControlsIncluded) {
		$siEntry = new SiEntry($eiEntryGui->createSiEntryIdentifier(), 
				new SiStyle(ViewMode::isBulky($this->viewMode), ViewMode::isReadOnly($this->viewMode)));
		$siEntry->setTreeLevel($eiEntryGui->getTreeLevel());
		
		$typeDefs = $eiEntryGui->getTypeDefs();
		
		foreach ($this->eiGuiFrames as $key => $eiGuiFrame) {
			ArgUtils::assertTrue(isset($typeDefs[$key]));
			$eiEntryGuiTypeDef = $typeDefs[$key];
			
			$siEntry->putBuildup($eiEntryGuiTypeDef->getEiMask()->getEiType()->getId(),
					$eiGuiFrame->createSiEntryBuildup($eiFrame, $eiEntryGuiTypeDef, $siControlsIncluded));
		}
		
		if ($eiEntryGui->isTypeDefSelected()) {
			$siEntry->setSelectedTypeId($eiEntryGui->getSelectedTypeDef()->getEiMask()->getEiType()->getId());
		}
		
		return $siEntry;
	}
	
// 	/**
// 	 * @return SiProp[]
// 	 */
// 	private function getSiProps() {
// 		IllegalStateException::assertTrue($this->guiStructureDeclarations !== null,
// 				'EiGuiFrame is forked.');
		
		
// 		$deter = new ContextSiFieldDeterminer();
		
// 		$siProps = [];
// 		foreach ($this->filterFieldGuiStructureDeclarations($this->guiStructureDeclarations) 
// 				as $guiStructureDeclaration) {
// 			$defPropPath = $guiStructureDeclaration->getDefPropPath();
			
// 			$siProps[] = $this->createSiProp($guiStructureDeclaration);
			
// 			$deter->reportDefPropPath($defPropPath);
// 		}
		
// 		return array_merge($deter->createContextSiProps($this->eiGuiFrame), $siProps);
// 	}
	
	
	
	
	
// 	/**
// 	 * @param GuiStructureDeclaration $guiStructureDeclaration
// 	 * @return SiProp
// 	 */
// 	private function createSiProp(GuiStructureDeclaration $guiStructureDeclaration) {
// 		return new SiProp($guiStructureDeclaration->getDefPropPath(),
// 				$guiStructureDeclaration->getLabel(), $guiStructureDeclaration->getHelpText());
// 	}
	
	
	
	/**
	 * @param GuiStructureDeclaration[] $guiStructureDeclarations
	 * @return GuiStructureDeclaration[]
	 */
	private function filterFieldGuiStructureDeclarations($guiStructureDeclarations) {
		$filtereds = [];
		foreach ($guiStructureDeclarations as $guiStructureDeclaration) {
			if ($guiStructureDeclaration->hasDefPropPath()) {
				$filtereds[] = $guiStructureDeclaration;
				continue;
			}
			
			array_push($filtereds, ...$this->filterFieldGuiStructureDeclarations(
					$guiStructureDeclaration->getChildren()));
		}
		return $filtereds;
	}
	
// 	function appendEiEntryGui(EiFrame $eiFrame, array $eiEntries, int $treeLevel = null) {
// 		ArgUtils::valArray($eiEntries, EiEntry::class);
		
// 		$eiEntryGui = new EiEntryGui($this->contextEiMask->getEiType(), $treeLevel);
		
// 		$eiEntries = $this->catEiEntries($eiEntries);
		
// 		foreach ($this->eiGuiFrames as $eiGuiFrame) {
// 			$eiType = $eiGuiFrame->getEiType();
			
// 			if (isset($eiEntries[$eiType->getId()])) {
// 				$eiGuiFrame->applyEiEntryGuiTypeDef($eiFrame, $eiEntryGui, $eiEntries[$eiType->getId()]);
// 				continue;
// 			}
			
// 			throw new \InvalidArgumentException('No EiEntry provieded for ' . $eiType);
// 		}
	
// 		$this->finalizeEiEntryGui($eiEntryGui);
		
// 		return $eiEntryGui;
// 	}
	
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry[] $eiEntries
	 * @param int $treeLevel
	 * @throws \InvalidArgumentException
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	function createEiEntryGui(EiFrame $eiFrame, array $eiEntries, EiGui $eiGui, int $treeLevel = null) {
		ArgUtils::assertTrue($eiGui->getEiGuiModel() === $this);
		ArgUtils::valArray($eiEntries, EiEntry::class);
		
		$eiEntryGui = new EiEntryGui($this->contextEiMask, $eiGui, $treeLevel);
		
		$eiEntries = $this->catEiEntries($eiEntries);

		foreach ($this->eiGuiFrames as $eiGuiFrame) {
			$eiType = $eiGuiFrame->getEiType();
			
			if (isset($eiEntries[$eiType->getId()])) {
				$eiGuiFrame->applyEiEntryGuiTypeDef($eiFrame, $eiEntryGui, $eiEntries[$eiType->getId()]);
				continue;
			}
			
			throw new \InvalidArgumentException('No EiEntry provided for ' . $eiType);
		}
		
		$this->finalizeEiEntryGui($eiEntryGui);
		
		return $eiEntryGui;
	}
	
	/**
	 * @param EiEntry[] $eiEntries
	 * @return EiEntry[]
	 */
	private function catEiEntries($eiEntries) {
		$catEiEntries = [];
		foreach ($eiEntries as $eiEntry) {
			$eiType = $eiEntry->getEiMask()->getEiType();
			if (isset($this->eiGuiFrames[$eiType->getId()])) {
				$catEiEntries[$eiType->getId()] = $eiEntry;
				continue;
			}
			
			while (null !== ($eiType = $eiType->getSuperEiType())) {
				if (!isset($this->eiGuiFrames[$eiType->getId()])) {
					continue;
				}
				
				if (!isset($catEiEntries[$eiType->getId()])) {
					$catEiEntries[$eiType->getId()] = $eiEntry;
				}
				
				break;
			}
		}
		return $catEiEntries;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param int $treeLevel
	 * @throws InaccessibleEiEntryException
	 */
	function createNewEiEntryGui(EiFrame $eiFrame, EiGui $eiGui, int $treeLevel = null) {
		$eiEntryGui = new EiEntryGui($this->contextEiMask, $eiGui, $treeLevel);
		
		foreach ($this->eiGuiFrames as $eiGuiFrame) {
			$eiGuiFrame->applyNewEiEntryGuiTypeDef($eiFrame, $eiEntryGui);
		}
		
		$this->finalizeEiEntryGui($eiEntryGui);
		
		return $eiEntryGui;
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 */
	private function finalizeEiEntryGui($eiEntryGui) {
		if ($this->hasSingleEiGuiFrame()) {
			$eiEntryGui->selectTypeDefByEiTypeId(key($this->eiGuiFrames));
		}
	}

	
	/**
	 * @param EiFrame $eiFrame
	 * @return \rocket\si\control\SiControl[]
	 */
	function createGeneralSiControls(EiFrame $eiFrame) {
		$contextEiTypeId = $this->contextEiMask->getEiType()->getId();
		
		if (isset($this->eiGuiFrames[$contextEiTypeId])) {
			return $this->eiGuiFrames[$contextEiTypeId]->createGeneralSiControls($eiFrame);
		}
		
		return [];
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiControlPath $guiControlPath
	 * @throws UnknownGuiControlException
	 */
	function createGeneralGuiControl(EiFrame $eiFrame, GuiControlPath $guiControlPath) {
		$contextEiTypeId = $this->contextEiMask->getEiType()->getId();
		
		if (isset($this->eiGuiFrames[$contextEiTypeId])) {
			return $this->eiGuiFrames[$contextEiTypeId]->createGeneralGuiControl($eiFrame, $guiControlPath);
		}
		
		throw new UnknownGuiControlException('Unknown GuiControlPath: ' . $guiControlPath);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param GuiControlPath $guiControlPath
	 * @throws UnknownGuiControlException
	 */
	function createEntryGuiControl(EiFrame $eiFrame, EiEntry $eiEntry, GuiControlPath $guiControlPath) {
		$eiTypeId = $eiEntry->getEiType()->getId();
		
		if (isset($this->eiGuiFrames[$eiTypeId])) {
			return $this->eiGuiFrames[$eiTypeId]->createEntryGuiControl($eiFrame, $eiEntry, $guiControlPath);
		}
		
		throw new UnknownGuiControlException('Unknown GuiControlPath: ' . $guiControlPath);
	}
}

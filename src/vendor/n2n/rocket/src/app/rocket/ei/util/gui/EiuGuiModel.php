<?php
namespace rocket\ei\util\gui;

use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\util\entry\EiuObject;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\util\EiuPerimeterException;
use rocket\ei\manage\gui\EiGuiUtil;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\gui\EiGui;

class EiuGuiModel {
	private $eiGuiModel;
	private $eiuGuiFrames;
	private $eiuAnalyst;
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGuiModel $eiGuiModel, EiuAnalyst $eiuAnalyst) {
		$this->eiGuiModel = $eiGuiModel;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
// 	/**
// 	 * @return \rocket\ei\util\frame\EiuFrame
// 	 */
// 	public function getEiuFrame() {
// 		if ($this->eiuFrame !== null) {
// 			return $this->eiuFrame;
// 		}
		
// 		if ($this->eiuAnalyst !== null) {
// 			$this->eiuFrame = $this->eiuAnalyst->getEiuFrame(false);
// 		}
		
// 		if ($this->eiuFrame === null) {
// 			$this->eiuFrame = new EiuFrame($this->eiGuiFrame->getEiFrame(), $this->eiuAnalyst);
// 		}
		
// 		return $this->eiuFrame;
// 	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function getEiGuiModel() {
		return $this->eiGuiModel;
	}
	
	function newGui() {
		return new EiuGui(new EiGui($this->eiGuiModel), $this, $this->eiuAnalyst);
	}
	
	function createSiDeclaration() {
		return $this->eiGuiModel->createSiDeclaration();
	}
	
	/**
	 * @return \rocket\si\control\SiControl[]
	 */
	function createGeneralSiControls() {
		return $this->eiGuiModel->createGeneralSiControls($this->eiuAnalyst->getEiFrame(true));
	}
	
	function guiFrames() {
		if ($this->eiuGuiFrames !== null) {
			return $this->eiuGuiFrames;
		}
		
		$this->eiuGuiFrames = [];
		foreach ($this->eiGuiModel->getEiGuiFrames() as $key => $eiGuiFrame) {
			$this->eiuGuiFrames[$key] = new EiuGuiFrame($eiGuiFrame, $this, $this->eiuAnalyst);
		}
		return $this->eiuGuiFrames;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	function copy(bool $bulky, bool $readOnly, array $defPropPathsArg = null, bool $guiStructureDeclarationsRequired = true) {
		$viewMode = ViewMode::determine($bulky, $readOnly, ViewMode::isAdd($this->eiGuiModel->getViewMode()));
		$cache = $this->eiuAnalyst->getManageState()->getEiGuiModelCache();
		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
		
		$newEiGuiModel = $cache->createMultiEiGuiModel($this->eiGuiModel->getContextEiMask(), $viewMode, 
				$this->eiGuiModel->getEiTypes(), $defPropPaths);
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		foreach ($this->eiGuiModel->getEiEntryGuis() as $eiEntryGui) {
			$newEiEntryGui = $newEiGuiModel->appendEiEntryGui($eiFrame, $eiEntryGui->getEiEntries(), $eiEntryGui->getTreeLevel());
			if ($eiEntryGui->isTypeDefSelected()) {
				$newEiEntryGui->selectTypeDefByEiTypeId($eiEntryGui->getSelectedTypeDef()->getEiType()->getId());
			}
		}
		
		return new EiuGuiModel($newEiGuiModel, $this->eiuAnalyst);
	}
	
	function newEntryGui($eiEntryArg = null) {
		$eiGui = new EiGui($this->eiGuiModel);
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		if ($eiEntryArg === null) {
			$eiGui->appendNewEiEntryGui($eiFrame);
		} else {
			$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg, 'eiEntryArg', true);
			$eiGui->appendEiEntryGui($eiFrame, [$eiEntry]);
		}
		
		return (new EiuGui($eiGui, $this, $this->eiuAnalyst))->entryGui();
	}
}
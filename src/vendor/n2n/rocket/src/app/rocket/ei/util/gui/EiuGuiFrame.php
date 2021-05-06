<?php
namespace rocket\ei\util\gui;

use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\gui\EiGuiSiFactory;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\EiuAnalyst;
use n2n\l10n\N2nLocale;
use n2n\util\ex\NotYetImplementedException;
use rocket\ei\util\spec\EiuProp;
use rocket\si\meta\SiDeclaration;
use rocket\si\meta\SiMaskDeclaration;

class EiuGuiFrame {
	private $eiGuiFrame;
	private $eiuGuiModel;
	private $eiuAnalyst;
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 * @param EiuFrame $eiuFrame
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiGuiFrame $eiGuiFrame, ?EiuGuiModel $eiuGuiModel, EiuAnalyst $eiuAnalyst) {
		$this->eiGuiFrame = $eiGuiFrame;
		$this->eiuGuiModel = $eiuGuiModel;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	function getEiGuiFrame() {
		return $this->eiGuiFrame;
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGuiModel
	 */
	function guiModel() {
		if ($this->eiuGuiModel === null) {
			$this->eiuGuiModel = new EiuGuiModel($this->eiGuiFrame->getEiGuiModel(), $this->eiuAnalyst);
		}
		
		return $this->eiuGuiModel;
	}
	
// 	/**
// 	 * @return \rocket\ei\util\frame\EiuFrame
// 	 */
// 	private function getEiuFrame() {
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
	 * @return number
	 */
	public function getViewMode() {
		return $this->eiGuiFrame->getEiGuiModel()->getViewMode();
	}
	
	/**
	 * @param DefPropPath|string $eiPropPath
	 * @param bool $required
	 * @return string|null
	 */
	public function getPropLabel($defPropPath, N2nLocale $n2nLocale = null, bool $required = false) {
		$defPropPath = DefPropPath::create($defPropPath);
		if ($n2nLocale === null) {
			$n2nLocale = $this->eiuAnalyst->getN2nContext()->getN2nLocale();
		}
		
// 		if (null !== ($displayItem = $this->getDisplayItemByDefPropPath($eiPropPath))) {
// 			return $displayItem->translateLabel($n2nLocale);
// 		}
		
		if (null !== ($guiProp = $this->getGuiPropWrapperByDefPropPath($defPropPath, $required))) {
			return $guiProp->getDisplayLabel();
		}
		
		return null;
	}
	
	/**
	 * @param DefPropPath|string $defPropPath
	 * @param bool $required
	 * @return \rocket\ei\util\spec\EiuProp
	 */
	function getProp($defPropPath, bool $required = true) {
		return new EiuProp($this->getGuiPropWrapperByDefPropPath($defPropPath, $required), null, $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\ei\EiPropPath[]
	 */
	function getEiPropPaths() {
		return $this->eiGuiFrame->getEiPropPaths();
	}
	
	function getDefPropPaths() {
		return $this->eiGuiFrame->getDefPropPaths();
	}
	
// 	function newEntryGui($eiEntryArg) {
// 		$eiEntry = EiuAnalyst::buildEiEntryFromEiArg($eiEntryArg, 'eiEntryArg');
		
// 		$eiEntryGui = $this->eiGuiFrame->createEiEntryGuiVariation($this->eiuAnalyst->getEiFrame(true), $eiEntry);
		
// 		return new EiuEntryGui($eiEntryGui, null, $this, $this->eiuAnalyst);
// 	}
	
	/**
	 * @param DefPropPath|string $prefixDefPropPath
	 * @return DefPropPath[]
	 */
	function getForkedDefPropPaths($prefixDefPropPath) {
		$prefixDefPropPath = DefPropPath::create($prefixDefPropPath);
		$size = $prefixDefPropPath->size();
		
		$forkedDefPropPaths = [];
		foreach ($this->eiGuiFrame->filterDefPropPaths($prefixDefPropPath) as $defPropPath) {
			$forkedDefPropPaths[] = $defPropPath->subDefPropPath($size);
		}
		return $forkedDefPropPaths;
	}
	
	/**
	 * @param DefPropPath|string $prefixDefPropPath
	 * @return \rocket\ei\EiPropPath[]
	 */
	function getForkedEiPropPaths($prefixDefPropPath) {
		$prefixDefPropPath = DefPropPath::create($prefixDefPropPath);
		
		$forkedEiPropPaths = [];
		foreach ($this->getForkedDefPropPaths($prefixDefPropPath) as $defPropPath) {
			$forkedEiPropPaths[] = $defPropPath->getFirstEiPropPath();
		}
		return $forkedEiPropPaths;
	}
	
	/**
	 * @param DefPropPath|string $eiPropPath
	 * @param bool $required
	 * @throws \InvalidArgumentException
	 * @throws GuiException
	 * @return \rocket\ei\manage\gui\GuiProp|null
	 */
	private function getGuiPropWrapperByDefPropPath($defPropPath, bool $required = false) {
		$defPropPath = DefPropPath::create($defPropPath);
		
		try {
			return $this->eiGuiFrame->getGuiDefinition()->getGuiPropWrapperByDefPropPath($defPropPath);
		} catch (GuiException $e) {
			if (!$required) return null;
			throw $e;
		}
	}
	
	/**
	 * @param DefPropPath|string $defPropPath
	 * @return \rocket\ei\manage\gui\DisplayDefinition|null
	 */
	function getDisplayDefinition($defPropPath, bool $required = false) {
		$defPropPath = DefPropPath::create($defPropPath);
		
		if (!$required && !$this->eiGuiFrame->containsDisplayDefintion($defPropPath)) {
			return null;
		}
		
		return $this->eiGuiFrame->getDisplayDefintion($defPropPath);
	}
		
// 	/**
// 	 * @param DefPropPath|string $eiPropPath
// 	 * @param bool $required
// 	 * @throws \InvalidArgumentException
// 	 * @return \rocket\ei\mask\model\DisplayItem
// 	 */
// 	public function getDisplayItemByDefPropPath($eiPropPath) {
// 		$eiPropPath = DefPropPath::create($eiPropPath);
		
// 		$displayStructure = $this->eiGuiFrame->getEiGuiSiFactory()->getDisplayStructure();
// 		if ($displayStructure !== null) {
// 			return $displayStructure->getDisplayItemByDefPropPath($eiPropPath);
// 		}
// 		return null;
// 	}
	
	/**
	 * @return bool
	 */
	public function isBulky() {
		return (bool) ($this->getViewMode() & ViewMode::bulky());	
	}
	
	/**
	 * @return bool
	 */
	public function isCompact() {
		return (bool) ($this->getViewMode() & ViewMode::compact());
	}
	
	/**
	 * @return boolean
	 */
	public function isReadOnly() {
		return (bool) ($this->getViewMode() & ViewMode::read());
	}
	
// 	public function initWithUiCallback(\Closure $viewFactory, array $defPropPaths) {
// 		$defPropPaths = DefPropPath::createArray($defPropPaths);
		
// 		$this->eiGuiFrame->init(new CustomGuiViewFactory($viewFactory), $defPropPaths);
// 	}

	/**
	 * @return \rocket\si\meta\SiDeclaration
	 */
	function createSiDeclaration() {
		return new SiDeclaration(ViewMode::createSiStyle($this->getViewMode()), [new SiMaskDeclaration(
				$this->eiGuiFrame->createSiMask($this->eiuAnalyst->getN2nContext(true)->getN2nLocale()),
				null)]);
	}
}

class CustomGuiViewFactory implements EiGuiSiFactory {
	private $factory;
	
	public function __construct(\Closure $factory) {
		$this->factory = $factory;
	}
	
// 	public function createUiComponent(array $eiEntryGuis, ?HtmlView $contextView): UiComponent {
// 		$uiComponent = $this->factory->call(null, $eiEntryGuis, $contextView);
// 		ArgUtils::valTypeReturn($uiComponent, [UiComponent::class, 'scalar'], null, $this->factory);
		
// 		if (is_scalar($uiComponent)) {
// 			$uiComponent = new HtmlSnippet($uiComponent);
// 		}
		
// 		return $uiComponent;
// 	}
	
// 	public function createSiDeclaration(): SiDeclaration {
// 		throw new NotYetImplementedException();
// 	}
	
	public function getSiStructureDeclarations(): array {
		throw new NotYetImplementedException();
	}

	public function getSiProps(): array {
		throw new NotYetImplementedException();
	}


}
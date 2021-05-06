<?php
namespace rocket\ei\util\spec;

use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\ei\manage\generic\UnknownGenericEiPropertyException;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\manage\generic\UnknownScalarEiPropertyException;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\ei\EiEngine;
use rocket\ei\util\filter\EiuFilterForm;
use rocket\ei\util\filter\controller\ScrFilterPropController;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\util\filter\controller\FilterJhtmlHook;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\ei\util\privilege\EiuPrivilegeForm;
use rocket\ei\manage\security\privilege\data\PrivilegeSetting;
use rocket\ei\util\sort\EiuSortForm;
use rocket\ei\manage\critmod\sort\SortSettingGroup;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\gui\GuiDefinitionListener;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\EiGuiListener;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\manage\frame\EiForkLink;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\util\gui\EiuGuiFrame;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\util\gui\EiuGuiModel;

class EiuEngine {
	private $eiEngine;
	private $eiuType;
	private $eiuMask;
	private $eiuAnalyst;
	
	public function __construct(EiEngine $eiEngine, EiuMask $eiuMask = null, EiuAnalyst $eiuAnalyst) {
		$this->eiEngine = $eiEngine;
		$this->eiuMask = $eiuMask;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\EiEngine
	 */
	public function getEiEngine() {
		return $this->eiEngine;
	}
	
	/**
	 * @return EiuMask 
	 */
	public function mask() {
		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		return $this->eiuMask = new EiuMask($this->eiEngine->getEiMask(), $this, $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuType
	 */
	public function type() {
		if ($this->eiuType !== null) {
			return $this->eiuType;
		}
		
		return $this->eiuType = new EiuType($this->getEiType(), $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\ei\EiType
	 */
	private function getEiType() {
		return $this->eiEngine->getEiMask()->getEiType();
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	public function supremeEngine() {
		if (!$this->eiEngine->hasSuperEiEngine()) {
			return $this;
		}
		
		return new EiuEngine($this->eiEngine->getSupremeEiEngine(), null, $this->eiuAnalyst);
	}
	
	public function removeGuiProp($defPropPath) {
		$this->getGuiDefinition()->removeGuiPropByPath(DefPropPath::create($defPropPath));
	}
	
	/**
	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
	 * @return bool
	 */
	public function containsDraftProperty($eiPropArg) {
		return $this->eiEngine->getDraftDefinition()->containsEiPropPath(EiPropPath::create($eiPropArg));
	}
	
	public function getGuiPropOptions(N2nLocale $n2nLocale = null) {
		/**
		 * @var ManageState $ms
		 */
		$ms = $this->eiuAnalyst->getN2nContext(true)->lookup(ManageState::class);
		
		$n2nLocale = $n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale();
		
		return array_map(
				function ($labelLstr) use ($n2nLocale) { return $labelLstr->t($n2nLocale); },
				$ms->getDef()->getGuiDefinition($this->eiEngine->getEiMask())->getLabelLstrs());
	}
	
	/**
	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
	 * @return bool
	 */
	public function containsGenericEiProperty($eiPropArg) {
		return $this->eiEngine->getGenericEiDefinition()->containsEiPropPath(EiPropPath::create($eiPropArg));
	}
	
	/**
	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
	 * @return GenericEiProperty
	 * @throws UnknownGenericEiPropertyException
	 */
	public function getGenericEiProperty($eiPropArg, bool $required = true) {
		try {
			return $this->eiEngine->getGenericEiDefinition()->getGenericEiPropertyByEiPropPath(
					EiPropPath::create($eiPropArg));
		} catch (UnknownGenericEiPropertyException $e) {
			if (!$required) return null;
			
			throw $e;
		}
	}
	
	/**
	 * @return string[]
	 */
	public function getGenericEiPropertyOptions() {
		$options = array();
		foreach ($this->eiEngine->getGenericEiDefinition()->getGenericEiProperties() as $genericEiProperty) {
			$str = (string) $genericEiProperty->getEiPropPath();
			$options[$str] = $str . ' (' . $genericEiProperty->getLabelLstr()->t(N2nLocale::getAdmin()) . ')';		
		}
		return $options;
	}
	
	/**
	 * @param EiPropPath|string $eiPropPath
	 * @return boolean
	 */
	function containsScalarEiProperty($eiPropPath) {
		return $this->eiEngine->getScalarEiDefinition()->containsEiPropPath(EiPropPath::create($eiPropPath));
	}
	
	/**
	 * @param mixed $eiPropArg See {@see EiPropPath::create()}
	 * @return ScalarEiProperty
	 * @throws UnknownScalarEiPropertyException
	 */
	public function getScalarEiProperty($eiPropArg, bool $required = true) {
		try {
			return $this->eiEngine->getScalarEiDefinition()->getScalarEiPropertyByEiPropPath(
					EiPropPath::create($eiPropArg));
		} catch (UnknownScalarEiPropertyException $e) {
			if (!$required) return null;
			
			throw $e;
		}
	}
	
	/**
	 * @return \rocket\ei\manage\generic\ScalarEiProperty[]
	 */
	function getScalarEiProperties() {
		return $this->eiEngine->getScalarEiDefinition()->getScalarEiProperties();
	}
	
	/**
	 * @return string[]
	 */
	public function getScalarEiPropertyOptions() {
		$options = array();
		foreach ($this->eiEngine->getScalarEiDefinition()->getScalarEiProperties() as $scalarEiProperty) {
			$str = (string) $scalarEiProperty->getEiPropPath();
			$options[$str] = $str . ' (' . $scalarEiProperty->getLabelLstr()->t(N2nLocale::getAdmin()) . ')';
		}
		return $options;
	}
	
	/**
	 * @return ManageState
	 */
	private function getManageState() {
		return $this->eiuAnalyst->getN2nContext(true)->lookup(ManageState::class);
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	public function getFilterDefinition() {
		return $this->getManageState()->getDef()->getFilterDefinition($this->eiEngine->getEiMask());
	}
	/**
	 * @return boolean
	 */
	public function hasFilterProps() {
		return !$this->getFilterDefinition()->isEmpty();
	}
	
	/**
	 * @return \rocket\ei\manage\security\filter\SecurityFilterDefinition
	 */
	public function getSecurityFilterDefinition() {
		return $this->getManageState()->getDef()->getSecurityFilterDefinition($this->eiEngine->getEiMask());
	}
	
	/**
	 * @return boolean
	 */
	public function hasSecurityFilterProps() {
		return !$this->getSecurityFilterDefinition()->isEmpty();
	}
	
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortDefinition
	 */
	public function getSortDefinition() {
		return $this->getManageState()->getDef()->getSortDefinition($this->eiEngine->getEiMask());
	}
	
	/**
	 * @return boolean
	 */
	public function hasSortProps() {
		return !$this->getSortDefinition()->isEmpty();
	}
	
	/**
	 * @return GuiDefinition 
	 */
	public function getGuiDefinition() {
		return $this->getManageState()->getDef()->getGuiDefinition($this->eiEngine->getEiMask());
	}
	
	/**
	 * @return \rocket\ei\manage\security\privilege\PrivilegeDefinition
	 */
	public function getPrivilegeDefinition() {
		return $this->getManageState()->getDef()->getPrivilegeDefinition($this->eiEngine->getEiMask());
	}
	
// 	/**
// 	 * @return \rocket\ei\manage\gui\GuiDefinition
// 	 */
// 	public function getGuiDefinition() {
// 		return $this->getManageState()->getDef()->getGuiDefinition($this->eiEngine->getEiMask());
// 	}
	
	/**
	 * @return \rocket\ei\manage\idname\IdNameDefinition
	 */
	public function getIdNameDefinition() {
		return $this->getManageState()->getDef()->getIdNameDefinition($this->eiEngine->getEiMask());
	}
	
	
	/**
	 * @param mixed $eiObjectArg
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString($eiObjectArg, bool $determineEiMask = true,
			N2nLocale $n2nLocale = null): string {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', $this->getEiType());
				
		$eiMask = $this->getEiEngine()->getEiMask();
		if ($determineEiMask) {
			$eiMask = $eiMask->determineEiMask($eiObject->getEiEntityObj()->getEiType());
		}
		
		$n2nContext = $this->eiuAnalyst->getN2nContext(true);
		return $this->getManageState()->getDef()->getIdNameDefinition($eiMask)
				->createIdentityString($eiObject, $n2nContext,
						$n2nLocale ?? $n2nContext->getN2nLocale());
	}
	
	public function onNewGui(\Closure $callback) {
		$this->getGuiDefinition()->registerGuiDefinitionListener(new ClosureGuiDefinitionListener($callback));
	}
	
	public function onNewEntryGui(\Closure $callback) {
		$this->getGuiDefinition()->registerGuiDefinitionListener(new ClosureEiGuiListener($callback));
	}
	
	/**
	 * @param mixed $eiPropPath
	 * @throws \InvalidArgumentException
	 * @return boolean
	 */
	public function containsGuiProp($eiPropPath) {
		return $this->getGuiDefinition()->containsGuiProp(DefPropPath::create($eiPropPath));
	}

	/**
	 * @param mixed $eiPropPath
	 * @throws \InvalidArgumentException
	 * @throws GuiException
	 * @return EiPropPath
	 */
	public function eiPropPathToEiPropPath($eiPropPath) {
		return $this->getGuiDefinition()->eiPropPathToEiPropPath(DefPropPath::create($eiPropPath));
	}
	
	
	/**
	 * @param FilterSettingGroup|null $rootGroup
	 * @return \rocket\ei\util\filter\EiuFilterForm
	 */
	public function newFilterForm(FilterSettingGroup $rootGroup = null) {
		return $this->createEiuFilterForm(
				$this->getFilterDefinition(),
				ScrFilterPropController::buildFilterJhtmlHook(
						$this->eiuAnalyst->getN2nContext(true)->lookup(ScrRegistry::class), 
						$this->eiEngine->getEiMask()->getEiTypePath()),
				$rootGroup);
	}
		
	/**
	 * @param FilterSettingGroup|null $rootGroup
	 * @return \rocket\ei\util\filter\EiuFilterForm
	 */
	public function newSecurityFilterForm(FilterSettingGroup $rootGroup = null) {
		return $this->createEiuFilterForm(
				$this->getSecurityFilterDefinition()->toFilterDefinition(),
				ScrFilterPropController::buildSecurityFilterJhtmlHook(
						$this->eiuAnalyst->getN2nContext(true)->lookup(ScrRegistry::class),
						$this->eiEngine->getEiMask()->getEiTypePath()),
				$rootGroup);
				
	}
	
	/**
	 * @param FilterDefinition $fd
	 * @param FilterJhtmlHook $fjh
	 * @param FilterSettingGroup|null $rg
	 * @return EiuFilterForm
	 */
	private function createEiuFilterForm(FilterDefinition $fd, FilterJhtmlHook $fjh, 
			FilterSettingGroup $rg = null) {
		return new EiuFilterForm($fd, $fjh, $rg, $this->eiuAnalyst);
	}
	
	
	/**
	 * @param SortSettingGroup|null $sortSetting
	 * @return \rocket\ei\util\sort\EiuSortForm
	 */
	public function newSortForm(SortSettingGroup $sortSetting = null) {
		return new EiuSortForm($this->getSortDefinition(), $sortSetting, $this->eiuAnalyst);
	}
	
	/**
	 * @return boolean
	 */
	public function hasPrivileges() {
		return !$this->getPrivilegeDefinition()->isEmpty();
	}
	
	/**
	 * @param PrivilegeSetting|null $privilegeSetting
	 * @return \rocket\ei\util\privilege\EiuPrivilegeForm
	 */
	public function newPrivilegeForm(PrivilegeSetting $privilegeSetting = null) {
		return new EiuPrivilegeForm($this->getPrivilegeDefinition(), $privilegeSetting, $this->eiuAnalyst);	
	}
	
// 	/**
// 	 * @param object $eiObjectArg
// 	 * @param N2nLocale $n2nLocale
// 	 * @return string
// 	 */
// 	public function createIdentityString(object $eiObjectArg, N2nLocale $n2nLocale = null): string {
// 		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', $this->eiuMask->getEiMask()->getEiType());
// 		return $this->getGuiDefinition()
// 				->createIdentityString($eiObject, $this->eiuAnalyst->getN2nContext(true),
// 						$n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
// 	}

	/**
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	public function newFrame(EiForkLink $eiForkLink = null) {
		$n2nContext = $this->eiuAnalyst->getN2nContext(true);
		
		$newEiFrame = $this->eiEngine->createEiFrame($n2nContext->lookup(ManageState::class));
		if ($eiForkLink !== null) {
			$newEiFrame->setEiForkLink($eiForkLink);
		}
		
		$newEiuAnalyst = new EiuAnalyst();
		$newEiuAnalyst->applyEiArgs($n2nContext);
		
		return new EiuFrame($newEiFrame, $newEiuAnalyst);
	}
	
	/**
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\util\gui\EiuGuiModel
	 */
	function newGuiModel(int $viewMode, array $defPropPathsArg = null) {
		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
		
		$cache = $this->eiuAnalyst->getManageState()->getEiGuiModelCache();
		$eiGuiModel =  $cache->obtainEiGuiModel($this->eiEngine->getEiMask(), $viewMode, $defPropPaths);
		
		return new EiuGuiModel($eiGuiModel, $this->eiuAnalyst);
	}
	
	/**
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return EiuGuiFrame
	 */
	function newGuiFrame(int $viewMode, array $defPropPaths = null, bool $guiStructureDeclarationsRequired = true) {
		$eiuGui = $this->newGuiModel($viewMode, $defPropPaths, $guiStructureDeclarationsRequired);
		
		return current($eiuGui->guiFrames());
	}
	
	/**
	 * @return EiuGuiModel 
	 */
	function newForgeMultiGuiModel(bool $bulky = true, bool $readOnly = false, array $allowedEiTypesArg = null, 
			array $defPropPathsArg = null) {
		$viewMode = ViewMode::determine($bulky, $readOnly, true);
		$cache = $this->eiuAnalyst->getManageState()->getEiGuiModelCache();
		$allowedEiTypes = EiuAnalyst::buildEiTypesFromEiArg($allowedEiTypesArg);
		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
		$eiGuiModel =  $cache->obtainForgeMultiEiGuiModel($this->eiEngine->getEiMask(), $viewMode, $allowedEiTypes, $defPropPaths);
		return new EiuGuiModel($eiGuiModel, $this->eiuAnalyst);
	}
}

class ClosureGuiDefinitionListener implements GuiDefinitionListener {
	private $callback;
	
	public function __construct(\Closure $callback) {
		$this->callback = $callback;
	}
	
	public function onNewEiGuiFrame(EiGuiFrame $eiGuiFrame) {
		$c = $this->callback;
		$c(new Eiu($eiGuiFrame));
	}
	
	public function onEiGuiFrameInitialized(EiGuiFrame $eiGuiFrame) {
	}

}

class ClosureEiGuiListener implements EiGuiListener, GuiDefinitionListener {
	private $eiEntryGuiCallback;
	
	public function __construct(\Closure $eiEntryGuiCallback) {
		$this->eiEntryGuiCallback = $eiEntryGuiCallback;
	}
	
	public function onNewEiEntryGui(EiEntryGui $eiEntryGui) {
		$c = $this->eiEntryGuiCallback;
		$c(new Eiu($eiEntryGui));
	}
	
	public function onInitialized(EiGuiFrame $eiGuiFrame) {
		
	}

	public function onNewEiGuiFrame(EiGuiFrame $eiGuiFrame) {
		$eiGuiFrame->registerEiGuiListener($this);
	}
	
	public function onGiBuild(EiGuiFrame $eiGuiFrame) {
		
	}


	
}

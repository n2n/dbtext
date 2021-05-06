<?php
namespace rocket\ei\manage\gui;

use n2n\core\container\N2nContext;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\ManagedDef;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;

class EiGuiModelFactory {
	/**
	 * @var N2nContext
	 */
	private $n2nContext;
	/**
	 * @var ManagedDef
	 */
	private $def;
	
	/**
	 * @param ManageState $manageState
	 */
	function __construct(ManageState $manageState) {
		$this->n2nContext = $manageState->getN2nContext();
		$this->def = $manageState->getDef();
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function createEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $defPropPaths, 
			bool $guiStructureDeclarationsRequired) {
		$eiGuiModel = new EiGuiModel($contextEiMask, $viewMode);
		
		$this->applyEiGuiFrame($eiGuiModel, false, $defPropPaths, $guiStructureDeclarationsRequired);
		
		return $eiGuiModel;
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @throws GuiBuildFailedException
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function createForgeEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $defPropPaths, 
			bool $guiStructureDeclarationsRequired) {
		$eiGuiModel = new EiGuiModel($contextEiMask, $viewMode);
		
		$this->applyEiGuiFrame($eiGuiModel, true, $defPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGuiModel->hasEiGuiFrames()) {
			throw new GuiBuildFailedException('Can not build forge EiGuiModel based on ' . $eiGuiModel->getContextEiMask() 
					. ' because its type is abstract.');
		}
		
		return $eiGuiModel;
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @throws GuiBuildFailedException
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function createMultiEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $allowedEiTypes, 
			?array $defPropPaths, bool $guiStructureDeclarationsRequired) {
		$eiGuiModel = new EiGuiModel($contextEiMask, $viewMode);
	
		$this->applyPossibleEiGuiFrames($eiGuiModel, false, $allowedEiTypes, $defPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGuiModel->hasEiGuiFrames()) {
			throw new GuiBuildFailedException('Can not build forge EiGuiModel based on ' . $eiGuiModel->getContextEiMask()
					. ' because its type and sub types do not match the allowed EiTypes: ' . implode(', ', $allowedEiTypes));
		}
		
		return $eiGuiModel;
		
	}
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @throws GuiBuildFailedException
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function createForgeMultiEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $allowedEiTypes,
			?array $defPropPaths, bool $guiStructureDeclarationsRequired) {
		$eiGuiModel = new EiGuiModel($contextEiMask, $viewMode);
		
		$this->applyPossibleEiGuiFrames($eiGuiModel, true, $allowedEiTypes, $defPropPaths, $guiStructureDeclarationsRequired);
		
		if (!$eiGuiModel->hasEiGuiFrames()) {
			throw new GuiBuildFailedException('Can not build forge EiGuiModel based on ' . $eiGuiModel->getContextEiMask()
					. ' because its type and sub types are either abstract or do not match the allowed EiTypes: ' 
					. implode(', ', array_map(function ($arg) { return (string) $arg; }, (array) $allowedEiTypes)));
		}
		
		return $eiGuiModel;
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param EiGuiModel $eiGuiModel
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\manage\gui\EiGuiFrame
	 */
	private function applyEiGuiFrame(EiGuiModel $eiGuiModel, bool $nonAbstractOnly, array $defPropPaths = null,
			bool $guiStructureDeclarationsRequired = true) {
		$contextEiMask = $eiGuiModel->getContextEiMask();
				
		if (!$this->testIfAllowed($contextEiMask->getEiType(), $nonAbstractOnly, null)) {
			return;
		}
		
		$guiDefinition = $this->def->getGuiDefinition($contextEiMask);
		$guiDefinition->createEiGuiFrame($this->n2nContext, $eiGuiModel, $defPropPaths,
				$guiStructureDeclarationsRequired);
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param EiGuiModel $eiGuiModel
	 * @param array $allowedTypeIds
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\manage\gui\EiGuiFrame[]
	 */
	private function applyPossibleEiGuiFrames(EiGuiModel $eiGuiModel, bool $creatablesOnly, array $allowedEiTypes = null,
			array $defPropPaths = null, bool $guiStructureDeclarationsRequired = true) {
		$contextEiMask = $eiGuiModel->getContextEiMask();
		$contextEiType = $contextEiMask->getEiType();
		
		if ($this->testIfAllowed($contextEiType, $creatablesOnly, $allowedEiTypes)) {
			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($contextEiType));
			$eiGuiFrame = $guiDefinition->createEiGuiFrame($this->n2nContext, $eiGuiModel, $defPropPaths,
					$guiStructureDeclarationsRequired);
			$eiGuiModel->putEiGuiFrame($eiGuiFrame);
		}

		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
			if (!$this->testIfAllowed($eiType, $creatablesOnly, $allowedEiTypes)) {
				continue;
			}
			
			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($eiType));
			$eiGuiFrame = $guiDefinition->createEiGuiFrame($this->n2nContext, $eiGuiModel, $defPropPaths,
					$guiStructureDeclarationsRequired);
			$eiGuiModel->putEiGuiFrame($eiGuiFrame);
		}
	}
	
	/**
	 * @param EiType $eiType
	 * @param bool $creatablesOnly
	 * @param EiType[] $allowedTypeIds
	 */
	private function testIfAllowed($eiType, $creatablesOnly, $allowedEiTypes) {
		if ($creatablesOnly && $eiType->isAbstract()) {
			return false;
		}
		
		if ($allowedEiTypes === null) {
			return true;
		}
		
		foreach ($allowedEiTypes as $allowedEiType) {
			if ($eiType->isA($allowedEiType)) {
				return true;
			}
		}
		
		return false;
	}
}
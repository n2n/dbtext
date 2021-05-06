<?php
namespace rocket\ei\manage\gui;

use n2n\core\container\N2nContext;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use rocket\ei\EiPropPath;
use rocket\ei\manage\ManageState;
use n2n\util\type\ArgUtils;

class EiGuiModelCache {
	/**
	 * @var EiGuiModelFactory
	 */
	private $eiGuiModelFactory;
	
	function __construct(ManageState $manageState) {
		$this->eiGuiModelFactory = new EiGuiModelFactory($manageState);
	}

	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param EiType[]|null $allowedEiTypes
	 * @param EiPropPath[]|null $defPropPaths
	 * @return string
	 */
	private function createCacheKey($contextEiMask, $viewMode, $allowedEiTypes, $defPropPaths) {
		$allowedEiTypesHashPart = '';
		if ($allowedEiTypes !== null) {
			$allowedEiTypesHashPart = json_encode(array_map(function($eiType) { return $eiType->getId(); }, $allowedEiTypes));
		}
		
		$defPropPathsHashPart = '';
		if ($defPropPaths !== null) {
			$defPropPathsHashPart = json_encode(array_map(function($defPropPath) { return (string) $defPropPath; }, $defPropPaths));
		}
		
		return (string) $contextEiMask->getEiTypePath() . ' ' . $viewMode . ' ' . $allowedEiTypesHashPart . ' ' 
				. $defPropPathsHashPart;
	}
	
	private $eiGuiModels = [];
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function obtainEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $defPropPaths) {
		$key = $this->createCacheKey($contextEiMask, $viewMode, null, $defPropPaths);
		
		if (isset($this->eiGuiModels[$key])) {
			return $this->eiGuiModels[$key];
		}
		
		return $this->eiGuiModels[$key] = $this->eiGuiModelFactory
				->createEiGuiModel($contextEiMask, $viewMode, $defPropPaths, true);
	}
	
	
	private $forgeEiGuiModels = [];
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @throws GuiBuildFailedException
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function obtainForgeEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $defPropPaths) {
		$key = $this->createCacheKey($contextEiMask, $viewMode, null, $defPropPaths);
		
		if (isset($this->forgeEiGuiModels[$key])) {
			return $this->forgeEiGuiModels[$key];
		}
		
		return $this->forgeEiGuiModels[$key] = $this->eiGuiModelFactory
				->createForgeEiGuiModel($contextEiMask, $viewMode, $defPropPaths, true);
	}
	
	
	private $multiEiGuiModels = [];
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @throws GuiBuildFailedException
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function obtainMultiEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $allowedEiTypes, 
			?array $defPropPaths, bool $guiStructureDeclarationsRequired) {
		ArgUtils::valArray($allowedEiTypes, EiType::class, true);
		$key = $this->createCacheKey($contextEiMask, $viewMode, $allowedEiTypes, $defPropPaths);
		
		if (isset($this->multiEiGuiModels[$key])) {
			return $this->multiEiGuiModels[$key];
		}
				
		return $this->multiEiGuiModels[$key] = $this->eiGuiModelFactory
				->createMultiEiGuiModel($contextEiMask, $viewMode, $allowedEiTypes, $defPropPaths, true);
		
	}
	
	private $forgeMultiEiGuiModels = [];
	
	/**
	 * @param EiMask $contextEiMask
	 * @param int $viewMode
	 * @param array $allowedEiTypeIds
	 * @param array $defPropPaths
	 * @throws GuiBuildFailedException
	 * @return \rocket\ei\manage\gui\EiGuiModel
	 */
	function obtainForgeMultiEiGuiModel(EiMask $contextEiMask, int $viewMode, ?array $allowedEiTypes,
			?array $defPropPaths) {
		ArgUtils::valArray($allowedEiTypes, EiType::class, true);
		$key = $this->createCacheKey($contextEiMask, $viewMode, $allowedEiTypes, $defPropPaths);
		
		if (isset($this->forgeMultiEiGuiModels[$key])) {
			return $this->forgeMultiEiGuiModels[$key];
		}
				
		return $this->forgeMultiEiGuiModels[$key] = $this->eiGuiModelFactory
				->createForgeMultiEiGuiModel($contextEiMask, $viewMode, $allowedEiTypes, $defPropPaths, true);
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
	private function applyPossibleEiGuiFrames(EiGuiModel $eiGuiModel, bool $creatablesOnly, array $allowedTypes = null,
			array $defPropPaths = null, bool $guiStructureDeclarationsRequired = true) {
		$contextEiMask = $eiGuiModel->getContextEiMask();
		$contextEiType = $contextEiMask->getEiType();
		
		if ($this->testIfAllowed($contextEiType, $creatablesOnly, $allowedTypes)) {
			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($contextEiType));
			$eiGuiFrame = $guiDefinition->createEiGuiFrame($this->n2nContext, $eiGuiModel, $defPropPaths,
					$guiStructureDeclarationsRequired);
			$eiGuiModel->putEiGuiFrame($eiGuiFrame);
		}
		
		foreach ($contextEiType->getAllSubEiTypes() as $eiType) {
			if (!$this->testIfAllowed($eiType, $creatablesOnly, $allowedTypes)) {
				continue;
			}
			
			$guiDefinition = $this->def->getGuiDefinition($contextEiMask->determineEiMask($contextEiType));
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
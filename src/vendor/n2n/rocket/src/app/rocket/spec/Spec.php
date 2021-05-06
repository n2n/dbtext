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
namespace rocket\spec;

use n2n\persistence\orm\model\EntityModelManager;
use rocket\ei\component\EiConfigurator;
use n2n\core\container\N2nContext;
use n2n\util\ex\IllegalStateException;
use rocket\spec\extr\SpecExtractionManager;
use rocket\core\model\launch\LaunchPad;
use rocket\core\model\launch\UnknownLaunchPadException;
use rocket\spec\extr\CustomTypeExtraction;
use n2n\util\type\attrs\AttributesException;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\persistence\orm\model\UnknownEntityPropertyException;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;
use n2n\config\InvalidConfigurationException;
use rocket\spec\extr\EiTypeExtraction;
use rocket\ei\mask\EiMask;
use rocket\custom\CustomType;
use rocket\ei\component\EiSetup;
use n2n\util\type\ArgUtils;
use rocket\ei\component\prop\EiProp;
use rocket\ei\component\modificator\EiModificator;
use rocket\ei\component\command\EiCommand;
use rocket\spec\result\EiErrorResult;
use rocket\spec\result\EiPropError;
use rocket\spec\result\EiModificatorError;
use rocket\spec\result\EiCommandError;
use rocket\ei\EiType;
use rocket\ei\UnknownEiTypeException;
use rocket\spec\extr\InitCascade;
use rocket\ei\EiLaunchPad;
use rocket\custom\CustomLaunchPad;

class Spec {	
	private $specExtractionManager;
	private $entityModelManager;
	private $eiSetupQueue;
	/**
	 * @var EiTypeFactory
	 */
	private $eiTypeFactory;
	private $eiErrorResult;
	
	const MODE_NO_SETUP = 1;
	const MODE_ERR_RESULT = 2;
	
	private $eager = false;
	private $launchPads = array();
	private $customTypes = array();
	private $eiTypes = array();
	private $eiTypeCis = array();
	
	/**
	 * @param SpecExtractionManager $specExtractionManager
	 * @param EntityModelManager $entityModelManager
	 */
	public function __construct(SpecExtractionManager $specExtractionManager, EntityModelManager $entityModelManager,
			N2nContext $n2nContext, int $mode) {
		$this->specExtractionManager = $specExtractionManager;
		$this->entityModelManager = $entityModelManager;
		
		if ($mode & self::MODE_ERR_RESULT) {
			$this->eiErrorResult = new EiErrorResult();
		}
		
		if (!($mode & self::MODE_NO_SETUP)) {
			$this->eiSetupQueue = new EiSetupQueue($this->eiErrorResult, $n2nContext);
		}
		
		$this->eiTypeFactory = new EiTypeFactory($this->entityModelManager, $this->getEiSetupQueue(), $this->eiErrorResult);
		
		if (!$this->specExtractionManager->isInitialized()) {
			$this->specExtractionManager->load();
			$this->specExtractionManager->extract();
		}
	}
	
	/**
	 * @return string[]
	 */
	static function getModes() {
		return [self::MODE_NO_SETUP, self::MODE_ERR_RESULT];
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param int $mode
	 * @throws InvalidConfigurationException
	 * @return EiErrorResult|null
	 */
	public function eagerInit() {
		foreach ($this->specExtractionManager->getCustomTypeExtractions() as $customTypeExtraction) {
			$this->initCustomTypeFromExtr($customTypeExtraction);
		}
		
		foreach ($this->specExtractionManager->getEiTypeExtractions() as $eiTypeExtraction) {
			$this->initEiTypeFromExtr($eiTypeExtraction);
		}
		
		$this->triggerEiSetup();
		
		return $this->eiErrorResult;
	}
	
	/**
	 * @return EiErrorResult|null
	 */
	function getEiErrorResult() {
		return $this->eiSetupQueue->getEiErrorResult();
	}
	
	/**
	 * @return \n2n\persistence\orm\model\EntityModelManager
	 */
	public function getEntityModelManager() {
		return $this->entityModelManager;
	}
	
	/**
	 * @return \rocket\spec\extr\SpecExtractionManager
	 */
	public function getSpecExtractionManager() {
// 		if (!$this->specExtractionManager->isInitialized()) {
// 			$this->specExtractionManager->extract();
// 		}
		
		return $this->specExtractionManager;
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsLaunchPad(string $id) {
		return isset($this->launchPads[$id]) || $this->specExtractionManager->containsLaunchPadExtractionTypePath(TypePath::create($id));
	}
	
	/**
	 * @param string $id
	 * @return LaunchPad
	 * @throws UnknownLaunchPadException
	 */
	public function getLaunchPadById(string $id) {
		if (isset($this->launchPads[$id])) {
			return $this->launchPads[$id];
		}

		return $this->initLaunchPadFromTypePath(TypePath::create($id));
	}
	
// 	/**
// 	 * @return LaunchPad[]
// 	 */
// 	public function getLaunchPads() {
// 		return $this->launchPads;
// 	}
	
// 	private function createInvalidLaunchPadConfigurationException($launchPadId, \Exception $previous) {
// 		throw new InvalidLaunchPadConfigurationException('LaunchPad with following id invalid configured: ' 
// 				. $launchPadId, 0, $previous);
// 	}
	
// 	private function createCustomLaunchPad(TypePath $typePath, CustomType $customType, string $label = null) {
// 		return $this->launchPads[(string) $typePath] = new CustomLaunchPad($typePath, $customType, $label);
// 	}
	
// 	private function createEiLaunchPad(TypePath $typePath, EiMask $eiMask, string $label = null) {
// 		return $this->launchPads[(string) $typePath] = new EiLaunchPad($typePath, $eiMask, $label);
// 	}
	
	/**
	 * 
	 */
	public function clear() {
		$this->eager = false;
		$this->customTypes = array();
		$this->eiTypes = array();
		$this->eiTypeCis = array();
		$this->launchPads = array();
	}
	
	
	/**
	 * @param string $class
	 * @return EiType
	 */
	private function initEiTypeFromClassName($className) {
		return $this->initEiTypeFromExtr($this->specExtractionManager->getEiTypeExtractionByClassName($className));
	}
	
	/**
	 * @param EiTypeExtraction $eiTypeExtraction
	 * @throws InvalidConfigurationException
	 * @return EiType
	 */
	private function initEiTypeFromExtr($eiTypeExtraction) {
		$id = $eiTypeExtraction->getId();
		
		if (isset($this->eiTypes[$id])) {
			return $this->eiTypes[$id];
		}
		
		$typePath = new TypePath($id);
		$eiModificationExtractions = $this->specExtractionManager->getEiModificatorExtractionsByEiTypePath($typePath);
		$eiType = $this->eiTypeFactory->create($eiTypeExtraction, $eiModificationExtractions);
		
		$this->eiTypes[$id] = $eiType;
		$this->eiTypeCis[$eiTypeExtraction->getEntityClassName()] = $eiType;
		
		if ($eiType->getEntityModel()->hasSuperEntityModel()) {
			$superClassName = $eiType->getEntityModel()->getSuperEntityModel()->getClass()->getName();
			
			try {
				$eiType->setSuperEiType($this->initEiTypeFromClassName($superClassName));
			} catch (UnknownEiTypeException $e) {
				throw new InvalidConfigurationException('EiType for ' . $eiTypeExtraction->getEntityClassName() 
						. ' requires super EiType for ' . $superClassName);
			}
		}
		
		$this->eiSetupQueue->addEiMask($eiType->getEiMask());
		$this->assembleEiTypeExtensions($eiType->getEiMask(), $typePath);
		
		foreach ($eiType->getEntityModel()->getSubEntityModels() as $subEntityModel) {
			$className = $subEntityModel->getClass()->getName();
			
			if ($this->containsEiTypeClassName($className)) {
				$this->initEiTypeFromClassName($className);
			}
		}
		
		foreach ($this->specExtractionManager->getCascadedEiTypeExtraction($eiTypeExtraction->getEntityClassName(), 
				InitCascade::TYPE_ALWAYS) as $eiTypeExtraction) {
			$this->initEiTypeFromExtr($eiTypeExtraction);	
		}
		
		return $eiType;
	}
	
	private function assembleEiTypeExtensions(EiMask $extendedEiMask, TypePath $extenedTypePath) {
		$eiType = $extendedEiMask->getEiType();
		
		foreach ($this->specExtractionManager->getEiTypeExtensionExtractionsByExtendedEiTypePath($extenedTypePath)
				as $eiTypeExtensionExtraction) {
			$typePath = new TypePath($eiType->getId(), $eiTypeExtensionExtraction->getId());
			$eiModificatorExtractions = $this->specExtractionManager->getEiModificatorExtractionsByEiTypePath($typePath);
			$eiTypeExtension = $this->eiTypeFactory->createEiTypeExtension($extendedEiMask, $eiTypeExtensionExtraction, $eiModificatorExtractions);
			$eiType->getEiTypeExtensionCollection()->add($eiTypeExtension);
			
			$this->eiSetupQueue->addEiMask($eiTypeExtension->getEiMask());
			$this->assembleEiTypeExtensions($eiTypeExtension->getEiMask(), $typePath);
		}
	}
	
	private function initCustomTypeFromExtr(CustomTypeExtraction $customTypeExtraction) {
		$id = $customTypeExtraction->getId();
		
		if (isset($this->customTypes[$id])) {
			return $this->customTypes[$id];
		}
		
		return $this->customTypes[$customTypeExtraction->getId()] = CustomTypeFactory::create($customTypeExtraction);
// 		$typePath = new TypePath($customTypeExtraction->getId());
		
// 		if ($this->specExtractionManager->containsLaunchPadExtractionTypePath($typePath)) {
// 			$launchPadExtraction = $this->specExtractionManager->getLaunchPadExtractionByTypePath($typePath);
// 			$this->createCustomLaunchPad($typePath, $customType, $launchPadExtraction->getLabel());
// 		}
	}
	
	private function initLaunchPadFromTypePath(TypePath $typePath) {
		$launchPadExtraction = $this->specExtractionManager->getLaunchPadExtractionByTypePath($typePath);
		
		if ($typePath->getEiTypeExtensionId() === null && !$this->specExtractionManager->containsEiTypeId($typePath->getTypeId())) {
			return $this->launchPads[(string) $typePath] = new CustomLaunchPad($typePath, $this->getCustomTypeById($typePath->getTypeId()));
		}
		
		$launchPadExtraction = $this->specExtractionManager->getLaunchPadExtractionByEiTypePath($typePath);
			
		$eiType = $this->getEiTypeById($typePath->getTypeId());
		$eiMask = null;
		if ($typePath->getEiTypeExtensionId() !== null) {
			$eiMask = $eiType->getEiTypeExtensionCollection()->getById($typePath->getEiTypeExtensionId())->getEiMask();
		} else {
			$eiMask = $eiType->getEiMask();
		}
		
		return $this->launchPads[(string) $typePath] = new EiLaunchPad($typePath, $eiMask, $launchPadExtraction->getLabel());
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsTypeId(string $id) {
		return isset($this->eiTypes[$id]) || isset($this->customTypes[$id]) || $this->specExtractionManager->containsTypeId($id);
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsCustomTypeId(string $id) {
		return isset($this->customTypes[$id]);
	}
	
	/**
	 *
	 * @param string $id
	 * @return Type
	 * @throws UnknownTypeException
	 * @throws IllegalStateException
	 */
	public function getCustomTypeById(string $id) {
		if (isset($this->customTypes[$id])) {
			return $this->customTypes[$id];
		}
		
		return $this->initCustomTypeFromExtr($this->specExtractionManager->getCustomTypeExtractionById($id));
	}
	
	/**
	 * @return CustomType[]
	 */
	public function getCustomTypes() {
		return $this->customTypes;
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsEiTypeId(string $id) {
		return isset($this->eiTypes[$id]) || $this->specExtractionManager->containsCustomTypeId($id);
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return bool
	 */
	public function containsEiTypeClass(\ReflectionClass $class) {
		return $this->containsEiTypeClassName($class->getName());
	}
	
	/**
	 * @param string $className
	 * @return bool
	 */
	public function containsEiTypeClassName(string $className) {
		return isset($this->eiTypeCis[$className])
				|| $this->specExtractionManager->containsEiTypeEntityClassName($className);
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\ei\EiType
	 * @throws UnknownTypeException
	 * @throws IllegalStateException
	 */
	public function getEiTypeById(string $id) {
		if (isset($this->eiTypes[$id])) {
			return $this->eiTypes[$id];
		}
		
		$eiType = $this->initEiTypeFromExtr($this->specExtractionManager->getEiTypeExtractionById($id));
		$this->triggerEiSetup();
		return $eiType;
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\ei\EiType
	 */
	public function getEiTypeByClass(\ReflectionClass $class) {
		return $this->getEiTypeByClassName($class->getName());
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return \rocket\ei\EiType
	 */
	public function getEiTypeByClassName(string $className) {
		if (isset($this->eiTypeCis[$className])) {
			return $this->eiTypeCis[$className];
		}
		
		$eiType = $this->initEiTypeFromClassName($className);
		
		$this->triggerEiSetup();
		
		return $eiType;
	}
	
	private function triggerEiSetup() {
		if ($this->eiSetupQueue !== null) {
			$this->eiSetupQueue->trigger();
		}
	}
	
	/**
	 * @param object $entityObj
	 * @throws \InvalidArgumentException
	 * @throws UnknownTypeException
	 * @return \rocket\ei\EiType
	 */
	public function getEiTypeOfObject($entityObj) {
		ArgUtils::valType($entityObj, 'object');
		
		$class = new \ReflectionClass($entityObj);
		$orgClassName = $class->getName();
		
		do {
			$className = $class->getName();
			if ($this->containsEiTypeClassName($className)) {
				$eiType = $this->initEiTypeFromClassName($className);
				$this->triggerEiSetup();
				return $eiType;
			}
		} while ($class = $class->getParentClass());
		
		$this->specExtractionManager->getEiTypeExtractionByClassName($orgClassName);
	}
	
	
	/**
	 * @return \rocket\ei\EiType[]
	 */
	public function getEiTypes() {
		return $this->eiTypes;
	}
	
	/**
	 * @return \rocket\spec\EiSetupQueue
	 */
	public function getEiSetupQueue() {
		return $this->eiSetupQueue;
	}
}

class EiSetupQueue {
	private $eiErrorResult;
	private $n2nContext;
	
	private $propIns = array();
	private $eiMasks = array();
	private $eiPropSetupTasks = array();
	private $eiModificatorSetupTasks = array();
	private $eiCommandSetupTasks = array();
	
	public function __construct(?EiErrorResult $eiErrorResult, N2nContext $n2nContext) {
		$this->eiErrorResult = $eiErrorResult;
		$this->n2nContext = $n2nContext;
	}
		
	public function addPropIn(PropIn $propIn) {
		$this->propIns[] = $propIn;
	}
	
	public function addEiMask(EiMask $eiMask) {
		$this->eiMasks[] = $eiMask;
	}
	
	function getEiMasks() {
		return $this->eiMasks;
	}
	
	public function addEiPropConfigurator(EiProp $eiProp, EiConfigurator $eiConfigurator) {
		$this->eiPropSetupTasks[] = new EiPropSetupTask($eiProp, $eiConfigurator);
	}
	
	public function addEiModificatorConfigurator(EiModificator $eiModificator, EiConfigurator $eiConfigurator) {
		$this->eiModificatorSetupTasks[] = new EiModificatorSetupTask($eiModificator, $eiConfigurator);
	}
	
	public function addEiCommandConfigurator(EiCommand $eiCommand, EiConfigurator $eiConfigurator) {
		$this->eiCommandSetupTasks[] = new EiCommandSetupTask($eiCommand, $eiConfigurator);
	}
	
	public function getPropIns() {
		return $this->propIns;
	}
	
	public function setPropIns(array $propIns) {
		$this->propIns = $propIns;
	}
	
// 	public function getEiConfigurators() {
// 		return $this->eiConfigurators;
// 	}
	
// 	public function setEiConfigurators(array $eiConfigurators) {
// 		$this->eiConfigurators = $eiConfigurators;
// 	}
	
	public function clear()  {
		$this->propIns = array();
		//$this->eiConfigurators = array();
		$this->eiPropSetupTasks = array();
		$this->eiModificatorSetupTasks = array();
		$this->eiCommandSetupTasks = array();
	}
		
	public function trigger() {
		$this->propIns();
				
// 		while (null !== ($eiConfigurator = array_shift($this->eiConfigurators))) {
// 			$this->setup($n2nContext, $eiConfigurator);
// 		}
		$eiMaskCallbackProcess = new EiMaskCallbackProcess($this);
		
		while (null !== ($eiPropSetupTask = array_shift($this->eiPropSetupTasks))) {
			try {
				$this->setup($eiPropSetupTask->getEiConfigurator());
				$eiMaskCallbackProcess->check($eiPropSetupTask->getEiProp());
			} catch (\Throwable $t) {
				if ($this->eiErrorResult === null) {
					throw $t;
				}
				
				$this->eiErrorResult->putEiPropError(EiPropError::fromEiProp($eiPropSetupTask->getEiProp(), $t));
			}
		}
		
		while (null !== ($eiModificatorSetupTask = array_shift($this->eiModificatorSetupTasks))) {
			try {
				$this->setup($eiModificatorSetupTask->getEiConfigurator());
				$eiMaskCallbackProcess->check(null, $eiModificatorSetupTask->getEiModificator());
			} catch (\Throwable $t) {
				if ($this->eiErrorResult === null) {
					throw $t;
				}
				
				$this->eiErrorResult->putEiModificatorError(
						EiModificatorError::fromEiModificator($eiModificatorSetupTask->getEiModificator(), $t));
			}
		}
		
 		while (null !== ($eiCommandSetupTask = array_shift($this->eiCommandSetupTasks))) {
			try {
				$this->setup($eiCommandSetupTask->getEiConfigurator());
				$eiMaskCallbackProcess->check(null, null, $eiCommandSetupTask->getEiCommand());
			} catch (\Throwable $e) {
				if ($this->eiErrorResult === null) {
					throw $e;
				}
				
				$this->eiErrorResult->putEiCommandError(
						EiCommandError::fromEiCommand($eiCommandSetupTask->getEiCommand(), $e));
			}
		}
		
		while (null !== ($eiMask = array_shift($this->eiMasks))) {
			$eiMask->setupEiEngine();
		}
		
		$eiMaskCallbackProcess->run($this->eiErrorResult);
		
// 		foreach ($cbrs as $cbr) {
// 			try {
// 				foreach ($cbr['cb'] as $c) {
// 					$c($cbr['em']->getEiEngine());
// 				}
// 			} catch (InvalidConfigurationException $e) {
// 				throw new InvalidEiMaskConfigurationException('Failed to setup EiMask.', 0, $e);
// 			}
// 		}
		
		return $this->eiErrorResult;
	}

// 	public function exclusiveTriggerForEiType($eiTypeId, N2nContext $n2nContext) {
// 		$this->exclusivePropInForEiType($eiTypeId, $n2nContext);
	
// 		foreach ($this->eiConfigurators as $key => $eiConfigurator) {
// 			if ($eiConfigurator->getEiComponent()->getEiEngine()->getEiMask()->getEiType()->getId() !== $eiTypeId) {
// 				continue;
// 			}
				
// 			$this->setup($n2nContext, $eiConfigurator);
// 			unset($this->eiConfigurators[$key]);
// 		}
// 	}
	
	/**
	 * 
	 * @param EiConfigurator $eiConfigurator
	 * @throws InvalidEiMaskConfigurationException
	 */
	private function setup($eiConfigurator) {
		$eiSetup = new EiSetup($this->n2nContext, $eiConfigurator->getEiComponent());
		
		try {
			try {
				$eiConfigurator->setup($eiSetup);
			} catch (AttributesException $e) {
				throw $eiSetup->createException(null, $e);
			} catch (\InvalidArgumentException $e) {
				throw $eiSetup->createException(null, $e);
			}
		} catch (InvalidConfigurationException $e) {
			throw new InvalidEiMaskConfigurationException('Failed to setup EiMask for: ' 
					. $eiConfigurator->getEiComponent() . '.', 0, $e);
		}
	}
	
	public function propIns() {
		while (null !== ($propIns = array_shift($this->propIns))) {
			$propIns->invoke($this->eiErrorResult);
		}
	}
	
	public function exclusivePropInForEiType($eiTypeId) {
		foreach ($this->propIns as $key => $propIn) {
			if ($propIn->getEiType()->getId() !== $eiTypeId) {
				continue;
			}
		
			$propIn->invoke($this->eiErrorResult);
			unset($this->propIns[$key]);
		}
	}
}

class EiMaskCallbackProcess {
	private $spec;
	private $callbackConfigurations = [];
	
	function __construct(EiSetupQueue $spec) {
		$this->spec = $spec;
	}
	
	function check(EiProp $eiProp = null, EiModificator $eiModificator = null, EiCommand $eiCommand = null) {
		foreach ($this->spec->getEiMasks() as $eiMask) {
			$this->checkCallbacks($eiMask, $eiProp, $eiModificator, $eiCommand);
		}
	}
	
	function run(EiErrorResult $eiErrorResult = null) {
		foreach ($this->callbackConfigurations as $callbackConfiguration) {
			try {
				try {
					$callbackConfiguration['callback']($callbackConfiguration['eiMask']->getEiEngine());
				} catch (InvalidConfigurationException $e) {
						throw new InvalidEiMaskConfigurationException('Failed to setup EiMask.', 0, $e);
				}
			} catch (\Throwable $t) {
				if (null === $eiErrorResult) {
					throw $t;
				}
				
				if (null !== $callbackConfiguration['eiProp']) {
					$eiErrorResult->putEiPropError(EiPropError::fromEiProp($callbackConfiguration['eiProp'], $t));
				}
				
				if (null !== $callbackConfiguration['eiModificator']) {
					$eiErrorResult->putEiModificatorError(EiModificatorError::fromEiModificator($callbackConfiguration['eiModificator'], $t));
				}
				
				if (null !== $callbackConfiguration['eiCommand']) {
					$eiErrorResult->putEiCommandError(EiCommandError::fromEiCommand($callbackConfiguration['eiCommand'], $t));
				}
			}
		}
	}
	
	private function checkCallbacks(EiMask $eiMask, EiProp $eiProp = null, 
			EiModificator $eiModificator = null, EiCommand $eiCommand = null) {
		//$newCallbacks = [];
		foreach ($eiMask->getEiEngineSetupCallbacks() as $objHash => $callback) {
			if (isset($this->callbackConfigurations[$objHash])) {
				continue;
			}
			
			//$newCallbacks[] = $callback;
			$this->callbackConfigurations[$objHash] = ['callback' => $callback, 'eiMask' => $eiMask, 
					'eiProp' => $eiProp, 'eiModificator' => $eiModificator, 'eiCommand' => $eiCommand];
		}
		
		// return $newCallbacks;
	}
}

class PropIn {
	private $eiType;
	private $eiPropConfigurator;
	private $objectPropertyName;
	private $entityPropertyName;
	private $contextEntityPropertyNames;

	public function __construct(EiType $eiType, $eiPropConfigurator, $objectPropertyName, $entityPropertyName, array $contextEntityPropertyNames) {
		$this->eiType = $eiType;
		$this->eiPropConfigurator = $eiPropConfigurator;
		$this->objectPropertyName = $objectPropertyName;
		$this->entityPropertyName = $entityPropertyName;
		$this->contextEntityPropertyNames = $contextEntityPropertyNames;
	}

	public function getEiType() {
		return $this->eiType;
	}
	
	public function invoke(EiErrorResult $eiErrorResult = null) {
		$entityPropertyCollection = $this->eiType->getEntityModel();
		$class = $entityPropertyCollection->getClass();
		
		$contextEntityPropertyNames = $this->contextEntityPropertyNames;
		while (null !== ($cepn = array_shift($contextEntityPropertyNames))) {
			$entityPropertyCollection = $entityPropertyCollection->getEntityPropertyByName($cepn)
					->getEmbeddedEntityPropertyCollection();
			$class = $entityPropertyCollection->getClass();
		}
		
		$accessProxy = null;
		if (null !== $this->objectPropertyName) {
			try{
				$propertiesAnalyzer = new PropertiesAnalyzer($class, false);
				$accessProxy = $propertiesAnalyzer->analyzeProperty($this->objectPropertyName, false, true);
				$accessProxy->setNullReturnAllowed(true);
			} catch (ReflectionException $e) {
				$this->handleException(new InvalidEiComponentConfigurationException('EiProp is assigned to unknown property: '
						. $this->objectPropertyName, 0, $e), $eiErrorResult);
			}
		}
			
		$entityProperty = null;
		if (null !== $this->entityPropertyName) {
			try {
				$entityProperty = $entityPropertyCollection->getEntityPropertyByName($this->entityPropertyName, true);
			} catch (UnknownEntityPropertyException $e) {
				$this->handleException(new InvalidEiComponentConfigurationException('EiProp is assigned to unknown EntityProperty: '
						. $this->entityPropertyName, 0, $e), $eiErrorResult);
			}
		}

// 		if ($entityProperty !== null || $accessProxy !== null) {
			try {
				$this->eiPropConfigurator->assignProperty(new PropertyAssignation($entityProperty, $accessProxy));
			} catch (IncompatiblePropertyException $e) {
				$this->handleException($e, $eiErrorResult);
			}
// 		}
	}
	
	private function handleException($e, EiErrorResult $eiErrorResult = null) {
		$e = $this->createException($e);
		if (null !== $eiErrorResult) {
			$eiErrorResult->putEiPropError(EiPropError::fromEiProp($this->eiPropConfigurator->getEiComponent(), $e));
			return;
		}
		
		throw $e;
	}
	
	/**
	 * @param \Throwable $e
	 * @return \rocket\ei\component\InvalidEiComponentConfigurationException
	 */
	private function createException($e) {
		$eiComponent = $this->eiPropConfigurator->getEiComponent();
		
		return new InvalidEiComponentConfigurationException('EiProp is invalid configured: ' . $eiComponent . ' in ' 
				. $eiComponent->getWrapper()->getEiPropCollection()->getEiMask(), 0, $e);
	}
}

class EiCommandSetupTask {
	private $eiCommand;
	private $eiConfigurator;
	
	public function __construct(EiCommand $eiCommand, EiConfigurator $eiConfigurator) {
		$this->eiCommand = $eiCommand;
		$this->eiConfigurator = $eiConfigurator;
	}
	
	/**
	 * @return \rocket\ei\component\command\EiCommand
	 */
	public function getEiCommand() {
		return $this->eiCommand;
	}
	
	/**
	 * @return \rocket\ei\component\EiConfigurator
	 */
	public function getEiConfigurator() {
		return $this->eiConfigurator;
	}
}

class EiModificatorSetupTask {
	private $eiModificator;
	private $eiConfigurator;
	
	public function __construct(EiModificator $eiModificator, EiConfigurator $eiConfigurator) {
		$this->eiModificator = $eiModificator;
		$this->eiConfigurator = $eiConfigurator;
	}
	
	/**
	 * @return \rocket\ei\component\modificator\EiModificator
	 */
	public function getEiModificator() {
		return $this->eiModificator;
	}
	
	/**
	 * @return \rocket\ei\component\EiConfigurator
	 */
	public function getEiConfigurator() {
		return $this->eiConfigurator;
	}
}

class EiPropSetupTask {
	private $eiProp;
	private $eiConfigurator;
	
	public function __construct(EiProp $eiProp, EiConfigurator $eiConfigurator) {
		$this->eiProp = $eiProp;
		$this->eiConfigurator = $eiConfigurator;
	}
	
	/**
	 * @return \rocket\ei\component\prop\EiProp
	 */
	public function getEiProp() {
		return $this->eiProp;
	}
	
	/**
	 * @return \rocket\ei\component\EiConfigurator
	 */
	public function getEiConfigurator() {
		return $this->eiConfigurator;
	}
}

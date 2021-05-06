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
namespace rocket\spec\extr;

use n2n\config\InvalidConfigurationException;
use rocket\spec\UnknownTypeException;
use rocket\spec\source\ModularConfigSource;
use n2n\util\ex\IllegalStateException;
use rocket\spec\InvalidEiMaskConfigurationException;
use rocket\core\model\launch\UnknownLaunchPadException;
use rocket\spec\TypePath;
use rocket\ei\component\UnknownEiComponentException;

/**
 * <p>This manager allows you to read und write spec configurations usually located in 
 * 	<code>[n2n-root]/var/etc/[module]/rocket/specs.json</code>. It is used by 
 * 	the {@see \rocket\spec\Spec} to load the current configuration.</p>
 * 
 * <p>It is also used by the dev tool Hangar {@link https://dev.n2n.rocks/en/hangar/docs} 
 * 	to manipulate spec configurations.</p>
 */
class SpecExtractionManager {
	private $init = false;
	private $modularConfigSource;
	private $moduleNamespaces;
	
	/**
	 * @var SpecConfigSourceDecorator[]
	 */
	private $specCsDecs = array();
	
	private $customTypeExtractions = array();
	private $eiTypeExtractions = array();
	private $eiTypeExtractionCis = array();
	private $eiTypeExtensionExtractionGroups = array();
	private $eiModificatorExtractionGroups = array();
	private $launchPadExtractions = array();
	
	/**
	 * @param ModularConfigSource $moduleConfigSource
	 * @param string[] $moduleNamespaces Namespaces of all modules which spec configurations shall be loaded. 
	 */
	public function __construct(ModularConfigSource $moduleConfigSource, array $moduleNamespaces) {
		$this->modularConfigSource = $moduleConfigSource;
		$this->moduleNamespaces = $moduleNamespaces;
	}
	
	/**
	 * @return \rocket\spec\source\ModularConfigSource
	 */
	public function getModularConfigSource() {
		return $this->modularConfigSource;
	}
	
	/**
	 * Searches all available module configurations. Nothing will be extracted but {@see self::hashCode()} gives the 
	 * right result.
	 */
	public function load() {
		$this->specCsDecs = array();
		
		foreach ($this->moduleNamespaces as $moduleNamespace) {
			$moduleNamespace = (string) $moduleNamespace;
				
			if (!$this->modularConfigSource->containsModuleNamespace($moduleNamespace)) {
				$this->specCsDecs[$moduleNamespace] = null;
				continue;
			}
				
			$this->specCsDecs[$moduleNamespace] = new SpecConfigSourceDecorator(
					$this->modularConfigSource->getOrCreateConfigSourceByModuleNamespace($moduleNamespace), 
					$moduleNamespace);
		}
	}
	
	/**
	 * 
	 */
	public function extract() {
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			$specCsDec->extract();
		}
		
		$this->dingselTypes();
		$this->dingselEiTypeExtensions();
		$this->dingselEiModificatorExtractions();
		$this->dingselLaunchPadExtractions();
		
		$this->init = true;
	}
	
	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->init;
	}
	
	private function dingselTypes() {
		$this->customTypeExtractions = array();
		$this->eiTypeExtractions = array();
		$this->eiTypeExtractionCis = array();
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
				
			foreach ($specCsDec->getCustomTypeExtractions() as $typeId => $customType) {
				if (isset($this->customTypeExtractions[$typeId]) || isset($this->eiTypeExtractions[$typeId])) {
					throw $this->createDuplicatedSpecIdException($typeId);
				}
				
				$this->customTypeExtractions[$typeId] = $customType;
			}
			
			foreach ($specCsDec->getEiTypeExtractions() as $typeId => $eiType) {
				if (isset($this->customTypeExtractions[$typeId]) || isset($this->eiTypeExtractions[$typeId])) {
					throw $this->createDuplicatedSpecIdException($typeId);
				}
				$this->eiTypeExtractions[$typeId] = $eiType;
				
				$entityClassName = $eiType->getEntityClassName();
				if (isset($this->eiTypeExtractionCis[$entityClassName])) {
					throw $this->createDuplicatedEntityClassNameException($entityClassName);
				}
				$this->eiTypeExtractionCis[$entityClassName] = $eiType;
			}
		}
	}
	
	private function dingselEiTypeExtensions() {
		$this->eiTypeExtensionExtractionGroups = array();
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			foreach ($specCsDec->getEiTypeExtensionExtractionGroups() 
					as $extendedEiTypePathStr => $eiTypeExtensionExtractions) {
				if (!isset($this->eiTypeExtensionExtractionGroups[$extendedEiTypePathStr])) {
					$this->eiTypeExtensionExtractionGroups[$extendedEiTypePathStr] = array();
				}
				
				foreach ($eiTypeExtensionExtractions as $eiTypeExtensionExtraction) {
					$id = $eiTypeExtensionExtraction->getId();
					
					if (isset($this->eiTypeExtensionExtractionGroups[$extendedEiTypePathStr][$id])) {
						throw new $this->createDuplicatedEiMaskIdException($extendedTypePath, $id);
					}
						
					if (isset($this->customTypeExtractions[$eiTypeExtensionExtraction->getExtendedEiTypePath()->getTypeId()])) {
						throw new InvalidConfigurationException('Invalid configuration in: ' . $specCsDec->getDataSource(), 0, 
								new InvalidEiMaskConfigurationException('EiMask with id \'' . $eiMaskId 
										. '\' was configured not for CustomType \'' . $eiTypeId . '\.'));
					}
						
					$this->eiTypeExtensionExtractionGroups[$extendedEiTypePathStr][$id] = $eiTypeExtensionExtraction;
				}
			}
		}
	}
	
	private function dingselEiModificatorExtractions() {
		$this->eiModificatorExtractionGroups = array();
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			foreach ($specCsDec->getEiModificatorExtractionGroups() as $typePathStr => $eiModificatorExtractions) {
				if (!isset($this->eiModificatorExtractionGroups[$typePathStr])) {
					$this->eiModificatorExtractionGroups[$typePathStr] = array();
				}
				
				foreach ($eiModificatorExtractions as $eiModificatorExtraction) {
					$id = $eiModificatorExtraction->getId();
					$typePath = $eiModificatorExtraction->getTypePath();
					$typePathStr = (string) $typePath;
					
					if (isset($this->eiModificatorExtractionGroups[$typePathStr][$id])) {
						throw new $this->createDuplicatedEiModificatorIdException($typePathStr, $id);
					}
										
					if (isset($this->customTypeExtractions[$typePath->getTypeId()])) {
						throw new InvalidConfigurationException('Invalid configuration in: ' . $specCsDec->getDataSource(), 0, 
								new InvalidEiMaskConfigurationException('EiModificator with id \'' . $eiModificatorId 
										. '\' was configured not for CustomType \'' . $typePath->getTypeId() . '\.'));
					}
						
					$this->eiModificatorExtractionGroups[$typePathStr][$id] = $eiModificatorExtraction;
				}
			}
		}
	}
		
	private function createDuplicatedSpecIdException($specId) {
		$configSources = array();
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			if ($specCsDec->containsSpecId($specId)) {
				$configSources[] = $specCsDec->getConfigSource();
			}
		}
		
		throw new InvalidConfigurationException('Spec with id \'' . $specId 
				. '\' is defined in multiple data sources: ' . implode(', ', $configSources));
	}
	
	private function createDuplicatedEntityClassNameException($entityClassName) {
		$configSources = array();
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			if ($specCsDec->containsEntityClassName($entityClassName)) {
				$configSources[] = $specCsDec->getConfigSource();
			}
		}
		
		return new InvalidConfigurationException('EiType for entity class \'' . $entityClassName 
				. '\' is defined in multiple times in: ' . implode(', ', $configSources));
	}
	
	private function createDuplicatedEiMaskIdException(string $eiTypeId, string $eiMaskId): InvalidConfigurationException {
		$dataSources = array();
		foreach ($this->specCsDecs as $specConfig) {
			if ($specConfig === null) continue;
			
			if ($specConfig->containsEiMaskId($eiTypeId, $eiMaskId)) {
				$dataSources[] = $specConfig->getDataSource();
			}
		}
		
		return new InvalidConfigurationException('EiMask with id \'' . $eiMaskId 
				. '\' for EiType \'' . $eiTypeId . '\' is defined in multiple data sources: ' . implode(', ', $dataSources));
	}
	
	private function createDuplicatedEiModificatorIdException(string $eiTypeId, string $eiModificatorId): InvalidConfigurationException {
		$dataSources = array();
		foreach ($this->specCsDecs as $specConfig) {
			if ($specConfig === null) continue;
			
			if ($specConfig->containsEiModificatorId($eiTypeId, $eiModificatorId)) {
				$dataSources[] = $specConfig->getDataSource();
			}
		}
		
		return new InvalidConfigurationException('EiModificator with id \'' . $eiModificatorId 
				. '\' for EiType \'' . $eiTypeId . '\' is defined in multiple data sources: ' . implode(', ', $dataSources));
	}
	
// 	private function createDuplicatedLaunchPadIdException($launchPadId): InvalidConfigurationException {
// 		$dataSources = array();
// 		foreach ($this->specCsDecs as $specConfig) {
// 			if ($specConfig->containsLaunchPadId($launchPadId)) {
// 				$dataSources[] = $specConfig->getDataSource();
// 			}
// 		}
	
// 		throw new InvalidConfigurationException('LaunchPad with id \'' . $launchPadId
// 				. '\' is defined in multiple data sources: ' . implode(', ', $dataSources));
// 	}
	
	/**
	 * @return string[]
	 */
	public function getTypeIds(): array {
		return array_merge(array_keys($this->customTypeExtractions), array_keys($this->eiTypeExtractions));
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsTypeId(string $id) {
		return isset($this->customTypeExtractions[$id]) || isset($this->eiTypeExtractions[$id]);
	}
	
	private function valUnique($id) {
		if (!$this->containsTypeId($id)) return;
		
		throw new IllegalStateException('Duplicated type id: ' . $id);
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsCustomTypeExtractionId(string $id) {
		return isset($this->customTypeExtractions[$id]);
	}
	
	/**
	 * @return CustomTypeExtraction[]
	 */
	public function getCustomTypeExtractions() {
		return $this->customTypeExtractions;
	}
	
	/**
	 * @param string $id
	 * @throws UnknownTypeException
	 * @return CustomTypeExtraction
	 */
	public function getCustomTypeExtractionById(string $id) {
		if (isset($this->customTypeExtractions[$id])) {
			return $this->customTypeExtractions[$id];
		}
		
		throw new UnknownTypeException('No CustomType with id \'' . $id . '\' defined in: '
				. $this->buildConfigSourceString());
	}
	
	/**
	 * @param CustomTypeExtraction $customTypeExtraction
	 */
	public function addCustomTypeExtraction(CustomTypeExtraction $customTypeExtraction) {
		$id = $customTypeExtraction->getId();
		$this->valUnique($id);
		
		$this->customTypeExtractions[$id] = $customTypeExtraction;
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsEiTypeId(string $id) {
		return isset($this->eiTypeExtractions[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws UnknownTypeException
	 * @return EiTypeExtraction
	 */
	public function getEiTypeExtractionById(string $id) {
		if (isset($this->eiTypeExtractions[$id])) {
			return $this->eiTypeExtractions[$id];
		}
		
		throw new UnknownTypeException('No EiType with id \'' . $id . '\' defined in: '
				. $this->buildConfigSourceString());
	}
	
	/**
	 * @param string $className
	 * @return bool
	 */
	public function containsEiTypeEntityClassName(string $className) {
		return isset($this->eiTypeExtractionCis[$className]);
	}
	
	/**
	 * @param string $className
	 * @throws UnknownTypeException
	 * @return EiTypeExtraction
	 */
	public function getEiTypeExtractionByClassName(string $className) {
		if (isset($this->eiTypeExtractionCis[$className])) {
			return $this->eiTypeExtractionCis[$className];
		}
		
		throw new UnknownTypeException('No EiType for Entity \'' . $className . '\' defined in: ' 
				. $this->buildConfigSourceString());
	}
	
	function getCascadedEiTypeExtraction(string $srcClassName, string $type) {
		return [];
	}
	
	/**
	 * @return EiTypeExtraction[]
	 */
	public function getEiTypeExtractions() {
		return $this->eiTypeExtractions;
	}
	
	public function addEiTypeExtraction(EiTypeExtraction $eiTypeExtraction) {
		$id = $eiTypeExtraction->getId();
		$this->valUnique($id);
		
		$entityClassName = $eiTypeExtraction->getEntityClassName();
		if (isset($this->eiTypeExtractionCis[$entityClassName])) {
			throw new IllegalStateException('EiType for Entity already defined: ' . $entityClassName);
		}
		
		$this->eiTypeExtractions[$id] = $eiTypeExtraction;
		$this->eiTypeExtractionCis[$entityClassName] = $eiTypeExtraction;
		
		
	}
	
	/**
	 * @param string $id
	 */
	public function removeTypeById(string $id) {
		unset($this->customTypeExtractions[$id]);
		
		if (isset($this->eiTypeExtractions[$id])) {
			unset($this->eiTypeExtractionCis[$this->eiTypeExtractions[$id]->getEntityClassName()]);
			unset($this->eiTypeExtractions[$id]);
		}
	}
	
// 	/**
// 	 * @return array
// 	 */
// 	public function getEiTypeExtensionExtractionGroups() {
// 		return $this->eiTypeExtensionExtractionGroups;
// 	}
	
// 	/**
// 	 * @return array
// 	 */
// 	public function getEiModificatorExtractionGroups() {
// 		return $this->eiModificatorExtractions;
// 	}
	
	/**
	 * @param string $extendedEiTypePath
	 * @param string $id
	 * @return bool
	 */
	public function containsEiTypeExtensionExtractionId(TypePath $extendedEiTypePath, string $id) {
		return isset($this->eiTypeExtensionExtractionGroups[(string) $extendedEiTypePath][$id]);
	}
	
	/**
	 * @param TypePath $eiTypePath
	 * @return EiTypeExtensionExtraction|null
	 */
	private function findEiTypeExtensionExtractionByEiTypePath(TypePath $eiTypePath) {
		$eiTypeId = $eiTypePath->getTypeId();
		$eiTypeExtensionId = $eiTypePath->getEiTypeExtensionId();
		
		foreach ($this->eiTypeExtensionExtractionGroups as $iTypePathStr => $eiTypeExtensionExtractions) {
			$iTypePath = TypePath::create($iTypePathStr);
			
			if ($iTypePath->getTypeId() !== $eiTypeId) continue;
			
			if (isset($eiTypeExtensionExtractions[$eiTypeExtensionId])) {
				return $eiTypeExtensionExtractions[$eiTypeExtensionId];
			}
		}
		
		return null;
	}
	
	/**
	 * @param TypePath $eiTypePath
	 * @return boolean
	 */
	public function containsEiTypeExentionsExtractionEiTypePath(TypePath $eiTypePath) {
		return null !== $this->findEiTypeExtensionExtractionByEiTypePath($eiTypePath);
	}
	
	/**
	 * @param string $extendedEiTypePath
	 * @param string $id
	 * @return EiTypeExtensionExtraction
	 */
	public function getEiTypeExtensionExtractionByEiTypePath(TypePath $eiTypePath) {
		if (null !== ($extr = $this->findEiTypeExtensionExtractionByEiTypePath($eiTypePath))) {
			return $extr;
		}
		
		throw new UnknownTypeException('No EiTypeExtension with TypePath \'' . $eiTypePath . '\' defined in: ' 
				. $this->buildConfigSourceString());
	}
	
	/**
	 * @param string $eiTypeId
	 * @return EiTypeExtensionExtraction[]
	 */
	public function getEiTypeExtensionExtractionsByExtendedEiTypePath(TypePath $extendedEiTypePath) {
		$extendedEiTypePathStr = (string) $extendedEiTypePath;
		if (isset($this->eiTypeExtensionExtractionGroups[$extendedEiTypePathStr])) {
			return $this->eiTypeExtensionExtractionGroups[$extendedEiTypePathStr];
		}
		return array();
	}
	
	/**
	 * @param EiTypeExtensionExtraction $eiTypeExtensionExtraction
	 * @throws IllegalStateException
	 */
	public function addEiTypeExtensionExtraction(EiTypeExtensionExtraction $eiTypeExtensionExtraction) {
		$extendedEiTypePath = $eiTypeExtensionExtraction->getExtendedEiTypePath();
		$id = $eiTypeExtensionExtraction->getId();
		
		if ($this->containsEiTypeExtensionExtractionId($extendedEiTypePath, $id)) {
			throw new IllegalStateException('EiTypeExtensionExtraction with id \'' . $id 
					. '\' already defined for EiType \'' . $extendedEiTypePath . '\'.');
		}
		
		$this->eiTypeExtensionExtractionGroups[(string) $extendedEiTypePath][$id] = $eiTypeExtensionExtraction;
	}
	
	/**
	 * @param TypePath $extendedEiTypePath
	 * @param string $id
	 */
	public function removeEiTypeExtensionExtractionByEiTypePath(TypePath $eiTypePath) {
		$eiTypeExtensionExtraction = $this->findEiTypeExtensionExtractionByEiTypePath($eiTypePath);
		if (null === $eiTypeExtensionExtraction) return;
		
		$extendedEiTypePath = $eiTypeExtensionExtraction->getExtendedEiTypePath();
		$id = $eiTypeExtensionExtraction->getId();
		
		unset($this->eiTypeExtensionExtractionGroups[(string) $extendedEiTypePath][$id]);
	}
		
	/**
	 * @param TypePath $eiTypePath
	 * @param string $id
	 * @return bool
	 */
	public function containsEiModificatorExtractionId(TypePath $eiTypePath, string $id) {
		return isset($this->eiModificatorExtractionGroups[(string) $eiTypePath][$id]);
	}
	
	/**
	 * @param TypePath $eiTypePath
	 * @return EiModificatorExtraction[]
	 */
	public function getEiModificatorExtractionsByEiTypePath(TypePath $eiTypePath) {
		$typePathStr = (string) $eiTypePath;
		if (isset($this->eiModificatorExtractionGroups[$typePathStr])) {
			return $this->eiModificatorExtractionGroups[$typePathStr];
		}
		
		return array();
	}
	
	/**
	 * @param TypePath $eiTypePath
	 * @return EiModificatorExtraction[]
	 */
	public function getEiModificatorExtractionById(TypePath $eiTypePath, string $id) {
		$typePathStr = (string) $eiTypePath;
		if (isset($this->eiModificatorExtractionGroups[$typePathStr][$id])) {
			return $this->eiModificatorExtractionGroups[$typePathStr][$id];
		}
	
		throw new UnknownEiComponentException('No EiTypeExtension with id \'' . $id . '\' defined for EiType \'' . $eiTypePath . '\' defined in: '
				. $this->buildConfigSourceString());
	}
	
	/**
	 * @param EiModificatorExtraction $eiModificatorExtraction
	 * @throws IllegalStateException
	 */
	public function addEiModificatorExtraction(EiModificatorExtraction $eiModificatorExtraction) {
		$eiTypePath = $eiModificatorExtraction->getTypePath();
		$id = $eiModificatorExtraction->getId();
		
		if ($this->containsEiModificatorExtractionId($eiTypePath, $id)) {
			throw new IllegalStateException('EiModificator with id \'' . $id . '\' already defined for EiType \''
					. $eiTypePath . '\'.');
		}
		
		$this->eiModificatorExtractionGroups[(string) $eiTypePath][$id] = $eiModificatorExtraction;
	}
	
	/**
	 * @param TypePath $eiTypePath
	 * @param string $id
	 */
	public function removeEiModificatorExtractionById(TypePath $eiTypePath, string $id) {
		unset($this->eiModificatorExtractionGroups[(string) $eiTypePath][$id]);
	}
	
	/**
	 * @param TypePath $eiTypePath
	 */
	public function removeEiModificatorExtractionsByEiTypePath(TypePath $eiTypePath) {
		$this->eiModificatorExtractionGroups[(string) $eiTypePath] = array();
	}
	
	/**
	 * @param TypePath $typePath
	 * @return LaunchPadExtraction[]
	 */
	public function getLaunchPadExtractionByEiTypePath(TypePath $typePath) {
		$typePathStr = (string) $typePath;
		if (isset($this->launchPadExtractions[$typePathStr])) {
			return $this->launchPadExtractions[$typePathStr];
		}
		return null;
	}
	
	/**
	 * @return string
	 */
	private function buildConfigSourceString() {
		$configSourceStrs = array();
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			$configSourceStrs[] = (string) $specCsDec->getConfigSource();
		}
		
		return 'config source bundle (' . implode(', ', $configSourceStrs) . ')';
	}
	
	/**
	 * @param TypePath $typePath
	 * @return bool
	 */
	public function containsLaunchPadExtractionTypePath(TypePath $typePath) {
		return isset($this->launchPadExtractions[(string) $typePath]);
	}
	
	/**
	 * @return LaunchPadExtraction[]
	 */
	public function getLaunchPadExtractions() {
		return $this->launchPadExtractions;
	}
	
	/**
	 * @param TypePath $typePath
	 * @throws UnknownLaunchPadException
	 * @return LaunchPadExtraction
	 */
	public function getLaunchPadExtractionByTypePath(TypePath $typePath) {
		$typePathStr = (string) $typePath;
		if (isset($this->launchPadExtractions[$typePathStr])) {
			return $this->launchPadExtractions[$typePathStr];
		}
		
		throw new UnknownLaunchPadException('No LaunchPad with id \'' . $typePathStr . '\' defined in: '
				. $this->buildConfigSourceString(), null, null, 2);
	}
	
	/**
	 * @param LaunchPadExtraction $launchPadExtraction
	 * @throws IllegalStateException
	 */
	public function addLaunchPad(LaunchPadExtraction $launchPadExtraction) {
		$typePath = $launchPadExtraction->getTypePath();
		if ($this->containsLaunchPadExtractionTypePath($typePath)) {
			throw new IllegalStateException('LaunchPadExtraction for Type \'' . $typePath . '\' already defined.');
		}
		
		$this->launchPadExtractions[(string) $launchPadExtraction->getTypePath()] = $launchPadExtraction;
	}
	
	/**
	 * @param TypePath $typePath
	 */
	public function removeLaunchPadByTypePath(TypePath $typePath) {
		unset($this->launchPadExtractions[(string) $typePath]);
	}
	
	/**
	 * @param string $moduleNamespace
	 * @throws IllegalStateException
	 * @return \rocket\spec\extr\SpecConfigSourceDecorator
	 */
	private function getSpecCsDescByModuleNamespace(string $moduleNamespace): SpecConfigSourceDecorator {
		if (isset($this->specCsDecs[$moduleNamespace])) {
			return $this->specCsDecs[$moduleNamespace];
		}
	
		if (array_key_exists($moduleNamespace, $this->specCsDecs)) {
			return $this->specCsDecs[$moduleNamespace] = new SpecConfigSourceDecorator(
					$this->modularConfigSource->getOrCreateConfigSourceByModuleNamespace($moduleNamespace), $moduleNamespace);
		}
	
		throw new IllegalStateException('Unknown module namespace: ' . $moduleNamespace);
	}
	
	public function flush() {
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			$specCsDec->clear();
		}
		
		foreach ($this->customTypeExtractions as $typeId => $customExtraction) {
			$this->getSpecCsDescByModuleNamespace($customExtraction->getModuleNamespace())
					->addCustomTypeExtraction($customExtraction);
		}
		
		foreach ($this->eiTypeExtractions as $typeId => $eiTypeExtraction) {
			$this->getSpecCsDescByModuleNamespace($eiTypeExtraction->getModuleNamespace())
					->addEiTypeExtraction($eiTypeExtraction);
		}
		
		foreach ($this->eiTypeExtensionExtractionGroups as $eiTypeId => $eiTypeExtensionExtractions) {
			foreach ($eiTypeExtensionExtractions as $eiTypeExtensionExtraction) {
				$this->getSpecCsDescByModuleNamespace($eiTypeExtensionExtraction->getModuleNamespace())
						->addEiTypeExtensionExtraction($eiTypeId, $eiTypeExtensionExtraction);
			}
		}
		
		foreach ($this->eiModificatorExtractionGroups as $eiTypeId => $unboundEiModificatorExtractions) {
			foreach ($unboundEiModificatorExtractions as $unboundEiModificatorExtractions) {
				$this->getSpecCsDescByModuleNamespace($unboundEiModificatorExtractions->getModuleNamespace())
						->addEiModificatorExtraction($eiTypeId, $unboundEiModificatorExtractions);
			}
		}
		
		foreach ($this->launchPadExtractions as $launchPadId => $launchPadExtraction) {
			$this->getSpecCsDescByModuleNamespace($launchPadExtraction->getModuleNamespace())
					->addLaunchPadExtraction($launchPadExtraction);
		}
		
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			$specCsDec->flush();
		}
	}
	
	/**
	 * 
	 */
	private function dingselLaunchPadExtractions() {
		foreach ($this->specCsDecs as $specCsDec) {
			if ($specCsDec === null) continue;
			
			foreach ($specCsDec->getLaunchPadExtractions() as $launchPadExtraction) {
				$typePathStr = (string) $launchPadExtraction->getTypePath();
				
				if (isset($this->launchPadExtractions[$typePathStr])) {
					throw $this->createDuplicatedLaunchPadIdException($typePathStr);
				}
				
				$this->launchPadExtractions[$typePathStr] = $launchPadExtraction;
			}
		}
	}
}

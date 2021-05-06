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

use n2n\reflection\ReflectionUtils;
use n2n\core\TypeNotFoundException;
use rocket\spec\source\ModularConfigSource;
use rocket\ei\component\prop\indepenent\IndependentEiProp;
use rocket\ei\component\prop\EiProp;
use rocket\ei\component\command\EiCommand;
use rocket\ei\component\command\IndependentEiCommand;
use rocket\ei\component\modificator\EiModificator;
use rocket\ei\component\modificator\IndependentEiModificator;

class EiComponentStore {
	const EI_FIELD_CLASSES_KEY = 'eiPropClasses';
	const EI_COMMAND_CLASSES_KEY = 'eiCommandClasses';
	const EI_COMMAND_GROUPS_KEY = 'eiCommandGroups';
	const EI_MODIFICATOR_CLASSES_KEY = 'eiModificatorClasses';
	
	private $eiComponentConfigSource;
	private $eiPropClasses = array();
	private $eiPropClassesByModule = array();
	private $eiCommandClasses = array();
	private $eiCommandClassesByModule = array();
	private $eiCommandGroups = array();
	private $eiCommandGroupsByModule = array();
	private $eiModificatorClasses = array();
	private $eiModificatorClassesByModule = array();
	
	public function __construct(ModularConfigSource $eiComponentConfigSource, array $moduleNamespaces) {
		$this->eiComponentConfigSource = $eiComponentConfigSource;
		
		foreach ($moduleNamespaces as $moduleNamespace) {
			if ($this->eiComponentConfigSource->containsModuleNamespace($moduleNamespace)) {
				$this->analyzeModuleRawData($moduleNamespace, $this->eiComponentConfigSource
						->getOrCreateConfigSourceByModuleNamespace($moduleNamespace)->readArray());
			}
		}
	}
	
	private function extractElementArray($elementKey, array $rawData) {
		if (isset($rawData[$elementKey]) && is_array($rawData[$elementKey])) {
			return $rawData[$elementKey];
		}
		
		return array();
	}
	
	private function analyzeModuleRawData(string $moduleNamespace, array $moduleRawData) {		
		// EiProps
		$this->eiPropClassesByModule[$moduleNamespace] = array();
		foreach ($this->extractElementArray(self::EI_FIELD_CLASSES_KEY, $moduleRawData) 
				as $key => $eiPropClassName) {
			try {
				$fieldClass = ReflectionUtils::createReflectionClass($eiPropClassName);
				if (!$fieldClass->implementsInterface(EiProp::class)
						|| !$fieldClass->implementsInterface(IndependentEiProp::class)) continue;
				
				$this->eiPropClasses[$eiPropClassName] = $fieldClass;
				$this->eiPropClassesByModule[$moduleNamespace][$eiPropClassName] = $fieldClass;
			} catch (TypeNotFoundException $e) { }
		}
		
		// EiCommands
		$this->eiCommandClassesByModule[$moduleNamespace] = array();
		foreach ($this->extractElementArray(self::EI_COMMAND_CLASSES_KEY, $moduleRawData) 
				as $key => $eiCommandClassName) {
			try {
				$eiCommandClass =  ReflectionUtils::createReflectionClass($eiCommandClassName);
				if (!$eiCommandClass->implementsInterface(EiCommand::class)
						|| !$eiCommandClass->implementsInterface(IndependentEiCommand::class)) continue;
				
				$this->eiCommandClasses[$eiCommandClassName] = $eiCommandClass;
				$this->eiCommandClassesByModule[$moduleNamespace][$eiCommandClassName] = $eiCommandClass;
			} catch (TypeNotFoundException $e) { }
		}
				
		// EiCommandGroups
		$this->eiCommandGroupsByModule[$moduleNamespace] = array();
		foreach ($this->extractElementArray(self::EI_COMMAND_GROUPS_KEY, $moduleRawData) 
				as $groupName => $eiCommandClassNames) {
			if (!is_array($eiCommandClassNames)) {
				continue;
			}
		
			$eiCommandGroup = new EiCommandGroup($groupName);
			foreach ($eiCommandClassNames as $key => $eiCommandClassName) {
				if (!isset($this->eiCommandClasses[$eiCommandClassName])) continue;
				$eiCommandGroup->addEiCommandClass($this->eiCommandClasses[$eiCommandClassName]); 
			}
		
			$this->eiCommandGroups[$groupName] = $eiCommandGroup;
			$this->eiCommandGroupsByModule[$moduleNamespace][$groupName] = $eiCommandGroup;
		}
		
		// EiModificators
		$this->eiModificatorClassesByModule[$moduleNamespace] = array();
		foreach ($this->extractElementArray(self::EI_MODIFICATOR_CLASSES_KEY, $moduleRawData) 
				as $key => $eiModificatorClassName) {
			try {
				$constraintClass =  ReflectionUtils::createReflectionClass($eiModificatorClassName);
				if (!$constraintClass->implementsInterface(EiModificator::class)
						|| !$constraintClass->implementsInterface(IndependentEiModificator::class)) continue;
		
				$this->eiModificatorClasses[$eiModificatorClassName] = $constraintClass;
				$this->eiModificatorClassesByModule[$moduleNamespace][$eiModificatorClassName] = $constraintClass;
			} catch (TypeNotFoundException $e) { }
		}
	}
	
	/**
	 * @return \ReflectionClass[]
	 */
	public function getEiPropClasses(): array {
		return $this->eiPropClasses;
	}
	
	public function getEiPropClassesByModuleNamespace(string $moduleNamespace): array {
		if (isset($this->eiPropClassesByModule[$moduleNamespace])) {
			return $this->eiPropClassesByModule[$moduleNamespace];
		}
		
		return array();
	}
	
	public function removeEiPropClassesByModuleNamespace(string $moduleNamespace) {
		if (!isset($this->eiPropClassesByModule[$moduleNamespace])) return;
		foreach ($this->eiPropClassesByModule[$moduleNamespace] as $eiPropClass) {
			unset($this->eiPropClasses[$eiPropClass->getName()]);
		}
		$this->eiPropClassesByModule[$moduleNamespace] = array();
	}
	
	public function addEiPropClass($moduleNamespace, \ReflectionClass $eiPropClass) {
		if (!isset($this->eiPropClassesByModule[$moduleNamespace])) {
			$this->eiPropClassesByModule[$moduleNamespace] = array();
		}
		$className = $eiPropClass->getName();
		$this->eiPropClasses[$className] = $eiPropClass;
		$this->eiPropClassesByModule[$moduleNamespace][$className] = $eiPropClass;
	}
	
	public function getEiCommandClasses() {
		return $this->eiCommandClasses;
	}
	
	public function getEiCommandClassesByModuleNamespace(string $moduleNamespace) {
		if (isset($this->eiCommandClassesByModule[$moduleNamespace])) {
			return $this->eiCommandClassesByModule[$moduleNamespace];
		}
		
		return array();
	}
	
	public function removeEiCommandClassesByModuleNamespace(string $moduleNamespace) {
		if (!isset($this->eiCommandClassesByModule[$moduleNamespace])) return;
		foreach ($this->eiCommandClassesByModule[$moduleNamespace] as $eiCommandClass) {
			unset($this->eiCommandClasses[$eiCommandClass->getName()]);
		}
		$this->eiCommandClassesByModule[$moduleNamespace] = array();
	}
	
	public function addEiCommandClass($moduleNamespace, \ReflectionClass $eiCommandClass) {
		if (!isset($this->eiCommandClassesByModule[$moduleNamespace])) {
			$this->eiCommandClassesByModule[$moduleNamespace] = array();
		}
		$className = $eiCommandClass->getName();
		$this->eiCommandClasses[$className] = $eiCommandClass;
		$this->eiCommandClassesByModule[$moduleNamespace][$className] = $eiCommandClass;
	}
	
	/**
	 * @return EiCommandGroup
	 */
	public function getEiCommandGroups() {
		return $this->eiCommandGroups;
	}
	
	public function getEiCommandGroupsByModuleNamespace(string $moduleNamespace) {
		if (isset($this->eiCommandGroupsByModule[$moduleNamespace])) {
			return $this->eiCommandGroupsByModule[$moduleNamespace];
		}
		
		return array();
	}
	
	public function removeEiCommandGroupsByModuleNamespace(string $moduleNamespace) {
		if (!isset($this->eiCommandGroupsByModule[$moduleNamespace])) return;
		foreach ($this->eiCommandGroupsByModule[$moduleNamespace] as $eiCommandGroup) {
			unset($this->eiCommandGroups[$eiCommandGroup->getName()]);
		}
		$this->eiCommandGroupsByModule[$moduleNamespace] = array();
	}
	
	public function addEiCommandGroup($moduleNamespace, EiCommandGroup $eiCommandGroup) {
		if (!isset($this->eiCommandGroupsByModule[$moduleNamespace])) {
			$this->eiCommandGroupsByModule[$moduleNamespace] = array();
		}
		
		$className = $eiCommandGroup->getName();
		$this->eiCommandGroups[$className] = $eiCommandGroup;
		$this->eiCommandGroupsByModule[$moduleNamespace][$className] = $eiCommandGroup;
	}
	
	public function getEiModificatorClasses() {
		return $this->eiModificatorClasses;
	}
	
	public function getEiModificatorClassesByModuleNamespace(string $moduleNamespace) {
		if (isset($this->eiModificatorClassesByModule[$moduleNamespace])) {
			return $this->eiModificatorClassesByModule[$moduleNamespace];
		}
		
		return array();
	}
	
	public function removeEiModificatorClassesByModuleNamespace(string $moduleNamespace) {
		if (!isset($this->eiModificatorClassesByModule[$moduleNamespace])) return;
		foreach ($this->eiModificatorClassesByModule[$moduleNamespace] as $eiModificatorClass) {
			unset($this->eiModificatorClasses[$eiModificatorClass->getName()]);
		}
		$this->eiModificatorClassesByModule[$moduleNamespace] = array();
	}
	
	public function addEiModificatorClass($moduleNamespace, \ReflectionClass $eiModificatorClass) {
		if (!isset($this->eiModificatorClassesByModule[$moduleNamespace])) {
			$this->eiModificatorClassesByModule[$moduleNamespace] = array();
		}
		$className = $eiModificatorClass->getName();
		$this->eiModificatorClasses[$className] = $eiModificatorClass;
		$this->eiModificatorClassesByModule[$moduleNamespace][$className] = $eiModificatorClass;
	}
	
	public function flush($module = null) {
		if ($module !== null) {
			$this->persistByModule((string) $module, $this->configSources[(string) $module]);
			return;
		}
		
		$moduleNamespaces = array_unique(array_merge(
				array_keys($this->eiPropClasses), array_keys($this->eiPropClassesByModule), 
				array_keys($this->eiCommandClasses), array_keys($this->eiCommandClassesByModule), 
				array_keys($this->eiCommandGroups), array_keys($this->eiCommandGroupsByModule), 
				array_keys($this->eiModificatorClasses), array_keys($this->eiModificatorClassesByModule)));
		
		foreach ($moduleNamespaces as $moduleNamespace) {
			$this->persistByModule($moduleNamespace);
		}
	}
	
	private function persistByModule(string $moduleNamespace) {
		$write = false;
		$moduleRawData = array();
		
		if (isset($this->eiPropClassesByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::EI_FIELD_CLASSES_KEY] = array();
			foreach ($this->eiPropClassesByModule[$moduleNamespace] as $eiPropClass) {
				$moduleRawData[self::EI_FIELD_CLASSES_KEY][] = $eiPropClass->getName();
			} 
		}
		
		if (isset($this->eiCommandClassesByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::EI_COMMAND_CLASSES_KEY] = array();
			foreach ($this->eiCommandClassesByModule[$moduleNamespace] as $eiCommandClass) {
				$moduleRawData[self::EI_COMMAND_CLASSES_KEY][] = $eiCommandClass->getName();
			}
		}
		
		if (isset($this->eiCommandGroupsByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::EI_COMMAND_GROUPS_KEY] = array();
			foreach ($this->eiCommandGroupsByModule[$moduleNamespace] as $eiCommandGroup) {
				$groupName = $eiCommandGroup->getName();
				$moduleRawData[self::EI_COMMAND_GROUPS_KEY][$groupName] = array();
				foreach ($eiCommandGroup->getEiCommandClasses() as $eiCommandClass) {
					$moduleRawData[self::EI_COMMAND_GROUPS_KEY][$groupName][] = $eiCommandClass->getName();
				}
			}
		}
		
		if (isset($this->eiModificatorClassesByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::EI_MODIFICATOR_CLASSES_KEY] = array();
			foreach ($this->eiModificatorClassesByModule[$moduleNamespace] as $eiModificatorClass) {
				$moduleRawData[self::EI_MODIFICATOR_CLASSES_KEY][] = $eiModificatorClass->getName();
			} 
		}
		
		if (isset($this->listenerClassesByModule[$moduleNamespace])) {
			$write = true;
			$moduleRawData[self::SPEC_LISTENER_CLASSES_KEY] = array();
			foreach ($this->listenerClassesByModule[$moduleNamespace] as $listenerClass) {
				$moduleRawData[self::SPEC_LISTENER_CLASSES_KEY][] = $listenerClass->getName();
			} 
		}
		
		if ($write) {
			$this->eiComponentConfigSource->getConfigSourceByModule($moduleNamespace)->writeArray($moduleRawData);
		}
	}
}

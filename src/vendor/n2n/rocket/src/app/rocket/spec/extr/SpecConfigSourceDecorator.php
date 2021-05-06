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

use n2n\config\source\WritableConfigSource;
use n2n\util\type\attrs\DataSet;
use n2n\config\InvalidConfigurationException;
use n2n\util\type\attrs\AttributesException;
use rocket\spec\InvalidSpecConfigurationException;
use rocket\spec\InvalidEiMaskConfigurationException;
use n2n\util\type\ArgUtils;

/**
 * Decorates the ConfigSource of a spec configuration from a single module and provides simplified interface to read
 * from and write to this ConfigSource. This class is used by {@see SpecExtractionManager}.
 */
class SpecConfigSourceDecorator {
	private $configSource;
	private $moduleNamespace;
	
	private $dataSet;
	private $customTypeExtractions = array();
	private $eiTypeExtractions = array();
	private $eiTypeExtensionExtractionGroups = array();
	private $eiModificatorExtractionGroups = array();
	private $launchPadExtractions = array();
	
	/**
	 * @param WritableConfigSource $configSource
	 * @param string $moduleNamespace
	 */
	public function __construct(WritableConfigSource $configSource, string $moduleNamespace) {
		$this->dataSet = new DataSet();
		$this->configSource = $configSource;
		$this->moduleNamespace = $moduleNamespace;
	} 
	
	/**
	 * @return string
	 */
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	/**
	 * @return \n2n\config\source\WritableConfigSource
	 */
	public function getConfigSource() {
		return $this->configSource;
	}
	
	/**
	 * Reads the decorated ConfigSource and uses {@see SpecExtractor} to extract all
	 * {@see TypeExtraction}s, {@see EiTypeExtensionExtraction}s, {@see LaunchPadExtraction}s
	 * and overwrites the matching properties on this class. You can access these properties 
	 * through the getter methods.  
	 * @throws InvalidConfigurationException
	 */
	public function extract() {
		$this->dataSet = new DataSet($this->configSource->readArray());
		
		$specExtractor = new SpecExtractor($this->dataSet, $this->moduleNamespace);
		
		try {
			$result = $specExtractor->extractTypes();
			$this->customTypeExtractions = $result['customTypeExtractions'];
			$this->eiTypeExtractions = $result['eiTypeExtractions'];
			$this->eiTypeExtensionExtractionGroups = $specExtractor->extractEiTypeExtensionGroups();
			$this->eiModificatorExtractionGroups = $specExtractor->extractEiModificatorGroups();
			$this->launchPadExtractions = $specExtractor->extractLaunchPads();
		} catch (AttributesException $e) {
			throw $this->createDataSourceException($e);
		} catch (InvalidSpecConfigurationException $e) {
			throw $this->createDataSourceException($e);
		} catch (InvalidEiMaskConfigurationException $e) {
			throw $this->createDataSourceException($e);
		}
	}
	
	/**
	 * Uses {@see SpecRawer} to do the opposite of {@see self::extract()}.
	 */
	public function flush() {
		$specRawer = new SpecRawer($this->dataSet);
		$specRawer->rawTypes($this->eiTypeExtractions, $this->customTypeExtractions);
		$specRawer->rawEiMasks($this->eiTypeExtensionExtractionGroups);
		$specRawer->rawEiModificatorExtractionGroups($this->eiModificatorExtractionGroups);
		$specRawer->rawLaunchPads($this->launchPadExtractions);
		
		$this->configSource->writeArray($this->dataSet->toArray());
	}
	
	/**
	 * 
	 */
	public function clear() {
		$this->dataSet = new DataSet();
		
		$this->customTypeExtractions = array();
		$this->eiTypeExtractions = array();
		$this->eiTypeExtensionExtractionGroups = array();
		$this->eiModificatorExtractionGroups = array();
		$this->launchPadExtractions = array();
	}
	
	/**
	 * @return \rocket\spec\extr\CustomTypeExtraction[]
	 */
	public function getCustomTypeExtractions() {
		return $this->customTypeExtractions;
	}
	
	/**
	 * @param CustomTypeExtraction[] $customTypeExtractions
	 */
	public function setCustomTypeExtractions(array $customTypeExtractions) {
		ArgUtils::valArray($customTypeExtractions, CustomTypeExtraction::class);
		
		$this->customTypeExtractions = array();
		foreach ($customTypeExtractions as $customTypeExtraction) {
			$this->addCustomTypeExtraction($customTypeExtraction);
		}
	}
	
	/**
	 * @param CustomTypeExtraction $customTypeExtraction
	 */
	public function addCustomTypeExtraction(CustomTypeExtraction $customTypeExtraction) {
		$id = $customTypeExtraction->getId();
		$this->customTypeExtractions[$id] = $customTypeExtraction;
		unset($this->eiTypeExtractions[$id]);
	}
	
	/**
	 * @return \rocket\spec\extr\EiTypeExtraction[]
	 */
	public function getEiTypeExtractions() {
		return $this->eiTypeExtractions;
	}
	
	/**
	 * @param EiTypeExtraction[] $specExtractions
	 */
	public function setEiTypeExtractions(array $eiTypeExtractions) {
		ArgUtils::valArray($eiTypeExtractions, EiTypeExtraction::class);
		
		$this->eiTypeExtractions = array();
		foreach ($eiTypeExtractions as $eiTypeExtraction) {
			$this->addEiTypeExtraction($eiTypeExtraction);
		}
	}
	
	/**
	 * @param EiTypeExtraction $eiTypeExtraction
	 */
	public function addEiTypeExtraction(EiTypeExtraction $eiTypeExtraction) {
		$id = $eiTypeExtraction->getId();
		$this->eiTypeExtractions[$id] = $eiTypeExtraction;
		unset($this->customTypeExtractions[$id]);
	}
	
	/**
	 * @param \Exception $previous
	 * @throws InvalidConfigurationException
	 */
	private function createDataSourceException(\Exception $previous) {
		throw new InvalidConfigurationException('Configruation error in data source: ' . $this->configSource, 0, $previous);
	}
	
	public function getEiMaskEiTypeIds() {
		return array_keys($this->eiTypeExtensionExtractionGroups);
	}
	
	public function getEiTypeExtensionExtractionsByEiTypeId($eiTypeId) {
		if (isset($this->eiTypeExtensionExtractionGroups[$eiTypeId])) {
			return $this->eiTypeExtensionExtractionGroups[$eiTypeId];
		}

		return array();
	}
	
	public function setEiTypeExtensionExtractions($eiTypeId, array $eiTypeExtensionExtractions) {
		$this->eiTypeExtensionExtractionGroups[$eiTypeId] = $eiTypeExtensionExtractions;
	}
	
	public function addEiTypeExtensionExtraction($eiTypeId, EiTypeExtensionExtraction $eiTypeExtensionExtraction) {
		if (!isset($this->eiTypeExtensionExtractionGroups[$eiTypeId])) {
			$this->eiTypeExtensionExtractionGroups[$eiTypeId] = array();
		}
		
		$this->eiTypeExtensionExtractionGroups[$eiTypeId][] = $eiTypeExtensionExtraction;
	}
	
	public function getEiTypeExtensionExtractionGroups() {
		return $this->eiTypeExtensionExtractionGroups;
	}
	
	public function getEiModificatorExtractionGroups() {
		return $this->eiModificatorExtractionGroups;
	}
	
	public function getEiModificatorsEiTypeIds() {
		return array_keys($this->eiModificatorExtractionGroups);
	}
	
	public function getEiModificatorExtractionsByEiTypeId(string $eiTypeId) {
		if (isset($this->eiModificatorExtractionGroups[$eiTypeId])) {
			return $this->eiModificatorExtractionGroups[$eiTypeId];
		}

		return array();
	}
	
	public function setEiModificatorExtractions(string $eiTypeId, array $eiModificatorExtractions) {
		ArgUtils::valArray($eiModificatorExtractions, EiModificatorExtraction::class);
		$this->eiModificatorExtractionGroups[$eiTypeId] = $eiModificatorExtractions;
	}
	
	public function addEiModificatorExtraction(string $eiTypeId, EiModificatorExtraction $eiModificatorExtraction) {
		if (!isset($this->eiModificatorExtractionGroups[$eiTypeId])) {
			$this->eiModificatorExtractionGroups[$eiTypeId] = array();
		}
		
		$this->eiModificatorExtractionGroups[$eiTypeId][] = $eiModificatorExtraction;
	}
	
	/**
	 * @param string $entityClassName
	 * @return boolean
	 */
	function containsEntityClassName(string $entityClassName) {
		foreach ($this->eiTypeExtractions as $eiTypeExtraction) {
			if ($eiTypeExtraction->getEntityClassName() === $entityClassName) {
				return true;
			}
		}
		
		return false;
	}
	
// 	public function containsEiMaskId(string $eiTypeId, string $eiMaskId): bool {
// 		return isset($this->eiTypeExtensionExtractionGroups[$eiTypeId][$eiMaskId]);
// 	}
	
// 	public function containsEiModificatorId(string $eiTypeId, string $eiModificatorId): bool {
// 		return isset($this->eiModificatorExtractionGroups[$eiTypeId][$eiModificatorId]);
// 	}

	/**
	 * @return LaunchPadExtraction[]
	 */
	public function getLaunchPadExtractions() {
		return $this->launchPadExtractions;
	}
	
	
	public function addLaunchPadExtraction(LaunchPadExtraction $launchPadExtraction) {
		$this->launchPadExtractions[(string) $launchPadExtraction->getTypePath()] = $launchPadExtraction;
	}

}

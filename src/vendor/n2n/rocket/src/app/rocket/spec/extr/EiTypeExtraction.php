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

use n2n\persistence\orm\util\NestedSetStrategy;

class EiTypeExtraction extends TypeExtraction {
	private $entityClassName;
	private $eiMaskExtraction;
	private $dataSourceName;
	private $nestedSetStrategy;
	
	/**
	 * @return string|null
	 */
	public function getEntityClassName() {
		return $this->entityClassName;
	}
	
	/**
	 * @param string|null $entityClassName
	 */
	public function setEntityClassName(?string $entityClassName) {
		$this->entityClassName = $entityClassName;
	}

	/**
	 * @return string|null
	 */
	public function getDataSourceName() {
		return $this->dataSourceName;
	}

	/**
	 * @param string|null $dataSourceName
	 */
	public function setDataSourceName(?string $dataSourceName) {
		$this->dataSourceName = $dataSourceName;
	}
	
	/**
	 * @param NestedSetStrategy|null $nestedSetStrategy
	 */
	public function setNestedSetStrategy(?NestedSetStrategy $nestedSetStrategy) {
		$this->nestedSetStrategy = $nestedSetStrategy;
	}
	
	/**
	 * @return \n2n\persistence\orm\util\NestedSetStrategy|null
	 */
	public function getNestedSetStrategy() {
		return $this->nestedSetStrategy;
	}
	
	/**
	 * @return \rocket\spec\extr\EiMaskExtraction
	 */
	public function getEiMaskExtraction() {
		return $this->eiMaskExtraction ?? $this->eiMaskExtraction = new EiMaskExtraction();
	}
	
	/**
	 * @param EiMaskExtraction $eiMaskExtraction
	 */
	public function setEiMaskExtraction(EiMaskExtraction $eiMaskExtraction) {
		$this->eiMaskExtraction = $eiMaskExtraction;
	}
	
// 	public function isDraftDisabled() {
// 		return $this->draftDisabled;
// 	}

// 	public function setDraftDisabled($draftDisabled) {
// 		$this->draftDisabled = $draftDisabled;
// 	}
	
// 	public function addEiTypeExtensionExtraction(EiTypeExtensionExtraction $eiTypeExtensionExtraction) {
// 		$this->eiTypeExtensionExtractions[$eiTypeExtensionExtraction->getId()] = $eiTypeExtensionExtraction;
// 	}
	
// 	public function setEiTypeExtensionExtractions(array $eiTypeExtensionExtractions) {
// 		ArgUtils::valArray($eiTypeExtensionExtractions, EiTypeExtensionExtraction::class);
// 		$this->eiTypeExtensionExtractions = $eiTypeExtensionExtractions;
// 	}
	

// 	public function containsEiTypeExtensionExtractionId($eiTypeExtensionExtractionId): bool {
// 		return isset($this->eiTypeExtensionExtractions[$eiTypeExtensionExtractionId]);
// 	}
	
// 	public function getEiTypeExtensionExtractionById($eiTypeExtensionExtractionId): EiTypeExtensionExtraction {
// 		if (isset($this->eiTypeExtensionExtractions[$eiTypeExtensionExtractionId])) {
// 			return $this->eiTypeExtensionExtractions[$eiTypeExtensionExtractionId];
// 		}
		
// 		throw new UnknownMaskException('No EiMask with id \'' . $eiTypeExtensionExtractionId . '\' defined in: ' 
// 				. $this->toTypeString());
// 	}
	
// 	/**
// 	 * @return \rocket\spec\extr\EiTypeExtensionExtraction[]
// 	 */
// 	public function getEiTypeExtensionExtractions() {
// 		return $this->eiTypeExtensionExtractions;
// 	}
	
// 	public function addEiModificatorExtraction(EiComponentExtraction $eiModificatorExtraction) {
// 		$this->eiModificatorExtractions[$eiModificatorExtraction->getId()] = $eiModificatorExtraction;
// 	}
	
// 	public function setEiModificatorExtractions(array $eiModificatorExtractions) {
// 		ArgUtils::valArray($eiModificatorExtractions, EiModificatorExtraction::class);
// 		$this->eiModificatorExtractions = $eiModificatorExtractions;
// 	}
	

// 	public function containsEiModificatorEExtractionId($eiModificatorExtractionId): bool {
// 		return isset($this->eiModificatorExtractions[$eiModificatorExtractionId]);
// 	}
	
// 	public function getEiModificatorExtractionById($eiModificatorExtractionId): EiTypeExtensionExtraction {
// 		if (isset($this->eiModificatorExtractions[$eiModificatorExtractionId])) {
// 			return $this->eiModificatorExtractions[$eiModificatorExtractionId];
// 		}
		
// 		throw new UnknownMaskException('No EiModificator with id \'' . $eiModificatorExtractionId . '\' defined in: ' 
// 				. $this->toTypeString());
// 	}
	
// 	/**
// 	 * @return \rocket\spec\extr\EiModificatorExtraction[]
// 	 */
// 	public function getEiModificatorExtractions() {
// 		return $this->eiModificatorExtractions;
// 	}
	
	public function toTypeString(): string {
		return 'EiType (id: ' . $this->getId() . ', module: ' . $this->getModuleNamespace() . ')';	
	}
}

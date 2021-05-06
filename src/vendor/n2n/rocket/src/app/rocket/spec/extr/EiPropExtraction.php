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

class EiPropExtraction extends EiComponentExtraction {
	private $label;
	private $objectPropertyName;
	private $entityPropertyName;
	private $forkedEiPropExtractions = array();
	private $initCascades = [];

	/**
	 * @return string|null
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string|null $label
	 */
	public function setLabel(?string $label) {
		$this->label = $label;
	}

	/**
	 * @return string|null
	 */
	public function getObjectPropertyName() {
		return $this->objectPropertyName;
	}

	/**
	 * @param string $propertyName
	 */
	public function setObjectPropertyName(?string $propertyName) {
		$this->objectPropertyName = $propertyName;
	}

	/**
	 * @return string|null
	 */
	public function getEntityPropertyName() {
		return $this->entityPropertyName;
	}

	/**
	 * @param string|null $entityPropertyName
	 */
	public function setEntityPropertyName(?string $entityPropertyName) {
		$this->entityPropertyName = $entityPropertyName;
	}
	
	/**
	 * @return EiPropExtraction[]
	 */
	public function getForkedEiPropExtractions() {
		return $this->forkedEiPropExtractions;
	}
	
	/**
	 * @param EiPropExtraction[] $forkedEiPropExtractions
	 */
	public function setForkedEiPropExtractions(array $forkedEiPropExtractions) {
		$this->forkedEiPropExtractions = $forkedEiPropExtractions;
	}
}

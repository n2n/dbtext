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
namespace rocket\impl\ei\component\prop\translation\model;

use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObjectArray;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\impl\web\dispatch\map\val\ValArrayKeys;
use n2n\impl\web\dispatch\map\val\ValMandatoryArrayKeys;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\map\bind\MappingDefinition;
use n2n\web\dispatch\annotation\AnnoDispObject;

class TranslationForm implements Dispatchable {
	private static function _annos(AnnoInit $ai) {
		$ai->p('dispatchables', new AnnoDispObjectArray(function ($key, TranslationForm $translationForm) {
			if (isset($translationForm->availableDispatchables[$key])) {
				return $translationForm->availableDispatchables[$key];
			}
			
			return null;
		}));
		$ai->p('translationMagForm', new AnnoDispObject());
	}
	
	private $mandatoryN2nLocaleIds = array();
	private $label;
	private $markClassKey;
	private $availableDispatchables = array();
	
	protected $translationMagForm;
	protected $dispatchables;
	
	public function __construct(array $mandatoryN2nLocaleIds, string $label) {
		$this->mandatoryN2nLocaleIds = $mandatoryN2nLocaleIds;
		$this->label = $label;
		
		$this->translationMagForm = new MagForm(new MagCollection());		
		$this->dispatchables = array();
	}
	
	public function getMarkClassKey() {
		return $this->markClassKey;
	}
	
	public function getDispatchables() {
		return $this->dispatchables;
	}
	
	public function setDispatchables(array $dispatchables) {
		$this->dispatchables = $dispatchables;
	} 

	public function putDispatchable(string $n2nLocaleId, Dispatchable $dispatchable) {
		$this->dispatchables[$n2nLocaleId] = $dispatchable;
	} 
	
	public function putAvailableDispatchable(string $n2nLocaleId, Dispatchable $dispatchable) {
		$this->availableDispatchables[$n2nLocaleId] = $dispatchable;
	}
	
	public function getTranslationMagForm() {
		return $this->translationMagForm;
	}
	
	public function setTranslationMagForm(MagDispatchable $MagForm) {
		$this->translationMagForm = $MagForm;
	}
	
	public function registerMag(string $propertyName, TranslationMag $translationMag) {
		return array(
				'magWrapper' => $this->translationMagForm->getMagCollection()->addMag($propertyName, $translationMag),
				'propertyPath' => new PropertyPath(array('translationMagForm', $translationMag->getPropertyName())));
	}
	
	private function _mapping(MappingDefinition $md) {
		$md->getMappingResult()->setLabel('dispatchables', $this->label);
	}
	
	private function _validation(BindingDefinition $bd) {
		$bd->val('dispatchables', new ValArrayKeys(array_keys($this->availableDispatchables)), 
				new ValMandatoryArrayKeys($this->mandatoryN2nLocaleIds));
	}
}

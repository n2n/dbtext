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

use n2n\reflection\property\AccessProxy;
use n2n\impl\web\dispatch\mag\model\MagAdapter;
use n2n\impl\web\dispatch\property\ScalarProperty;
use rocket\ei\manage\gui\field\GuiField;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\PropertyPathPart;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\property\ManagedProperty;
use n2n\web\ui\UiComponent;
use rocket\ei\manage\entry\EiFieldValidationResult;
use n2n\web\dispatch\map\bind\BindingErrors;
use n2n\web\dispatch\mag\UiOutfitter;
use n2n\web\dispatch\map\bind\MappingDefinition;
use rocket\ei\util\entry\EiuEntry;

class TranslationMag extends MagAdapter {
	private $markClassKey;
	
	private $displayables = array();
	private $magPropertyPaths = array();
	private $validationResults = array();
	private $eiuEntries = array();
	/**
	 * @var SrcLoadConfig
	 */
	private $srcLoadConfig;

	public function __construct($label, string $markClassKey) {
		parent::__construct($label, array());
		$this->markClassKey = $markClassKey;
	}

	public function createManagedProperty(AccessProxy $accessProxy): ManagedProperty {
		return new ScalarProperty($accessProxy, true);
	}

	public function putDisplayable($n2nLocaleId, GuiField $displayable, EiFieldValidationResult $validationResult) {
		$this->displayables[$n2nLocaleId] = $displayable;
		$this->validationResults[$n2nLocaleId] = $validationResult;
	}

	public function putMagPropertyPath($n2nLocaleId, PropertyPath $magPropertyPath, EiFieldValidationResult $validationResult, 
			EiuEntry $eiuEntry) {
		$this->magPropertyPaths[$n2nLocaleId] = $magPropertyPath;
		$this->validationResults[$n2nLocaleId] = $validationResult;
		$this->eiuEntries[$n2nLocaleId] = $eiuEntry;
		$this->value[$n2nLocaleId] = 1;
	}
	
	public function setSrcLoadConfig(SrcLoadConfig $srcLoadConfig) {
		$this->srcLoadConfig = $srcLoadConfig;
	}
	
	public function setupMappingDefinition(MappingDefinition $md) {
// 		if (!$md->isDispatched()) return;
		
// 		$loadedN2nLocaleIds = $md->getDispatchedValue($this->propertyName);
// 		foreach ($this->magPropertyPaths as $n2nLocaleId => $magPropertyPath) {
// 			$md->
// 		}
	}

	public function setupBindingDefinition(BindingDefinition $bd) {
		$basePropertyPath = $bd->getPropertyPath()->reduced(1);
		
		$that = $this;
		$bd->closure(function (BindingErrors $be) use ($that, $basePropertyPath, $bd) {
			$loadedN2nLocaleIds = $bd->getMappingResult()->__get($that->propertyName);
			
			foreach ($that->magPropertyPaths as $n2nLocaleId => $magPropertyPath) {
				$propertyPath = $basePropertyPath->ext(new PropertyPathPart('dispatchables', true, $n2nLocaleId))
						->ext($magPropertyPath);
				
				$tPropertyPath = $propertyPath->reduced(1);
				if (!$bd->getBindingTree()->containsPropertyPath($tPropertyPath)) {
					continue;
				}
				
				$transDispBd = $bd->getBindingTree()->lookup($tPropertyPath);
				
				if (isset($loadedN2nLocaleIds[$n2nLocaleId])) {
					$transDispBd->reset($propertyPath->getLast()->getPropertyName());
					continue;
				}
				
				$be->addErrors($that->propertyName, $transDispBd->getMappingResult()
						->filterErrorMessages($propertyPath->getLast(), true));
			}
		});
	}

	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
		$basePropertyPath = $propertyPath->reduced(2);
		
		$propertyPaths = array();
		foreach ($this->magPropertyPaths as $n2nLocaleId => $magPropertyPath) {
			$propertyPaths[$n2nLocaleId] = $basePropertyPath->ext(new PropertyPathPart('dispatchables', true, $n2nLocaleId))
					->ext($magPropertyPath);
		}
		
		$srcLoadConfig = array();
		if ($this->srcLoadConfig !== null) {
			$srcLoadConfig = $this->srcLoadConfig->toAttrs();
		}

		return $view->getImport('\rocket\impl\ei\component\prop\translation\view\mag.html', 
				array('propertyPath' => $propertyPath, 'propertyPaths' => $propertyPaths, 'validationResults' => $this->validationResults, 
						'label' => $this->getLabel($view->getN2nLocale()),
						'srcLoadConfig' => $srcLoadConfig, 'eiuEntries' => $this->eiuEntries,
						'markClassKey' => $this->markClassKey));
	}
}

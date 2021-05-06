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
namespace rocket\impl\ei\component\prop\relation\model\filter;

use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use n2n\util\type\ArgUtils;
use n2n\impl\web\dispatch\mag\model\MagAdapter;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\util\filter\form\FilterGroupForm;
use n2n\impl\web\dispatch\property\ObjectProperty;
use n2n\reflection\property\AccessProxy;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\property\ManagedProperty;
use n2n\web\ui\UiComponent;
use rocket\ei\util\filter\controller\FilterJhtmlHook;
use n2n\web\dispatch\mag\UiOutfitter;

class RelationFilterGroupMag extends MagAdapter {
	private $targetFilterDefinition;
	
	private $targetFilterSettingGroup;
	private $filterJhtmlHook;
	
	public function __construct(FilterDefinition $targetFilterDefinition, 
			FilterJhtmlHook $filterJhtmlHook) {
		parent::__construct('Target Filter');
	
		$this->targetFilterDefinition = $targetFilterDefinition;
		$this->filterJhtmlHook = $filterJhtmlHook;
	}
	
	public function setValue($value) {
		ArgUtils::assertTrue($value instanceof FilterSettingGroup);
		
		$this->targetFilterSettingGroup = $value;
	}
	
	public function getValue() {
		return $this->targetFilterSettingGroup;
	}
	
	public function getFormValue() {
		return new FilterGroupForm($this->targetFilterSettingGroup, $this->targetFilterDefinition);
	}
	
	public function setFormValue($formValue) {
		ArgUtils::assertTrue($formValue instanceof FilterGroupForm);
		
		$this->targetFilterSettingGroup = $formValue->buildFilterSettingGroup();
	}
	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createManagedProperty($accessProxy)
	 */
	public function createManagedProperty(AccessProxy $accessProxy): ManagedProperty {
		return new ObjectProperty($accessProxy, false);
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::setupBindingDefinition($bindingDefinition)
	 */
	public function setupBindingDefinition(BindingDefinition $bindingDefinition) {
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createUiField($propertyPath, $view)
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
		return $view->getImport('\rocket\ei\util\filter\view\filterForm.html', 
				array('propertyPath' => $propertyPath,
						'filterJhtmlHook' => $this->filterJhtmlHook));
	}	
}

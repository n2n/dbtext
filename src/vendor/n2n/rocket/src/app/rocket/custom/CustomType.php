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
namespace rocket\custom;

use n2n\core\container\N2nContext;
use n2n\reflection\ReflectionUtils;
use rocket\spec\Type;

class CustomType extends Type {
	private $controllerLookupId;
	
	public function __construct(string $id, string $moduleNamespace, string $controllerLookupId) {
		parent::__construct($id, $moduleNamespace);
		$this->controllerLookupId = $controllerLookupId;
	}
	
	public function getLabel() {
		return 'Custom Spec: ' . $this->getId();
	}
	
	public function getControllerLookupId() {
		return $this->controllerLookupId;
	}
		
	/* (non-PHPdoc)
	 * @see \rocket\spec\Spec::createController()
	 */
	public function lookupController(N2nContext $n2nContext) {
		$controller = ReflectionUtils::createObject($this->controllerLookupId);
		$n2nContext->magicInit($controller);
		return $controller;
	}
	/* (non-PHPdoc)
	 * @see \rocket\spec\Spec::getOverviewPathExt()
	 */
	public function getOverviewPathExt() {
		return null;
	}
	
	public function hasSecurityOptions() {
		return false;
	}
	
	public function getPrivilegeOptions(N2nContext $n2nContext) {}
	
	public function createAccessMagCollection(N2nContext $n2nContext) {}
	
	public function createRestrictionSelectorItems(N2nContext $n2nContext) {}
	
// 	public function toTypeExtraction() {
// 		$extraction = new CustomTypeExtraction($this->getId(), $this->getModule());
// 		$extraction->setControllerClassName($this->controllerClass->getName());
// 		$extraction->setLabel($this->getLabel());
// 		return $extraction;
// 	}
}

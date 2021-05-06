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
namespace rocket\impl\ei\component\prop\adapter\gui;

use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\gui\GuiPropSetup;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;

/**
 * Don't use this class directly. Use factory methods of {@see GuiFields}.  
 */
class GuiPropProxy implements GuiProp {
	private $guiPropSetupCallback;
	
	/**
	 * @param \Closure $closure
	 */
	function __construct(\Closure $guiPropSetupCallback) {
		$this->guiPropSetupCallback = new \ReflectionFunction($guiPropSetupCallback);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiPropSetup()
	 */
	function buildGuiPropSetup(Eiu $eiu, ?array $defPropPaths): ?GuiPropSetup {
		$mmi = new MagicMethodInvoker($eiu->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $eiu);
		$mmi->setParamValue('defPropPaths', $defPropPaths);
		$mmi->setReturnTypeConstraint(TypeConstraints::type(GuiPropSetup::class, true));
		
		return $mmi->invoke(null, $this->guiPropSetupCallback);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiProp::buildGuiField()
	 */
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		if ($this->guiFieldAssembler !== null) {
			return $this->guiFieldAssembler->buildGuiField($eiu, $readOnly);
		}
		
		if ($this->guiFieldClosure !== null) {
			
		}
		
		return null;
	}
	
	function getForkGuiDefinition(): ?GuiDefinition {
		return null;
	}
}

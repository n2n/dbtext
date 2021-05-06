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
namespace rocket\ei\manage\gui;

use n2n\util\type\ArgUtils;
use rocket\ei\manage\DefPropPath;
use n2n\util\ex\UnsupportedOperationException;

class GuiStructureDeclaration {
	private $label;
	private $helpText;
	private $siStructureType;
	private $defPropPath;
	private $children;
	
	private function __construct() {
	}
	
	/**
	 * @return string|null
	 */
	function getLabel() {
		UnsupportedOperationException::assertTrue($this->defPropPath === null);
		
		return $this->label;
	}
	
	/**
	 * @return string|null
	 */
	function getHelpText() {
		UnsupportedOperationException::assertTrue($this->defPropPath === null);
		
		return $this->helpText;
	}
	
	/**
	 * @return string|null
	 */
	function getSiStructureType() {
		return $this->siStructureType;
	}
	
	function hasDefPropPath() {
		return $this->defPropPath !== null;
	}
	
	/**
	 * @return DefPropPath
	 */
	function getDefPropPath() {
		UnsupportedOperationException::assertTrue($this->defPropPath !== null);
		
		return $this->defPropPath;
	}
	
	
	function hasChildrean() {
		return $this->children !== null;
	}
	
	/**
	 * @return GuiStructureDeclaration[]
	 */
	function getChildren() {
		UnsupportedOperationException::assertTrue($this->children !== null);
		
		return $this->children;
	}
	
	/**
	 * @param GuiStructureDeclaration $child
	 */
	function addChild(GuiStructureDeclaration $child) {
		UnsupportedOperationException::assertTrue($this->children !== null);
		
		$this->children[] = $child;
	}
	
	function getAllDefPropPaths() {
		if ($this->defPropPath !== null) {
			return [(string) $this->defPropPath => $this->defPropPath];
		}
		
		$defPropPaths = [];
		foreach ($this->children as $child) {
			$defPropPaths = array_merge($defPropPaths, $child->getAllDefPropPaths());
		}
		return $defPropPaths;
	}
	
	/**
	 * @param string $siStructureType
	 * @param DefPropPath $defPropPath
	 * @param string|null $label
	 * @param string|null $helpText
	 * @return GuiStructureDeclaration
	 */
	static function createField(DefPropPath $defPropPath, string $siStructureType) {
		$gsd = new GuiStructureDeclaration();
		$gsd->siStructureType = $siStructureType;
		$gsd->defPropPath = $defPropPath;
		return $gsd;
	}
	
	/**
	 * @param string $siStructureType
	 * @param GuiStructureDeclaration[] $children
	 * @param string|null $label
	 * @param string|null $helpText
	 * @return GuiStructureDeclaration
	 */
	static function createGroup(array $children, string $siStructureType, ?string $label, string $helpText = null) {
		ArgUtils::valArray($children, GuiStructureDeclaration::class);
		$gsd = new GuiStructureDeclaration();
		$gsd->siStructureType = $siStructureType;
		$gsd->children = $children;
		$gsd->label = $label;
		$gsd->helpText = $helpText;
		return $gsd;
	}
}


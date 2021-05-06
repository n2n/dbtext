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
namespace rocket\si\meta;

use n2n\util\type\ArgUtils;

class SiStructureDeclaration implements \JsonSerializable {
	private $propId;
	private $label;
	private $helpText;
	private $structureType;
	private $children = [];
	
	/**
	 * @param SiProp $siPropId
	 * @param string $label
	 */
	private function __construct(?string $structureType, ?string $propId, ?string $label, ?string $helpText, array $children = []) {
		$this->setStructureType($structureType);
		$this->label = $label;
		$this->helpText = $helpText;
		$this->propId = $propId;
		$this->setChildren($children);
	}
	
	/**
	 * @param string $structureType
	 * @param string $propId
	 * @return \rocket\si\meta\SiStructureDeclaration
	 */
	static function createProp(string $structureType, string $propId) {
		return new SiStructureDeclaration($structureType, $propId, null, null);
	}
	
	/**
	 * @param string $structureType
	 * @param string $label
	 * @param string $helpText
	 * @return \rocket\si\meta\SiStructureDeclaration
	 */
	static function createGroup(string $structureType, ?string $label, ?string $helpText) {
		return new SiStructureDeclaration($structureType, null, $label, $helpText);
	}
	
	/**
	 * @return SiProp
	 */
	public function getPropId() {
		return $this->propId;
	}

	/**
	 * @param string $propId
	 * @return \rocket\si\meta\SiProp
	 */
	public function setPropId(?string $propId) {
		$this->propId = $propId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStructureType() {
		return $this->structureType;
	}

	/**
	 * @param string $displayType
	 * @return \rocket\si\meta\SiProp
	 */
	public function setStructureType(?string $structureType) {
		ArgUtils::valEnum($structureType, SiStructureType::all(), null, true);
		$this->structureType = $structureType;
		return $this;
	}
	
	/**
	 * @return SiStructureDeclaration[]
	 */
	public function getChildren() {
		return $this->children;
	}
	
	/**
	 * @param SiStructureDeclaration[] $children
	 * @return SiStructureDeclaration
	 */
	public function setChildren(array $children) {
		ArgUtils::valArray($children, SiStructureDeclaration::class);
		$this->children = $children;
		return $this;
	}
	
	public function jsonSerialize() {
		return [
			'structureType' => $this->structureType,
			'propId' => $this->propId,
			'label' => $this->label,
			'helpText' => $this->helpText,
			'children' => $this->children
		];
	}

}

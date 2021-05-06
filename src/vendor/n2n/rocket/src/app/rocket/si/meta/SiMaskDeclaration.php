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

class SiMaskDeclaration implements \JsonSerializable {
	/**
	 * @var SiMask
	 */
	private $mask;
	/**
	 * @var SiStructureDeclaration[]|null
	 */
	private $structureDeclarations;
	
	/**
	 * @param SiMask $type
	 * @param SiStructureDeclaration[] $structureDeclarations
	 */
	function __construct(SiMask $mask, ?array $structureDeclarations) {
		$this->mask = $mask;
		$this->setStructureDeclarations($structureDeclarations);
	}
	
	/**
	 * @param SiMask $type
	 * @return SiMaskDeclaration
	 */
	function setType(SiMask $type) {
		$this->mask = $type;
		return $this;
	}
	
	/**
	 * @return SiMask
	 */
	function getType() {
		return $this->mask;
	}

	/**
	 * @param SiStructureDeclaration[] $structureDeclarations
	 * @return SiMaskDeclaration
	 */
	function setStructureDeclarations(?array $structureDeclarations) {
		ArgUtils::valArray($structureDeclarations, SiStructureDeclaration::class, true);
		$this->structureDeclarations = $structureDeclarations;
		return $this;
	}
	
	/**
	 * @param string $typeId
	 * @param SiStructureDeclaration[] $structureDeclaration
	 * @return SiMaskDeclaration
	 */
	function addStructureDeclaration(SiStructureDeclaration $structureDeclaration) {
		$this->structureDeclarations[] = $structureDeclaration;
		return $this;
	}
	
	function hasStructureDeclarations() {
		return $this->structureDeclarations !== null;
	}
	
	/**
	 * @return array|null
	 */
	function getStructureDeclarations() {
		return $this->structureDeclarations;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'mask' => $this->mask,
			'structureDeclarations' => $this->structureDeclarations
		];
	}
}
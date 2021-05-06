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

use n2n\util\type\attrs\DataSet;

class SiMaskQualifier implements \JsonSerializable {
	private $identifier;
	private $name;
	private $iconClass;
	
	function __construct(SiMaskIdentifier $identifier, string $name, string $iconClass) {
		$this->identifier = $identifier;
		$this->name = $name;
		$this->iconClass = $iconClass;
	}
	
	/**
	 * @return string
	 */
	function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 * @return \rocket\si\meta\SiMaskQualifier
	 */
	function setName(string $name) {
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return \rocket\si\meta\SiMaskIdentifier
	 */
	function getIdentifier() {
		return $this->identifier;
	}
	
	function jsonSerialize() {
		return [
			'identifier' => $this->identifier,
			'name' => $this->name,
			'iconClass' => $this->iconClass
		];
	}

	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return \rocket\si\meta\SiMaskQualifier
	 */
	static function parse(array $data) {
		$ds = new DataSet($data);
	
		try {
			return new SiMaskQualifier(SiMaskIdentifier::parse($ds->reqArray('identifier')),
					$ds->reqString('name'), $ds->reqString('iconClass'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}
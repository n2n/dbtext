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

use n2n\util\uri\Url;
use n2n\util\type\attrs\DataSet;

class SiStyle implements \JsonSerializable {
	private $bulky;
	private $readOnly;
	
	/**
	 * @param bool $siref
	 * @param Url $url
	 * @param string $label
	 */
	function __construct(bool $bulky, bool $readOnly) {
		$this->bulky = $bulky;
		$this->readOnly = $readOnly;
	}
	
	/**
	 * @return bool
	 */
	function isBulky() {
		return $this->bulky;
	}
	
	/**
	 * @return bool
	 */
	function isReadOnly() {
		return $this->readOnly;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'bulky' => $this->bulky,
			'readOnly' => $this->readOnly
		];
	}
	
	/**
	 * @param array $data
	 * @return \rocket\si\meta\SiStyle
	 */
	static function createFromData(array $data) {
		$ds = new DataSet($data);
		
		return new SiStyle($ds->reqBool('bulky'), $ds->reqBool('readOnly'));
	}
}
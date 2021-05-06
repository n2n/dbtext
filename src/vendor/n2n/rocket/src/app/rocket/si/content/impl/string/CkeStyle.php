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
namespace rocket\si\content\impl\string;

class CkeStyle implements \JsonSerializable {
	private $name;
	private $tag;
	private $class;
	
	public function __construct(string $name, string $tag, string $class = null) {
		$this->name = $name;
		$this->tag = $tag;
		$this->class = $class;
	}
	
	public function getName() {
		return $this->name;
	}

	public function setName(string $name) {
		$this->name = $name;
	}

	public function getTag() {
		return $this->tag;
	}

	public function setTag(string $element) {
		$this->tag = $element;
	}

	public function getClass() {
		return $this->class;
	}

	public function setClass(string $class) {
		$this->class = $class;
	}
	
	function jsonSerialize() {
		return [
			'name' => $this->name,
			'tag' => $this->tag,
			'class' => $this->class
		];
	}
}

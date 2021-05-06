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
namespace rocket\spec\extr;

class InitCascade {
	const TYPE_DELETE = 'delete';
	const TYPE_ALWAYS = 'always';
	
	private $className;
	private $type;
	
	function __construct(string $className, string $type) {
		$this->className = $className;
		$this->type = $type;
	}
	
	/**
	 * @return string[]
	 */
	static function getTypes() {
		return [ self::TYPE_ALWAYS, self::TYPE_DELETE ];
	}
	
	/**
	 * @return string
	 */
	function getClassName() {
		return $this->className;
	}
	
	/**
	 * @return string
	 */
	function getType() {
		return $this->type;
	}
}
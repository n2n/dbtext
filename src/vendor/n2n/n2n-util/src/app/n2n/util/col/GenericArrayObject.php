<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\util\col;

use n2n\util\type\ArgUtils;

class GenericArrayObject extends \ArrayObject implements Collection {
	private $genericType;
	
	public function __construct(array $array = null, $genericType = null) {
		parent::__construct((array) $array);
		$this->genericType = $genericType;
	}

	public function offsetSet($index, $newval) {
		ArgUtils::valType($newval, $this->genericType);
		parent::offsetSet($index, $newval);
	}

	public function append($value) {
		ArgUtils::valType($value, $this->genericType);
		parent::append($value);
	}
	
	public function isEmpty() {
		return 0 == $this->count();
	}
	
	public function clear() {
		$this->exchangeArray(array());
	}
	
	public function exchangeArray($input) {
		if (is_array($input)) {
			ArgUtils::valArray($input, $this->genericType);
		}
		parent::exchangeArray($input);
	}
}

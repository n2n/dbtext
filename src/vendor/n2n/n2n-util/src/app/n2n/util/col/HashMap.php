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
use n2n\util\HashUtils;

class HashMap implements Map {
	private $keys = array();
	private $values = array();
	
	private $genericKeyType;
	private $genericValueType;

	public function __construct($genericKeyType = null, $genericValueType = null) {
		$this->genericKeyType = $genericKeyType;
		$this->genericValueType = $genericValueType;
	}

	public function getKeyByHashCode(string $hashCode) {
		if (isset($this->keys[$hashCode])) {
			return $this->keys[$hashCode];
		}

		return null;
	}
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($key, $value) {
		ArgUtils::valType($key, $this->genericKeyType);
		ArgUtils::valType($value, $this->genericValueType);
		
		$hashCode = HashUtils::hashCode($key);
		$this->keys[$hashCode] = $key;
		$this->values[$hashCode] = $value;
	}
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($key) {
		$hashCode = HashUtils::hashCode($key);
		if (isset($this->values[$hashCode])) {
			return $this->values[$hashCode];
		}
		return null;
	}
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($key) {
		$hashCode = HashUtils::hashCode($key);
		unset($this->keys[$hashCode]);
		unset($this->values[$hashCode]);
	}

	public function clear() {
		$this->keys = array();
		$this->values = array();
	}

	public function isEmpty() {
		return empty($this->values);
	}

	public function count() {
		return sizeof($this->values);
	}
	/* (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($key) {
		return isset($this->keys[HashUtils::hashCode($key)]);
	}

	public function getIterator() {
		return new HashMapIterator(array_keys($this->keys), array_values($this->values));
	}
	
	public function getKeys() {
		return $this->keys;
	}
	
	public function getValues() {
		return $this->values;
	}
}

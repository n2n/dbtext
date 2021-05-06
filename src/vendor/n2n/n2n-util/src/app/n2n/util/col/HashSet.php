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

class HashSet implements Set {
	private $values = array();
	private $genericType;
	
	public function __construct($genericType = null) {
		$this->genericType = $genericType;
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::add()
	 */
	public function add($arg) {
		ArgUtils::valType($arg, $this->genericType);
		
		$this->values[HashUtils::hashCode($arg)] = $arg;
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::addAll()
	 */
	public function addAll(array $args) {
		foreach ($args as $arg) {
			$this->add($arg);
		}
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::remove()
	 */
	public function remove($arg) {
		unset($this->values[HashUtils::hashCode($arg)]);	
	}	
	
	public function removeAll(array $args) {
		foreach ($args as $arg) {
			$this->remove($arg);
		}
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::clear()
	 */
	public function clear() {
		$this->values = array();
	}

	/* (non-PHPdoc)
	 * @see \n2n\util\Set::contains()
	 */
	public function contains($arg) {
		return array_key_exists(HashUtils::hashCode($arg), $this->values);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::isEmpty()
	 */
	public function isEmpty() {
		return empty($this->values);	
	}
	
	/* (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new \ArrayIterator($this->values);	
	}
	
	/* (non-PHPdoc)
	 * @see Countable::count()
	 */
	public function count() {
		return sizeof($this->values);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\util\Set::toArray()
	 */
	public function toArray() {
		return $this->values;
	}
}

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
namespace n2n\persistence\orm\query\from;

use n2n\util\ex\IllegalStateException;

class TreePath {
	const PROPERTY_NAME_SEPARATOR = '.';
	
	private $nextPropertyNames;
	protected  $donePropertyNames = array();
	/**
	 * @param array $propertyNames
	 */
	public function __construct(array $propertyNames) {
		if (0 == count($propertyNames)) {
			throw new \InvalidArgumentException();
		}
		$this->nextPropertyNames = $propertyNames;
	}
	
	/**
	 * @return \n2n\persistence\orm\query\from\TreePath
	 */
	public function copy() {
		$treePath = new TreePath($this->nextPropertyNames);
		$treePath->donePropertyNames = $this->donePropertyNames;
		return $treePath;
	}
	
	/**
	 * @return string
	 */
	public function next() {
		if (null !== ($propertyName = array_shift($this->nextPropertyNames))) {
			$this->donePropertyNames[] = $propertyName;
			return $propertyName;
		}
		
		throw new IllegalStateException();
	}
	
	public function hasNext() {
		return !empty($this->nextPropertyNames);
	}
	
	public function getNext() {
		if ($this->hasNext()) {
			return reset($this->nextPropertyNames);
		}
		
		throw new IllegalStateException();
	}
	
	public function getNumDones() {
		return count($this->donePropertyNames);
	}
	
	public function getDones($offeset = 0, $length = null) {
		return array_slice($this->donePropertyNames, $offeset, $length);
	}
	

	public function getNumNext() {
		return count($this->nextPropertyNames);
	}
	
	public function getNexts($offeset = 0, $length = null) {
		return array_slice($this->nextPropertyNames, $offeset, $length);
	}
	
	public function getAll() {
		return array_merge($this->donePropertyNames, $this->nextPropertyNames);
	}
	
	public static function prettyPropertyStr(array $properyNames) {
		return implode(self::PROPERTY_NAME_SEPARATOR, $properyNames);
	}
}

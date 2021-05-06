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
namespace n2n\io\ini;

use n2n\io\IoUtils;
use n2n\io\IoException;

class IniReader {
	private $values;
	private $groups;
	/**
	 * 
	 * @param string $iniString
	 * @throws IniInitializationFailedException
	 */
	public function __construct($iniString, $processGroups = true) {
		try {
			$this->values = IoUtils::parseIniString($iniString, $processGroups);
			$this->groups = array();
			foreach ($this->values as $key => $value) {
				if (is_array($value)) {
					$this->groups[] = $key;
				}
			}
		} catch (IoException $e) {
			throw new IniInitializationFailedException('todo', 0, $e);
		}
	}
	/**
	 * 
	 * @return array
	 */	
	public function getGroups() {
		return $this->groups;
	}
	/**
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function hasValue($key) {
		return array_key_exists($key, $this->values);
	}
	/**
	 * 
	 * @param string $key
	 * @return mixed
	 * @throws UnknownIniPropertyException
	 */
	public function getValue($key) {
		if (!array_key_exists($key, $this->values)) {
			throw new UnknownIniPropertyException('Unknown ini key: ' . $key);
		}
		
		return $this->values[$key];
	}
	/**
	 * 
	 * @param string $group
	 * @param string $key
	 * @return bool
	 */
	public function hasGroupedValue($group, $key) {
		return array_key_exists($group, $this->values) 
				&& is_array($this->values[$group])
				&& array_key_exists($key, $this->values[$group]);
	}
	/**
	 * 
	 * @param string $group
	 * @param string $key
	 * @return mixed
	 * @throws UnknownIniPropertyException
	 */
	public function getGroupedValue($group, $key) {
		if (!array_key_exists($group, $this->values) || !is_array($this->values[$group])) {
			throw new UnknownIniPropertyException('Unknown ini group: ' . $key);
		}
		
		if (!array_key_exists($key, $this->values[$group])) {
			throw new UnknownIniPropertyException('Unknown grouped ini key. group: ' . $group . ', key: ' . $key);
		}
		
		return $this->values[$group][$key];
	}
	/**
	 * 
	 * @param string $group
	 * @return bool
	 */
	public function hasGroup($group) {
		return array_key_exists($group, $this->values) && is_array($this->values[$group]);
	}
	/**
	 * 
	 * @param string $group
	 * @throws UnknownIniPropertyException
	 */
	public function getGroupValues($group) {
		if (!array_key_exists($group, $this->values) || !is_array($this->values[$group])) {
			throw new UnknownIniPropertyException('Unknown ini group: ' . $group);
		}
		
		return $this->values[$group];
	}
}

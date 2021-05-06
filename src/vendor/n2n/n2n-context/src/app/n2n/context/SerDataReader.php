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
namespace n2n\context;

use n2n\util\UnserializationFailedException;
use n2n\util\StringUtils;
use n2n\util\type\TypeUtils;

class SerDataReader {
	private $attrs;
	
	public function __construct(array $attrs) {
		$this->attrs = $attrs;
	}
	
	public function contains($name) {
		return array_key_exists($name, $this->attrs);
	}
	
	private function ensureExists($name) {
		if ($this->contains($name)) return;
		
		throw new UnserializationFailedException('Serialized data contains not attribute \'' . $name . '\'');
	}
	
	public function get($name, $defaultValue = null) {
		$this->ensureExists($name);
		
		return $this->attrs[$name];
	}
	
	private function getAndValidateType($name, $expectedType, $nullAllowed) {
		$value = $this->get($name);
		
		if (TypeUtils::isValueA($value, $expectedType, $nullAllowed)) return $value;
		
		throw new UnserializationFailedException('Type ' . $expectedType . ' expected for serialized data attribute \'' 
				. $name . '\'. Type ' . TypeUtils::getTypeInfo($value) . ' given.');
	}
	
	public function getInt($name, $nullAllowed = true) {
		return $this->getAndValidateType($name, 'int', $nullAllowed);
	}
	
	public function getString($name, $nullAllowed = true) {
		return $this->getAndValidateType($name, 'string', $nullAllowed);
	}
	
	public function getNumeric($name, $nullAllowed = true) {
		return $this->getAndValidateType($name, 'numeric', $nullAllowed);
	}
	
	public function getObject($name, $nullAllowed = true, $type = null) {
		if ($type === null) $type = 'object';
		
		return $this->getAndValidateType($name, $type, $nullAllowed);
	}
	
	public static function createFromSerializedStr($serializedStr) {
		$attrs = StringUtils::unserialize($serializedStr);
		if (!is_array($attrs)) {
			throw new UnserializationFailedException('Serialized data are corrupted.');
		}
		
		return new SerDataReader($attrs);
	}
}

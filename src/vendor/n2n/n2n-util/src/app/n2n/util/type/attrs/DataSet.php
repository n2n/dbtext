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
namespace n2n\util\type\attrs;

use n2n\util\type\TypeConstraint;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use n2n\util\col\ArrayUtils;
use n2n\util\StringUtils;
use n2n\util\type\TypeUtils;
use n2n\util\type\TypeName;

class DataSet {
	private $attrs;
	private $interceptor;
	/**
	 * 
	 * @param array $attrs
	 */
	public function __construct(array $attrs = null) {
		$this->attrs = (array) $attrs;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function isEmpty() {
		return empty($this->attrs);
	}
	/**
	 *
	 * @return boolean
	 */
	public function contains(string $name) {
		return array_key_exists($name, $this->attrs);
	}
	
	public function getNames() {
		return array_keys($this->attrs);
	}
	
	public function hasKey(string $name, $key) {
		return array_key_exists($name, $this->attrs) 
				&& is_array($this->attrs[$name])
				&& array_key_exists($key, $this->attrs[$name]);
	}
	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function set(string $name, $value) {
		$this->attrs[$name] = $value;
	}
	/**
	 * 
	 * @param string $name
	 * @param mixed $key scalar
	 * @param mixed $value
	 */
	public function add(string $name, string $key, $value) {
		if(!isset($this->attrs[$name]) || !is_array($this->attrs[$name])) {
			$this->attrs[$name] = array();
		}
	
		$this->attrs[$name][$key] = $value;
	}
	/**
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function push(string $name, $value) {
		if(!isset($this->attrs[$name]) || !is_array($this->attrs[$name])) {
			$this->attrs[$name] = array();
		}
	
		$this->attrs[$name][] = $value;
	}
	
	private function retrieve(string $name, $type, $mandatory, $defaultValue = null, &$found = null) {
		$typeConstraint = TypeConstraint::build($type);
		
		if (!$this->contains($name)) {
			$found = false;
			if (!$mandatory) return $defaultValue;
			throw new MissingAttributeFieldException('Unknown attribute: ' . $name);
		}
		
		$found = true;
		$value = $this->attrs[$name];
		
		if ($typeConstraint === null) {
			return $value;
		}
		
		try {
			$typeConstraint->validate($value);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw new InvalidAttributeException('Property contains invalid value: ' . $name, 0, $e);
		}
		
		return $value;
	}

	
	/**
	 * @param string|AttributePath|array $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::req()} or {@see self::opt()}
	 */
	public function get($name, bool $mandatory = true, $defaultValue = null, TypeConstraint $typeConstraint = null) {
		if ($mandatory) {
			return $this->req($name, $typeConstraint);
		}
		
		return $this->opt($name, $typeConstraint, $defaultValue);
	}
	
	/**
	 * @param string $name
	 * @param bool $mandatory
	 * @param mixed $defaultValue
	 * @param TypeConstraint $typeConstraint
	 * @throws InvalidAttributeException
	 * @return mixed
	 */
	public function req(string $name, $type = null) {
		return $this->retrieve($name, $type, true);
	}
	
	public function opt(string $name, $type = null, $defaultValue = null) {
		return $this->retrieve($name, $type, false, $defaultValue);
	}
	
	/**
	 * @param string|AttributePath|array $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqScalar()} or {@see self::optScalar()}
	 */
	public function getScalar(string $name, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqScalar($name, $nullAllowed);
		}
		
		return $this->optScalar($name, $defaultValue, $nullAllowed);
	}
	
	public function reqScalar(string $name, bool $nullAllowed = false) {
		return $this->req($name, TypeConstraint::createSimple('scalar', $nullAllowed));
	}
	
	public function optScalar(string $name, $defaultValue = null, bool $nullAllowed = true) {
		return $this->opt($name, TypeConstraint::createSimple('scalar', $nullAllowed), $defaultValue);
	}
	
	/**
	 * @param string|AttributePath|array $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqString()} or {@see self::optString()}
	 */
	public function getString(string $name, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqString($name, $nullAllowed);
		}
		
		return $this->optString($name, $defaultValue, $nullAllowed); 
	}
	
	public function reqString(string $name, bool $nullAllowed = false, bool $lenient = true) {
		if (!$lenient) {
			return $this->req($name, TypeConstraint::createSimple('string', $nullAllowed));
		}
		
		if (null !== ($value = $this->reqScalar($name, $nullAllowed))) {
			return (string) $value;
		}
		
		return null;
	}
	
	public function optString(string $name, $defaultValue = null, $nullAllowed = true, bool $lenient = true) {
		if (!$lenient) {
			return $this->opt($name, TypeConstraint::createSimple('string', $nullAllowed), $defaultValue);
		}
		
		if (null !== ($value = $this->optScalar($name, $defaultValue, $nullAllowed))) {
			return (string) $value;
		}
		
		return null;
	}
	
	/**
	 * @param string|AttributePath|array $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqBool()} or {@see self::optBool()}
	 */
	public function getBool(string $name, bool $mandatory = true, $defaultValue = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqBool($name, $nullAllowed);
		}
		
		return $this->optBool($name, $defaultValue, $nullAllowed);
	}
	
	public function reqBool(string $name, bool $nullAllowed = false, $lenient = true) {
		if (!$lenient) {
			return $this->req($name, TypeConstraint::createSimple('bool', $nullAllowed));
		}
		
		if (null !== ($value = $this->reqScalar($name, $nullAllowed))) {
			return (bool) $value;
		}
		
		return null;
	}
	
	public function optBool(string $name, $defaultValue = null, bool $nullAllowed = true, $lenient = true) {
		if (!$lenient) {
			return $this->opt($name, TypeConstraint::createSimple('bool', $nullAllowed), $defaultValue);
		}
		
		if (null !== ($value = $this->optScalar($name, $defaultValue, $nullAllowed))) {
			return (bool) $value;
		}
		
		return $defaultValue;
	}
	
	public function reqNumeric(string $name, bool $nullAllowed = false) {
		return $this->req($name, TypeConstraint::createSimple('numeric', $nullAllowed));
	}
	
	public function optNumeric(string $name, $defaultValue = null, bool $nullAllowed = true) {
		return $this->opt($name, TypeConstraint::createSimple('numeric', $nullAllowed), $defaultValue);
	}
	
	public function reqInt(string $name, bool $nullAllowed = false, $lenient = true) {
		if (!$lenient) {
			return $this->req($name, TypeConstraint::createSimple('int', $nullAllowed));
		}
		
		if (null !== ($value = $this->reqNumeric($name, $nullAllowed))) {
			return (int) $value;
		}
		
		return null;
	}
	
	public function optInt(string $name, $defaultValue = null, bool $nullAllowed = true, $lenient = true) {
		if (!$lenient) {
			return $this->opt($name, TypeConstraint::createSimple('int', $nullAllowed), $defaultValue);
		}
		
		if (null !== ($value = $this->optNumeric($name, $defaultValue))) {
			return (int) $value;
		}
			
		return $defaultValue;
	}
	
	public function reqEnum(string $name, array $allowedValues, bool $nullAllowed = false) {
		return $this->getEnum($name, $allowedValues, true, null, $nullAllowed);
	}
	
	public function optEnum(string $name, array $allowedValues, $defaultValue = null, bool $nullAllowed = true) {
		return $this->getEnum($name, $allowedValues, false, $defaultValue, $nullAllowed);
	}
	
	private function getEnum(string $name, array $allowedValues, $mandatory = true, $defaultValue = null, $nullAllowed = false) {
		$found = null;
		$value = $this->retrieve($name, null, $mandatory, $defaultValue, $found);
		
		if (!$found) return $defaultValue;
	
		if ($nullAllowed && $value === null) {
			return $value;
		}
		
		if (!ArrayUtils::inArrayLike($value, $allowedValues)) {
			throw new InvalidAttributeException('Property \'' . $name
				. '\' must contain one of following values: ' . implode(', ', $allowedValues) 
				. '. Given: ' . TypeUtils::buildScalar($value));
		}
	
		return $value;
	}
	
	/**
	 * @param string|AttributePath|array $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param TypeConstraint|string|null $fieldType
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqArray()} or {@see self::optArray()}
	 */
	public function getArray(string $name, bool $mandatory = true, $defaultValue = array(), $fieldType = null, bool $nullAllowed = false) {
		if ($mandatory) {
			return $this->reqArray($name, $fieldType, $nullAllowed);
		}
		
		return $this->optArray($name, $fieldType, $defaultValue, $nullAllowed);
	}
	
	public function reqArray(string $name, $fieldType = null, bool $nullAllowed = false) {
		return $this->req($name, TypeConstraint::createArrayLike('array', $nullAllowed, $fieldType));
	}
	
	public function optArray(string $name, $fieldType = null, $defaultValue = [], bool $nullAllowed = false) {
		return $this->opt($name, TypeConstraint::createArrayLike('array', $nullAllowed, $fieldType), $defaultValue);
	}
	
	/**
	 * @param string|AttributePath|array $name
	 * @param bool $mandatory
	 * @param mixed|null $defaultValue
	 * @param bool $nullAllowed
	 * @return mixed|null
	 * @deprecated use {@see self::reqScalarArray()} or {@see self::optScalarArray()}
	 */
	public function getScalarArray(string $name, bool $mandatory = true, $defaultValue = array(), bool $nullAllowed = false, bool $fieldNullAllowed = true) {
		if ($mandatory) {
			return $this->reqScalarArray($name, $nullAllowed, $fieldNullAllowed);
		}
		
		return $this->optScalarArray($name, $defaultValue, $nullAllowed, $fieldNullAllowed);
	}
	
	/**
	 * @param string $name
	 * @param bool $nullAllowed
	 * @param bool $fieldNullAllowed
	 * @return array|null
	 */
	public function reqScalarArray(string $name, bool $nullAllowed = false, bool $fieldNullAllowed = false) {
		return $this->reqArray($name, TypeConstraint::createSimple('scalar', $fieldNullAllowed), $nullAllowed);
	}
	
	/**
	 * @param string $name
	 * @param array $defaultValue
	 * @param bool $nullAllowed
	 * @param bool $fieldNullAllowed
	 * @return array|mixed
	 */
	public function optScalarArray(string $name, $defaultValue = [], bool $nullAllowed = false, bool $fieldNullAllowed = false) {
		return $this->optArray($name, TypeConstraint::createSimple('scalar', $fieldNullAllowed), $defaultValue, $nullAllowed);
	}
	
	/**
	 * @param string $name
	 * @param bool $nullAllowed
	 * @return \n2n\util\type\attrs\DataSet|null
	 */
	public function reqDataSet(string $name, bool $nullAllowed = false) {
		return new DataSet($this->reqArray($name, null, $nullAllowed));
	}
	
	/**
	 * @param string $name
	 * @param mixed $defaultValue
	 * @param bool $nullAllowed
	 * @return \n2n\util\type\attrs\DataSet|null
	 */
	public function optDataSet(string $name, $defaultValue = null, bool $nullAllowed = true) {
		if (null !== ($array = $this->optArray($name, null, $defaultValue, $nullAllowed))) {
			return new DataSet($array);
		}
		
		return null;
	}
	
	/**
	 * @param string $name
	 * @param bool $nullAllowed
	 * @return \n2n\util\type\attrs\DataSet[]|null
	 */
	public function reqDataSets(string $name, bool $nullAllowed = false) {
		$dataSetDatas = $this->reqArray($name, TypeName::ARRAY, $nullAllowed);
		if ($dataSetDatas === null) {
			return null;
		}
		
		$dataSets = [];
		foreach ($dataSetDatas as $key => $dataSetData) {
			$dataSets[$key] = new DataSet($dataSetData);
		}
		return $dataSets;
	}

	/**
	 * @param string $name
	 */
	public function remove(string ...$names) {
		foreach ($names as $name) {
			unset($this->attrs[$name]);
		}
	}
	
	/**
	 * @param string $name
	 * @param mixed $key scalar
	 */
	public function removeKey(string $name, $key) {
		if ($this->hasKey($name, $key)) {
			unset($this->attrs[$name][$key]);
		}
	}
	
	/** 
	 * @param array $attrs
	 */
	public function setAll(array $attrs) {
		$this->attrs = $attrs;
	}
	
	/**
	 * @return array
	 */
	public function toArray() {
		return $this->attrs;
	}
	
	/** 
	 * @param DataSet $dataSet
	 */
	public function append(DataSet $dataSet) {
		$this->appendAll($dataSet->toArray());
	}
	
	/** 
	 * @param array $attrs
	 */
	public function appendAll(array $attrs, bool $ignoreNull = false) {
		foreach ($attrs as $key => $value) {
			if ($ignoreNull && $value === null) continue;
			
			if (is_array($value) && isset($this->attrs[$key]) && is_array($this->attrs[$key])) {
				$value = array_merge($this->attrs[$key], $value);
// 				$value = $this->merge($this->attrs[$key], $value);
			}
			
			$this->attrs[$key] = $value;
		}
	}
	
	public function removeNulls(bool $recursive = false) {
		$this->removeNullsR($this->attrs, $recursive);
	}
	
	private function removeNullsR(array &$attrs, bool $recursive = false) {
		foreach ($attrs as $key => $value) {
			if (!isset($attrs[$key])) {
				unset($attrs[$key]);
			} else if ($recursive && is_array($attrs[$key])) {
				$this->removeNullsR($attrs[$key], true);
			}
		}
	}
	
	/**
	 * @param array $attrs
	 * @param array $attrs2
	 */
	protected function merge(array $attrs, array $attrs2) {
		foreach ($attrs2 as $key => $value) {
			if (is_numeric($key)) {
				$attrs[] = $attrs2[$key];
				continue;
			}
				
			if (!array_key_exists($key, $attrs)) {
				$attrs[$key] = $value;
				continue;
			}
				
			if (is_array($attrs[$key])) {
				$attrs[$key] = $this->merge($attrs[$key], $attrs2[$key]);
				continue;
			}
				
			$attrs[$key] = $value;
		}
	
		return $attrs;
	}
	/**
	 * 
	 * @return string
	 */
	public function serialize() {
		return serialize($this->attrs);
	}
	/**
	 * 
	 * @param string $serialized
	 * @param \n2n\util\UnserializationFailedException
	 */
	public static function createFromSerialized($serialized) {
		$attrs = StringUtils::unserialize($serialized);
		if (!is_array($attrs)) $attrs = array();
		return new Attributes($attrs);
	}
}

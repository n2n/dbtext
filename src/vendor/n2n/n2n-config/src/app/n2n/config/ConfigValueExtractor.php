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
namespace n2n\config;

use n2n\util\type\TypeUtils;

class ConfigValueExtractor {
	private $data;
	private $configSourceName;
	private $baseProperty;
	private $groupName;
	private $stage;	
	/**
	 * @param array $data
	 * @param string $configSourceName
	 * @param ConfigProperty $baseProperty
	 * @param string $groupName
	 * @param string $stage
	 */
	public function __construct(array $data, $configSourceName, ConfigProperty $baseProperty = null, 
			$groupName = null, $stage = null) {
		$this->data = $data;
		$this->configSourceName = (string) $configSourceName;
		$this->baseProperty = $baseProperty;
		$this->groupName = $groupName;
		$this->stage = $stage;
	}
	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}
	
	public function getConfigSourceName() {
		return $this->configSourceName;
	}
	/**
	 * @param mixed $propertyExpression
	 * @return \n2n\config\ConfigProperty
	 */
	protected function createConfigProperty($propertyExpression) {
		if ($this->baseProperty === null) {
			return ConfigProperty::create($propertyExpression);
		}
		
		return $this->baseProperty->ext($propertyExpression);
	} 
	/**
	 * @param string $key
	 * @param mixed $default
	 * @param bool $required
	 * @throws InvalidConfigurationException
	 * @return mixed
	 */
	protected function getLevelValue($key, $default, $required) {
		if (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}
		
		if (!$required) return $default;
		
		throw new InvalidConfigurationException('Missing property \'' .
				$this->createConfigProperty($key)->__toString() . '\' in config source: ' 
						. $this->configSourceName);
	}
	/**
	 * @param mixed $key
	 * @param bool $required
	 * @throws InvalidConfigurationException
	 * @return \n2n\config\ConfigValueExtractor
	 */
	protected function getLevelValueExtractor($key, $required) {
		if (isset($this->levelValueExtractors[$key])) {
			return $this->levelValueExtractors[$key];
		}
		
		$levelValue = $this->getLevelValue($key, null, $required);
		if ($levelValue === null) return null;
		
		$configProperty = $this->createConfigProperty($key);
		if (is_array($levelValue)) {
			return new ConfigValueExtractor($levelValue, $this->configSourceName,
					$configProperty, $this->groupName, $this->stage);
		}
		
		throw new InvalidConfigurationException('Property \'' . $configProperty->__toString() 
				. '\' must be an object or array, ' . TypeUtils::getTypeInfo($levelValue) 
				. ' given in config source: ' . $this->configSourceName);
	}
	/**
	 * @param mixed $key
	 * @return boolean
	 */
	protected function containsOnLevel($key) {
		return array_key_exists($key, $this->data);
	}
	/**
	 * @return array
	 */
	public function getLevelKeys() {
		return array_keys($this->data);
	}
	/**
	 * @param mixed $property
	 * @return boolean
	 */
	public function contains($property) {
		$configProperty = ConfigProperty::create($property);
		$valueExtractor = $this->getValueExtractor($configProperty->createBaseProperty(), false);
		return $valueExtractor !== null && $valueExtractor->containsOnLevel($configProperty->getLastPart());
	}
	/**
	 * @param mixed $property
	 * @param string $default
	 * @param bool $required
	 * @throws InvalidConfigurationException
	 * @return mixed
	 */
	public function getValue($property, $required = false, $default = null) {
		$configProperty = $this->createConfigProperty($property);
		
		$valueExtractor = $this;
		foreach ($configProperty->getBaseParts() as $part) {
			$valueExtractor = $valueExtractor->getLevelValueExtractor($part, $required);
			if ($valueExtractor === null) return $default;
		}

		return $valueExtractor->getLevelValue($property, $default, $required);
	}
	/**
	 * @param mixed $property
	 * @param string $default
	 * @param bool $required
	 * @throws InvalidConfigurationException
	 * @return mixed
	 */
	public function getScalar($property, $required = false, $default = null) {
		$value = $this->getValue($property, $required, $default);
		if (is_scalar($value) || (!$required && $value === $default)) {
			return $value;
		}
		
		throw new InvalidConfigurationException('Property \'' . $this->createConfigProperty($property)->__toString() 
				. '\' must be scalar (integer, float, string or boolean), ' 
				. TypeUtils::getTypeInfo($value) . ' given in config source: ' 
				. $this->configSourceName);
	}
	/**
	 * @param mixed $property
	 * @param string $default
	 * @param bool $required
	 * @throws InvalidConfigurationException
	 * @return number
	 */
	public function getNumber($property, $required = false, $default = null) {
		$value = $this->getValue($property, $required, $default);
		if (is_numeric($value) || (!$required && $value === $default)) {
			return $value;
		}
		
		throw new InvalidConfigurationException('Property \'' .
				$this->createConfigProperty($property) . '\' must be numeric, ' . TypeUtils::getTypeInfo($value) 
						. ' given in config source: ' . $this->configSourceName);
	}
// 	/**
// 	 * @param mixed $property
// 	 * @param string $default
// 	 * @param bool $required
// 	 * @throws InvalidConfigurationException
// 	 * @return number
// 	 */
// 	public function getInt($property, $required = false, $default = null) {
// 		$value = $this->getValue($property, $default, $required);
// 		if (is_int($value) || (!$required && $value === $default)) {
// 			return $value;
// 		}
		
// 		throw new InvalidConfigurationException('Property \'' .
// 				$configProperty->__toString() . '\' must be an integer, ' . TypeUtils::getTypeInfo($value) 
// 						. ' given in config source: ' . $this->configSourceName);
// 	}
	/**
	 * @param mixed $property
	 * @param mixed $default
	 * @param bool $required
	 * @throws InvalidConfigurationException
	 * @return boolean
	 */
	public function getBool($property, $required = false, $default = null) {
		$value = $this->getValue($property, $required, $default);
		if (is_bool($value) || (!$required && $value === $default)) {
			return $value;
		}
		
		throw new InvalidConfigurationException('Property \'' .
				ConfigProperty::create($property)->__toString() . '\' must be a boolean, ' . TypeUtils::getTypeInfo($value) 
						. ' given in config source: ' . $this->configSourceName);
	}
	/**
	 * @param mixed $property
	 * @param string $default
	 * @param bool $required
	 * @throws InvalidConfigurationException
	 * @return array
	 */
	public function getArray($property, $required = false, $default = null) {
		$value = $this->getValue($property, $required, $default);
		if (is_array($value) || (!$required && $value === $default)) {
			return $value;
		}
		
		throw new InvalidConfigurationException('Property \'' .
				ConfigProperty::create($property)->__toString() . '\' must be an array, ' . TypeUtils::getTypeInfo($value) 
						. ' given in config source: ' . $this->configSourceName);
	}
	/**
	 * @param mixed $property
	 * @param string $default
	 * @param bool $required
	 * @throws InvalidConfigurationException
	 * @return array
	 */
	public function getScalarArray($property, $required = false, $default = null) {
		$value = $this->getArray($property, $required, $default);
		if (!$required && $value === $default) {
			return $value;
		}
		
		foreach ((array) $value as $key => $fieldValue) {
			if (is_scalar($value[$key])) continue;
			
			throw new InvalidConfigurationException('Property \'' . $this->createConfigProperty($property). '[' 
					. $key . ']\' must be scalar (integer, float, string or boolean), ' 
					. TypeUtils::getTypeInfo($value) . ' given in config source: ' 
					. $this->configSourceName);
		}
		
		return $value;
	}
	/**
	 * @param mixed $property
	 * @param array $options
	 * @param bool $required
	 * @param string $default
	 * @throws InvalidConfigurationException
	 * @return string
	 */
	public function getEnumValue($property, array $options, $required = false, $default = null) {
		$value = $this->getValue($property, $required, $default);
		
		if (in_array($value, $options) || (!$required && $value === $default)) {
			return $value;
		}
		
		throw new InvalidConfigurationException('Invalid value for property \'' 
				. $this->createConfigProperty($property) . '\' (allowed: ' . implode(', ', $options) 
				. '). \'' . TypeUtils::buildUsefullValueIdentifier($value) 
				. '\' given in config source: ' . $this->configSourceName);
	}
	/**
	 * @param mixed $property
	 * @param bool $required
	 * @return \n2n\config\ConfigValueExtractor;
	 */
	public function getValueExtractor($property, $required = false) {
		$valueExtractor = $this;
		foreach (ConfigProperty::create($property)->toArray() as $key => $part) {
			$valueExtractor = $valueExtractor->getLevelValueExtractor($key, $required);
			if ($valueExtractor === null) return null;
		}
		return $valueExtractor;
	}	
}

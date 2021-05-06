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
namespace n2n\util\ini;

use n2n\util\StringUtils;

class IniRepresentation {
	
	const COMMENT_IDENTIFIER = ';';
	const GROUP_START_IDENTIFIER = '[';
	const GROUP_END_IDENTIFIER = ']';
	const PROPERTY_ASSIGNATOR = '=';
	const VALUE_IDENTIFIER = '"';
	const ARRAY_PROPERTY_START_IDENTIFIER = '[';
	const ARRAY_PROPERTY_END_IDENTIFIER = ']';
	
	/**
	 * @var \n2n\util\ini\ContentPart[]
	 */
	private $contentParts;
	
	public function __construct($iniString) {
		$this->contentParts = array();
		$this->initialize($iniString);
	}
	/**
	 * @return \n2n\util\ini\ContentPart[] $contentParts
	 */
	public function getContentParts() {
		return $this->contentParts;
	}
	/**
	 * @param \n2n\util\ini\ContentPart[] $contentParts
	 */
	public function setContentParts($contentParts) {
		$this->contentParts = $contentParts;
	}
	
	public function appendContentPart(ContentPart $contentPart) {
		if ($contentPart instanceof Property && count($this->findGroups()) > 0) {
			throw new \InvalidArgumentException('Properties are not allowed if groups are available.'
 					. ' Property must be appended to a group.');
		} 
		$this->contentParts[] = $contentPart;
	}
	
	public function removeContentPart(ContentPart $contentPart) {
		if (false !== ($key = array_search($contentPart, $this->contentParts))) {
			unset($this->contentParts[$key]);
		}
	}
	/**
	 * @return \n2n\util\ini\Group[]
	 */
	public function findGroups() {
		$groups = array();
		foreach ($this->contentParts as $contentPart) {
			if ($contentPart instanceof Group) {
				$groups[$contentPart->getName()] = $contentPart;
			}
		}
		return $groups;
	}
	/**
	 * @param string $name
	 * @return \n2n\util\ini\Group
	 */
	public function findGroupByName($name) {
		$name = $this->purifyGroupName($name);
		foreach ($this->contentParts as $contentPart) {
			if ($contentPart instanceof Group && $this->purifyGroupName($contentPart->getName()) == $name) {
				return $contentPart;
			}
		}
		return null;
	}
	/**
	 * @param string $name
	 * @return \n2n\util\ini\Property 
	 */
	public function findPropertyByName($name) {
		foreach ($this->contentParts as $contentPart) {
			if ($contentPart instanceof Property && $contentPart->getName() == $name) {
				return $contentPart;
			} elseif ($contentPart instanceof Group) {
				break;
			}
		}
		return null;
	}
	/**
	 * @param string $prefix
	 * @return \n2n\util\ini\Property[]
	 */
	public function findPropertiesWithPrefix($prefix) {
		$properties = array();
		foreach ((array) $this->contentParts as $contentPart) {
			if ($contentPart instanceof Property && StringUtils::startsWith($prefix, $contentPart->getName())) {
				$properties[] = $contentPart;
			}
		}
		if (0 === count($properties)) {
			return null;
		}
		return $properties;
	}
	
	public function findPropertyByGroupAndName($group, $name) {
		if (null !== ($group = $this->findGroupByName($group))) {
			return $group->findPropertyByName($name);
		}
		return null;
	}
	
	public function addProperty(Property $property) {
		$contentParts = array();
		$added = false;
		foreach ($this->contentParts as $contentPart) {
			if ($contentPart instanceof Group && $added == false) {
				$contentParts[] = $property;
				$added = true;
			}
			$contentParts[] = $contentPart;
		}
		$this->contentParts = $contentParts;
	}
	
	public function replace(array $rawData) {
		$contentParts = array();
		//first remove all of the Contentparts which are not Part of the rawData
		foreach ($this->contentParts as $contentPart) {
			if ($contentPart instanceof DataContentPart) {
				if (!array_key_exists($contentPart->getName(), $rawData)) {
					continue;
				}
				if ($contentPart instanceof SimpleProperty) {
					if (is_array($rawData[$contentPart->getName()])) {
						continue;
					}
				} else {
					if (!is_array(($rawDataArray = $rawData[$contentPart->getName()]))) {
						continue;
					}
					if ($contentPart instanceof Group) {
						foreach ((array) $contentPart->getGroupables() as $groupable) {
							if (!($groupable instanceof Property)) continue;
							if (!array_key_exists($groupable->getName(), $rawDataArray) 
									|| ($groupable instanceof ArrayProperty && is_scalar($rawDataArray[$groupable->getName()]))
									|| ($groupable instanceof SimpleProperty && is_array($rawDataArray[$groupable->getName()]))) {
								$contentPart->removeGroupable($groupable);
								continue;
							}
							
						}
					}
				}
			}
			$contentParts[] = $contentPart;
		}
		$this->contentParts = $contentParts;
		//Then add / update the new data
		foreach ($rawData as $dataContentPartName => $dataContentPartValue) {
			if (is_scalar($dataContentPartValue)) {
				if (null !== ($property = $this->findPropertyByName($dataContentPartName))) {
					$property->setValue($dataContentPartValue);
				} else {
					$this->addProperty(SimpleProperty::createWithAndValue($dataContentPartName, $dataContentPartValue));
				}
			} elseif (is_array($dataContentPartValue)) {
				if (null !== ($arrayProperty = $this->findPropertyByName($dataContentPartName))) {
					$arrayProperty->setValue($dataContentPartValue);
				} else { 
					//We assume that every array in the root array is a group (if there s not a arrayProperty already)
					if (null === ($group = $this->findGroupByName($dataContentPartName))) {
						$group = Group::createWithName($dataContentPartName);
						$this->appendContentPart($group);
					}
					foreach ($dataContentPartValue as $propertyName => $propertyValue) {
						if (null !== $property = $group->findPropertyByName($propertyName)) {
							$property->setValue($propertyValue);
						} else {
							
							if (is_array($propertyValue)) {
								$property = ArrayProperty::createWithNameAndValue($propertyName , $propertyValue);
							} else {
								$property = SimpleProperty::createWithNameAndValue($propertyName, $propertyValue);
							}
							$group->appendGroupable($property);
						}
					}
				}
			}
		}
		$contentParts = array();
		foreach ($rawData as $key => $value) {
			$contentParts[] = $this->getContentPartWithName($key);
		}
		$this->contentParts = $contentParts;
	}
	
	public function __toString(): string {
		return implode('', $this->contentParts);
	}
	
	public function toArray() {
		$data = array();
		foreach ($this->contentParts as $contentPart) {
			if ($contentPart instanceof DataContentPart) {
				if ($contentPart instanceof SimpleProperty) {
					$data[$contentPart->getName()] = $contentPart->getValue();
				} else {
					if ($contentPart instanceof Group) {
						$groupName = $contentPart->getName();
						$data[$groupName] = array();
						foreach ((array) $contentPart->getGroupables() as $groupable) {
							if (!($groupable instanceof Property)) continue;
							$data[$groupName][$groupable->getName()] = $groupable->getValue();
						}
					}
				}
			}
		}
		return $data;
	}
	
	public static function isGroup($str) {
		return 1 === preg_match('/^\\s*' . preg_quote(self::GROUP_START_IDENTIFIER) . '.*' . 
				preg_quote(self::GROUP_END_IDENTIFIER) . '/', $str);
	}
	
	public static function isSimpleProperty($str) {
		return 1 === preg_match('/^\\s*(\\w*\\.?)+\\s*' . preg_quote(self::PROPERTY_ASSIGNATOR) . '\\s*(\d+|true|false|' . 
				preg_quote(self::VALUE_IDENTIFIER) . '.*' . preg_quote(self::VALUE_IDENTIFIER) .')/', $str);
	}
	
	public static function isArrayProperty($str) {
		return 1 === preg_match('/^\\s*(\\w*\\.?)+' . preg_quote(self::ARRAY_PROPERTY_START_IDENTIFIER) . '\\S*' . 
				preg_quote(self::ARRAY_PROPERTY_END_IDENTIFIER) . '\\s*' . preg_quote(self::PROPERTY_ASSIGNATOR) . '\\s*(\d+|true|false|' . 
				preg_quote(self::VALUE_IDENTIFIER) . '.*' . preg_quote(self::VALUE_IDENTIFIER) . ')/', $str);
	}
	
	public static function isComment($str) {
		return 1 === preg_match('/^\\s*' . preg_quote(self::COMMENT_IDENTIFIER) . '/', $str);
	}

	public static function isContentPart($str) {
		return self::isGroup($str) || self::isArrayProperty($str) || self::isSimpleProperty($str) || self::isComment($str);
	}
	
	private function initialize($iniString) {
		$stack = array();
		$currentGroup = null;
		$currentArrayProperty = null;
		$previousLine = null;
		$previousContentPart = null;
		foreach (explode(PHP_EOL, $iniString) as $line) {
			$stack[] = $line;
			if (self::isComment($line) && $previousContentPart instanceof DefaultContentPart 
					&& $previousLine !== null  && !self::isContentPart($previousLine)) {
				$previousContentPart = new DefaultContentPart($stack);
				if (null === $currentGroup) {
					$this->appendContentPart($previousContentPart);
				} else {
					if (null === $currentArrayProperty) {
						$currentGroup->appendGroupable($previousContentPart);	
					} else {
						$currentArrayProperty->appendItem($previousContentPart);
					}
				}
				$stack = array();
			} else {
				if (self::isGroup($line)) {
					$currentArrayProperty = null;
					$currentGroup = new Group($stack);
					$this->appendContentPart($currentGroup);
					$previousContentPart = $currentGroup;
					$stack = array(); 
				} elseif (self::isSimpleProperty($line)) {
					$currentArrayProperty = null;
					$previousContentPart = new SimpleProperty($stack);
					if (null === $currentGroup) {
						$this->appendContentPart($previousContentPart);
					} else {
						$currentGroup->appendGroupable($previousContentPart);
					}
					$stack = array();
				} elseif (self::isArrayProperty($line)) {
					$arrayPropertyName = ArrayProperty::extractNameFromString($line);
					if (null === $currentGroup) {
						$arrayProperty = $this->findPropertyByName($arrayPropertyName);
					} else {
						$arrayProperty = $currentGroup->findPropertyByName($arrayPropertyName);
					}
					if (null === $arrayProperty) {
						$arrayProperty = new ArrayProperty($stack);
						if (null == $currentGroup) {
							$this->appendContentPart($arrayProperty);
						} else {
							$currentGroup->appendGroupable($arrayProperty);
						}
					} else {
						$arrayProperty->appendItem(new SimpleProperty($stack));
					}
					
					$stack = array();
					$currentArrayProperty = $arrayProperty;
					$previousContentPart = $currentArrayProperty;
				}
			}
			$previousLine = $line;
		}
	}
	
	private function removeNotExistingItemsFromArrayProperty($rawDataArray, ArrayProperty $arrayProperty) {
		foreach (array_keys($arrayProperty->getValue()) as $simplePropertyName) {
			if (!array_key_exists($simplePropertyName, $rawDataArray)) {
				$arrayProperty->removeItem($arrayProperty->findPropertyByName($simplePropertyName));
			}
		}
	}
	
	private function purifyGroupName($groupName) {
		return strtolower(preg_replace('/\s/', '', $groupName));
	}
	
	private function getContentPartWithName($name) {
		foreach ($this->contentParts as $contentPart) {
			if (!($contentPart instanceof DataContentPart)) continue;
			if ($contentPart->getName() === $name) return $contentPart;
		}
		return null;
	}
}

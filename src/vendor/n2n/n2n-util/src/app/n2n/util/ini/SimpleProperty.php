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

class SimpleProperty extends ContentPartAdapter implements Property, ArrayPropertyItem {
	const VALUE_TRUE = 'true';
	const VALUE_FALSE = 'false';
	
	private $inlineComment;
	private $name;
	private $value;
	
	/**
	 * @param array $lines
	 * @throws \\InvalidArgumentException
	 */
	public function __construct(array $lines) {
		$this->inlineComment = null;
		$this->name = null;
		$this->value = null;
		$line = array_pop($lines);
		$lineArray = preg_split('/=/', $line, 2);
		if (count($lineArray) == 2) {
			$maskedName = $lineArray[0];
			$maskedValue = $lineArray[1];
			
			$this->name = self::extractNameFromString($maskedName);
			
			if (count(($maskedValueArray = preg_split('/\\s' . preg_quote(IniRepresentation::COMMENT_IDENTIFIER) . '/',
					 $maskedValue, 2))) > 1) {
				$maskedValue = $maskedValueArray[0];
				$this->inlineComment = IniRepresentation::COMMENT_IDENTIFIER . trim($maskedValueArray[1]);
			}
			$this->value = self::extractValueFromString($maskedValue);
			if (null !== $this->value && null !== $this->name) {
				parent::__construct($lines);
			} else {
				throw new \InvalidArgumentException('Invalid key value pair structure in line "' . $line
						. '". Expected structure: {key} = "{value}" ;{inlineComment}');
			}
		} else {
			throw new \InvalidArgumentException('Missing assignation in key value pair in line: "' . $line . '"');
		}
	}
	
	public function getValue() {
		return $this->value;	
	}
	
	public function setValue($value) {
		$this->value = $value;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}
	
	public function getComment() {
		$parentComment = parent::getComment();
		if (strlen($parentComment)) {
			$parentComment = parent::getComment();
		}
		return $parentComment . $this->inlineComment;
	}
	
	public function __toString(): string {
		return parent::__toString() . $this->name . ' ' . IniRepresentation::PROPERTY_ASSIGNATOR . ' ' . $this->getValueForOutput() . 
				$this->inlineComment . PHP_EOL;
	}
	
	public static function extractNameFromString($str) {
		$matches = array();
		preg_match('/^\\s*(\\w+\\.?)+(' . preg_quote(IniRepresentation::ARRAY_PROPERTY_START_IDENTIFIER)  . '.*' . 
				 preg_quote(IniRepresentation::ARRAY_PROPERTY_END_IDENTIFIER) . ')?/', $str, $matches);
		return trim($matches[0]);
	}
	
	public static function extractValueFromString($str) {
		$matches = array();
		preg_match('/' . preg_quote(IniRepresentation::PROPERTY_ASSIGNATOR) . '?\\s*(\d+|true|false|' 
				. preg_quote(IniRepresentation::VALUE_IDENTIFIER) . '.*' . preg_quote(IniRepresentation::VALUE_IDENTIFIER) . ')/', $str, $matches);
		if (count($matches) > 0) {
			
			$value = trim(str_replace(IniRepresentation::VALUE_IDENTIFIER, '', 
					preg_replace('/' . preg_quote(IniRepresentation::PROPERTY_ASSIGNATOR) . '/', '', $matches[0], 1)));
			switch ($value) {
				case self::VALUE_FALSE:
					return false;
				case self::VALUE_TRUE:
					return true;
				default:
					return $value;
			}
		} else {
			return null;
		}
	}
	
	public static function generateConditionedValueFor($value) {
		if (is_bool($value)) {
			if ($value) {
				return self::VALUE_TRUE;
			}
			return self::VALUE_FALSE;
		} else if(is_numeric($value)) {
			return $value;
		}
		return IniRepresentation::VALUE_IDENTIFIER . $value . IniRepresentation::VALUE_IDENTIFIER;
	}
	
	public static function createWithNameAndValue($name, $value) {
		return new SimpleProperty(array($name . IniRepresentation::PROPERTY_ASSIGNATOR
				. self::generateConditionedValueFor($value)));
	}
	
	private function getValueForOutput() {
		return self::generateConditionedValueFor($this->value);
	}
}

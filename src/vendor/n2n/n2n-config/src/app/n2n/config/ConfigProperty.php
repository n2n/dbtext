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

class ConfigProperty {
	const PART_SEPARATOR = '.';
	
	private $parts;
	private $partsCount;
	/**
	 * @param array $parts
	 */
	private function __construct(array $parts) {
		$this->parts = $parts;
		$this->partsCount = count($this->parts);
	}
	
	public function getSize() {
		return $this->partsCount;
	}
	
	public function getBaseParts() {
		if ($this->partsCount < 2) return array();
		return array_slice($this->parts, 0, $this->partsCount - 1);
	}
	
	public function getLastPart() {
		return end($this->parts);
	}
	
	public function createBaseProperty() {
		return new ConfigProperty($this->getBaseParts());
	}
	/**
	 * @return array 
	 */
	public function toArray() {
		return $this->parts;
	}
	/**
	 * @return string
	 */
	public function __toString(): string {
		return implode(self::PART_SEPARATOR, $this->parts);
	}
	/**
	 * @param mixed $expression array or string
	 * @throws \InvalidArgumentException
	 * @return \n2n\config\ConfigProperty
	 */
	public static function create($expression) {
		if ($expression instanceof ConfigProperty) {
			return $expression;
		}
		
		if (empty($expression) && $expression !== 0) {
			throw new \InvalidArgumentException('Empty property passed.');
		}
		
		if (is_array($expression)) {
			return new ConfigProperty($expression);
		}
		
		return new ConfigProperty(array((string) $expression));
	}
}

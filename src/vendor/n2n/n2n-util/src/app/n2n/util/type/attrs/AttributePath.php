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

use n2n\util\type\TypeUtils;

class AttributePath {
	const SEPARATOR = '/';
	const WILDHARD = '*';
	
	private $names = [];
	
	public function __construct(array $names) {
		foreach ($names as $name) {
			if (empty($name) && $name !== 0) {
				continue;
			}
			
			array_push($this->names, $name);
		}
	}
	
	public function size() {
		return count($this->names);
	}
	
	public function slices(int $offset, int $length = null) {
		return new AttributePath(array_slice($this->names, $offset, $length));
	}
	
	public function toArray() {
		return $this->names;
	}
	
	/**
	 * @param string|string[]|AttributePath $expression
	 * @return NULL|\n2n\util\type\attrs\AttributePath
	 */
	public static function build($expression) { 
		if ($expression === null) {
			return null;
		}
		
		return self::create($expression);
	}
	
	/**
	 * @param string|string[]|AttributePath $expression
	 * @throws \InvalidArgumentException
	 * @return \n2n\util\type\attrs\AttributePath
	 */
	public static function create($expression) {
		if ($expression instanceof AttributePath) {
			return $expression;
		}
		
		if (is_array($expression)) {
			return new AttributePath($expression);
		}
		
		if (is_scalar($expression)) {
			return new AttributePath(explode(self::SEPARATOR, $expression));
		}
		
		throw new \InvalidArgumentException('Invalid AttributePath expression type: ' 
				. TypeUtils::getTypeInfo($expression));
	}
	
	/**
	 * @param array $expressions
	 * @return \n2n\util\type\attrs\AttributePath[]
	 */
	public static function createArray(array $expressions) {
		$paths = [];
		foreach ($expressions as $expression) {
			$paths[] = self::create($expression);
		}
		return $paths;
	}
	
	public function __toString(): string {
		return implode(self::SEPARATOR, $this->names);
	}
	
	/**
	 * @param string $pathPart
	 * @param string $name
	 * @return boolean
	 */
	public static function match(string $pathPart, string $name) {
		return self::matchesWildcard($pathPart) || $pathPart == $name; 
	}
	
	/**
	 * @param string $pathPart
	 * @return boolean
	 */
	public static function matchesWildcard(string $pathPart) {
		return self::WILDHARD == $pathPart; 
	}
}

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
use n2n\util\StringUtils;
use n2n\util\type\TypeName;

class ArrayUtils {

	public static function shift(array &$array, bool $required = false) {
		if ($required && empty($array)) {
			throw new \OutOfRangeException('Array empty.');
		}
		
		return array_shift($array);
	}
	
	public static function first($arrayLike) {
		if (is_array($arrayLike)) {
			return self::reset($arrayLike);
		}
		
		ArgUtils::valArrayLike($arrayLike);
		$arr = $arrayLike->getArrayCopy();
		return self::reset($arr);
	}
	
	public static function last(array $array) {
		return self::end($array);
	}
	
	public static function reset(array &$array) {
		if (false !== ($result = reset($array))) {
			return $result;
		}
	
		return null;
	}
	
	public static function current(array &$array) {
		if (false !== ($result = current($array))) {
			return $result;
		}
		
		return null;
	}
	
	public static function end(array &$array) {
		if (false !== ($result = end($array))) {
			return $result;
		}
		
		return null;
	}
	
	public static function isArrayLike($value) {
		return TypeName::isValueArrayLike($value);
	}
	
	public static function isClassArrayLike(\ReflectionClass $class) {
		return TypeName::isClassArrayLike($class);
	}
	
	public static function isTypeNameArrayLike(string $typeName) {
		return TypeName::isArrayLike($typeName);
	}
	
	public static function inArrayLike($needle, $arrayLike) {
		ArgUtils::valArrayLike($arrayLike);
		
		foreach ($arrayLike as $value) {
// 			if ($value === $needle) return true;
			
			if ($value === null || $needle === null || is_object($needle) || is_object($value)
					|| is_array($needle) || is_array($value)) {
				if ($value === $needle) return true;
				continue; 
			}
			
			if (StringUtils::doEqual($value, $needle)) return true;
		}
		
		return false;
	}
}

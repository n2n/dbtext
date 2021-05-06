<?php
namespace n2n\util\type;

class TypeName {
	const STRING = 'string';
	const INT = 'int';
	const FLOAT = 'float';
	const BOOL = 'bool';
	const ARRAY = 'array';
	const RESOURCE = 'resource';
	const OBJECT = 'object';
	
	const PSEUDO_SCALAR = 'scalar';
	const PSEUDO_MIXED = 'mixed';
	const PSEUDO_ARRAYLIKE = 'arraylike';
	const PSEUDO_NUMERIC = 'numeric';
	
	/**
	 * @param string $typeName
	 * @return boolean
	 */
	static function isScalar(string $typeName) {
		switch ($typeName) {
			case self::STRING:
			case self::INT:
			case self::FLOAT:
			case self::BOOL:
			case self::PSEUDO_SCALAR:
			case self::PSEUDO_NUMERIC:
				return true;
			default:
				return false;
		}
	}
	
	/**
	 * @param mixed $value
	 * @param string $typeName
	 */
	static function convertValue($value, string $typeName) {
		switch ($typeName) {
			case self::STRING;
				if (is_scalar($value)) {
					return (string) $value;
				}
				
				throw self::createValueNotConvertableException($value, $typeName);
			case self::BOOL:
				return (bool) $value;
			case self::FLOAT:
				if (is_numeric($value)) {
					return (float) $value;
				}
				
				throw self::createValueNotConvertableException($value, $typeName);
			case self::INT:
				if (is_numeric($value) && (int) $value == $value) {
					return (int) $value;
				}
				
				throw self::createValueNotConvertableException($value, $typeName);
				
			default:
				throw new \InvalidArgumentException('It is not possible to convert a value to ' . $typeName);
		}
	}
	
	/**
	 * @param string $typeName
	 * @return bool
	 */
	static function isConvertable(string $typeName) {
		switch ($typeName) {
			case self::STRING:
			case self::BOOL:
			case self::INT:
			case self::FLOAT:
				return true;
			default:
				return false;
		}
	}
	
	/**
	 * @param mixed $value
	 * @param string $typeName
	 * @throws \InvalidArgumentException
	 */
	private static function createValueNotConvertableException($value, string $typeName) {
		throw new \InvalidArgumentException('Value ' . TypeUtils::getTypeInfo($value) . ' is not convertable to ' . $typeName);
	}
	
	/**
	 * @param string $testingTypeName
	 * @param string $typeName
	 * @return boolean
	 */
	static function isA(string $testingTypeName, string $typeName) {
		if ($testingTypeName == $typeName || $typeName == self::PSEUDO_MIXED) {
			return true;
		}
		
		switch ($testingTypeName) {
			case self::INT:
			case self::FLOAT:
				return $typeName == self::PSEUDO_NUMERIC || $typeName == self::PSEUDO_SCALAR;
			case self::STRING:
			case self::BOOL:
			case self::PSEUDO_NUMERIC:
				return $typeName == self::PSEUDO_SCALAR;
			case self::ARRAY:
				return $typeName == self::PSEUDO_ARRAYLIKE;
			case self::OBJECT:
			case self::RESOURCE:
			case self::PSEUDO_MIXED:
			case self::PSEUDO_SCALAR:
				return false;
		}
		
		if ($typeName == self::PSEUDO_ARRAYLIKE) {
			return self::isArrayLike($testingTypeName);
		}
		
		return is_subclass_of($testingTypeName, $typeName);
	}
	
	/**
	 * @param mixed $value
	 * @param string $typeName
	 * @return boolean
	 */
	static function isValueA($value, string $typeName) {
		switch ($typeName) {
			case TypeName::PSEUDO_MIXED:
				return true;
			case TypeName::PSEUDO_SCALAR:
				return is_scalar($value);
			case TypeName::ARRAY:
				return is_array($value);
			case TypeName::STRING:
				return is_string($value);
			case TypeName::PSEUDO_NUMERIC:
				return is_numeric($value);
			case TypeName::INT:
				return is_int($value);
			case TypeName::FLOAT:
				return is_float($value);
			case TypeName::BOOL:
				return is_bool($value);
			case TypeName::OBJECT:
				return is_object($value);
			case TypeName::RESOURCE:
				return is_resource($value);
			case TypeName::PSEUDO_ARRAYLIKE:
				return self::isValueArrayLike($value);
			case 'null':
			case 'NULL':
				return $value === null;
			default:
				return is_a($value, $typeName);
		}
	}
	
	/**
	 * @param mixed $value
	 * @return boolean
	 */
	static function isValueArrayLike($value) {
		return is_array($value) || ($value instanceof \ArrayAccess
				&& $value instanceof \IteratorAggregate && $value instanceof \Countable);
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @return boolean
	 */
	static function isClassArrayLike(\ReflectionClass $class) {
		return $class->implementsInterface('ArrayAccess')
				&& $class->implementsInterface('IteratorAggregate')
				&& $class->implementsInterface('Countable');
	}
	
	/**
	 * @param string $typeName
	 * @return boolean
	 */
	static function isArrayLike(string $typeName) {
		switch ($typeName) {
			case self::ARRAY:
			case self::PSEUDO_ARRAYLIKE:
			case 'ArrayObject':
				return true;
			case self::STRING:
			case self::INT:
			case self::FLOAT:
			case self::BOOL:
			case self::ARRAY:
			case self::RESOURCE:
			case self::OBJECT:
			case self::PSEUDO_SCALAR:
			case self::PSEUDO_MIXED:
			case self::PSEUDO_ARRAYLIKE:
			case self::PSEUDO_NUMERIC:
				return false;
		}
		
		return is_subclass_of($typeName, 'ArrayAccess')
				&& is_subclass_of($typeName, 'IteratorAggregate')
				&& is_subclass_of($typeName, 'Countable');
	}
	
	/**
	 * @param string $typeName
	 * @return boolean
	 */
	static function isSafe(string $typeName) {
		switch ($typeName) {
			case self::PSEUDO_MIXED:
			case self::PSEUDO_SCALAR:
			case self::PSEUDO_NUMERIC:
			case self::PSEUDO_ARRAYLIKE:
				return false;
			default:
				return true;
		}
	}
	
	/**
	 * @param string $typeName
	 * @return bool
	 */
	static function isValid(string $typeName) {
		return (bool) preg_match('/[0-9a-zA-Z_\\\\]/', $typeName);
	}
}

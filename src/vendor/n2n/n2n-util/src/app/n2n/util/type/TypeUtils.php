<?php
namespace n2n\util\type;

use n2n\util\StringUtils;
use n2n\util\col\ArrayUtils;

class TypeUtils {
	const COMMON_MAX_CHARS = 100;
	const ENCODED_NAMESPACE_LEVEL_DEFAULT_SEPARATOR = '-';
	
	/**
	 * @param mixed $value
	 * @param int $maxChars
	 * @return string
	 */
	public static function buildUsefullValueIdentifier($value, int $maxChars = self::COMMON_MAX_CHARS) {
		if (is_scalar($value)) {
			return mb_substr($value, 0, $maxChars);
		}
		return self::getTypeInfo($value);
	}
	
	/**
	 * @param mixed $value
	 * @return string
	 */
	public static function getTypeInfo($value) {
		if (is_object($value)) {
			return get_class($value);
		}
		return gettype($value);
	}
	
	/**
	 * @param mixed $value
	 * @return string|int|bool
	 */
	public static function buildScalar($value) {
		if (is_scalar($value)) {
			return $value;
		}
		
		return self::getTypeInfo($value);
	}
	
	/**
	 * @param string $typeName
	 * @return string|null
	 */
	public static function buildTypeAcronym(string $typeName) {
		if (preg_match_all('/[A-Z0-9]+/', $typeName, $matches)) {
			return strtolower(implode('', $matches[0]));
		}
		
		return null;
	}
	
	/**
	 * @param string $typeName
	 * @return string
	 */
	public static function prettyName(string $typeName) {
		$typeName = preg_replace('/((?<=[a-z0-9])[A-Z]|(?<=.)[A-Z](?=[a-z]))/', ' ${0}', (string) $typeName);
		
		$typeName = preg_replace_callback('/_./',
				function ($treffer) {
					return ' ' . mb_strtoupper($treffer[0][1]);
				}, $typeName);
		
		$typeName = str_replace(array('[', ']'), array(' (', ')'), $typeName);
		
		return ucfirst($typeName);
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @param string $propertyName
	 * @return string
	 */
	public static function prettyClassPropName(\ReflectionClass $class, string $propertyName) {
		return self::prettyPropName($class->getName(), $propertyName);
	}
	
	/**
	 * @param string $className
	 * @param string $propertyName
	 * @return string
	 */
	public static function prettyPropName(string $className, string $propertyName) {
		if ($className instanceof \ReflectionClass) {
			$className = $className->getName();
		}
		return $className . '::$' . $propertyName;
	}
	
	/**
	 * @param \ReflectionProperty $property
	 * @return string
	 */
	public static function prettyReflPropName(\ReflectionProperty $property) {
		return self::prettyPropName($property->getDeclaringClass()->getName(), $property->getName());
	}
	
	/**
	 * @param string $className
	 * @param string $methodName
	 * @return string
	 */
	public static function prettyMethName(string $className, string $methodName) {
		return $className . '::' . $methodName . '()';
	}
	
	/**
	 * @param \ReflectionClass $class
	 * @param string $methodName
	 * @return string
	 */
	public static function prettyClassMethName(\ReflectionClass $class, string $methodName) {
		return self::prettyMethName($class->getName(), $methodName);
	}
	
	/**
	 * @param \ReflectionFunctionAbstract $method
	 * @return string
	 */
	public static function prettyReflMethName(\ReflectionFunctionAbstract $method) {
		if ($method instanceof \ReflectionMethod) {
			return self::prettyMethName($method->getDeclaringClass()->getName(), $method->getName());
		}
		
		return $method->getName() . '()';
	}
	
	/**
	 * @param string $value
	 * @param int $maxChars
	 * @return string
	 */
	public static function prettyValue($value, int $maxChars = self::COMMON_MAX_CHARS) {
		if (is_scalar($value)) {
			return StringUtils::reduce($value, $maxChars);
		}
		
		return self::getTypeInfo($value);
	}
	
	/**
	 * @param string $string
	 * @return string
	 */
	public static function stripSpecialChars($string) {
		return preg_replace('/[^0-9a-zA-Z_]/', '', $string);
	}
	
	/**
	 * @param string $namespace
	 * @param string $namespaceLevelSepartor
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public static function encodeNamespace(string $namespace, 
			string $namespaceLevelSepartor = self::ENCODED_NAMESPACE_LEVEL_DEFAULT_SEPARATOR) {
		if (self::hasSpecialChars($namespace, false)) {
			throw new \InvalidArgumentException('Invalid namespace: ' . $namespace);
		}
		
		return str_replace('\\', $namespaceLevelSepartor, trim((string) $namespace, '\\'));
	}
	
	/**
	 * @param string $encodedNamespace
	 * @param string $namespaceLevelSepartor
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public static function decodeNamespace(string $encodedNamespace, 
			string $namespaceLevelSepartor = self::ENCODED_NAMESPACE_LEVEL_DEFAULT_SEPARATOR) {
		$namespace = str_replace($namespaceLevelSepartor, '\\', trim((string) $encodedNamespace, self::ENCODED_NAMESPACE_LEVEL_DEFAULT_SEPARATOR));
		
		if (self::hasSpecialChars($namespace, false)) {
			throw new \InvalidArgumentException('Invalid namespace: ' . $namespace);
		}
		
		return $namespace;
	}
	
	/**
	 * @param string $string
	 * @return bool
	 */
	public static function hasSpecialChars(string $string, bool $treatSeparatorAsSpecial = true) {
		return preg_match('/[^0-9a-zA-Z_' . ($treatSeparatorAsSpecial ? '' : '\\\\') . ']/', $string);
	}
	
	/**
	 * @param string $namespace
	 * @return string
	 */
	public static function purifyNamespace(string $namespace) {
		return trim(str_replace('/', '\\', $namespace), '\\');
	}
	
	public static function isValueA($value, $expectedType, bool $nullAllowed): bool {
		if ($expectedType === null || ($nullAllowed && $value === null)) return true;
		
		if (is_array($expectedType)) {
			foreach ($expectedType as $type) {
				if (self::isValueA($value, $type, false)) return true;
			}
			return false;
		}
		
		if ($expectedType instanceof TypeConstraint) {
			return $expectedType->isValueValid($value);
		}
		
		if ($expectedType instanceof \ReflectionClass) {
			$expectedType = $expectedType->getName();
		}
		
		return TypeName::isValueA($value, $expectedType);
	}
		
	/**
	 * @param mixed $value
	 * @return boolean
	 */
	public static function isValueArrayLike($value) {
		return ArrayUtils::isArrayLike($value);
	}
	
	public static function isTypeA($type, $expectedType): bool {
		if ($expectedType === null) return true;
		if ($type === null) return false;
		
		switch ($type) {
			case 'scalar':
				return $expectedType == 'scalar';
			case 'array':
				return $expectedType == 'array';
			case 'string':
				return $expectedType == 'string' || $expectedType == 'scalar';
			case 'numeric':
				return $expectedType == 'numeric' || $expectedType == 'scalar';
		}
		
		if ($type instanceof \ReflectionClass) {
			$type = $type->getName();
		}
		
		if ($expectedType instanceof \ReflectionClass) {
			$expectedType = $expectedType->getName();
		}
		
		return $type == $expectedType || is_subclass_of($type, $expectedType);
	}
}
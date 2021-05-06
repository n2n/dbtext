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
namespace n2n\util\type;

use n2n\util\col\ArrayUtils;

class ArgUtils {
// 	public static function validateEnumArgument($paramNo, array $options, $bti = 1) {
// 		$lutp = N2N::getLastUserTracePoint($bti);
// 		$paramNo = (int) $paramNo;
	
// 		if (array_key_exists($paramNo, $lutp['args']) && in_array($lutp['args'][$paramNo], $options)) {
// 			return;	
// 		}
		
// 		throw new \InvalidArgumentException(SysTextUtils::get('n2n_error_reflection_invalid_enum_parameter_value', 
// 				array('method' => (isset($lutp['class']) ? $lutp['class'] . '::' : '') . $lutp['function'], 
// 						'paramNo' => $paramNo + 1, 'allowedValues' => implode(', ', $options))));
// 	}

	private static function buildExMsgStart(string $parameterName = null) {
		if ($parameterName === null) {
			return 'Passed parameter';
		}
		
		return 'Parameter ' . $parameterName; 
	}
	
	private static function buildFunctionName($object, $method) {
		if ($method instanceof \Closure) {
			$function = new \ReflectionFunction($method);
			return $function->getName();
		}
		
		if ($method instanceof \ReflectionFunction) {
			return $method->getName();
		}
		
		if ($method instanceof \ReflectionMethod) {
			if ($object === null) {
				return TypeUtils::prettyReflMethName($method);
			}
			
			$method = $method->getName();
		}
		
		if (!is_object($object) || !is_scalar($method)) {
			throw self::createNiceJobException();
		}
		
		return TypeUtils::prettyMethName(get_class($object), $method);
	}
	
	private static function prettyExpectedType($expectedType): string {
		if (!is_array($expectedType)) {
			return (string) TypeUtils::prettyValue($expectedType);
		} 
		
		$strs = array();
		foreach ($expectedType as $type) {
			$strs[] = self::prettyExpectedType($type);
		}
		return implode('|', $strs);
	}
	
	private static function createNiceJobException(): \Exception {
		return new \InvalidArgumentException('Invalid arguments passed to argument validation api. Nice job!');
	}
	
	public static function valType($param, $expectedType, bool $nullAllowed = false, string $parameterName = null) {
		if (TypeUtils::isValueA($param, $expectedType, $nullAllowed)) return;
	
		throw new \InvalidArgumentException(self::buildExMsgStart($parameterName)
				. ' must be of type ' . self::prettyExpectedType($expectedType) . ', ' 
				. TypeUtils::getTypeInfo($param) . ' given.');
	}
	
	public static function valTypeReturn($returnedValue, $expectedType, $object, $method, bool $nullAllowed = false) {
		if (TypeUtils::isValueA($returnedValue, $expectedType, $nullAllowed)) return;
	
		throw new \InvalidArgumentException(self::buildFunctionName($object, $method)
				. ' return value must be of type ' . self::prettyExpectedType($expectedType) . ', '
				. TypeUtils::getTypeInfo($returnedValue) . ' given.');
	}

	public static function valObject($param, bool $nullAllowed = false, string $parameterName = null) {
		self::valType($param, 'object', $nullAllowed, $parameterName);
	}
	
	public static function valObjectReturn($returnedValue, $object, $method, bool $nullAllowed = false) {
		self::valTypeReturn($returnedValue, 'object', $object, $method, $nullAllowed);
	}
	
	public static function valScalar($param, bool $nullAllowed = false, string $parameterName = null) {
		self::valType($param, 'scalar', $nullAllowed, $parameterName);
	}
	
	public static function valScalarReturn($returnedValue, $object, $method, bool $nullAllowed = false) {
		self::valTypeReturn($returnedValue, 'scalar', $object, $method, $nullAllowed);
	}
	
	public static function valEnum($value, array $allowedValues, array $valueLables = null, $nullAllowed = false, 
			$parameterName = null) {
		if (($value === null && $nullAllowed) || in_array($value, $allowedValues, true)) return;
		
		throw new \InvalidArgumentException(self::buildExMsgStart($parameterName) . ' contains invalid value \'' 
				. TypeUtils::buildScalar($value) . '\'. Allowed values: ' 
				. implode(', ', ($valueLables !== null ? $valueLables : $allowedValues)));
	}
	
	public static function valEnumReturn($returnedValue, array $allowedValues, $object, $method, array $valueLables = null, 
			bool $nullAllowed = false) {
		if (($returnedValue === null && $nullAllowed) || ArrayUtils::inArrayLike($returnedValue, $allowedValues)) return;

		throw new \InvalidArgumentException(self::buildFunctionName($object, $method) . ' contains invalid value \''
				. TypeUtils::buildScalar($returnedValue) . '\'. Allowed values: '
				. implode(', ', ($valueLables !== null ? $valueLables : $allowedValues)));
	}
	
	public static function valArray($value, $expectedFieldType, bool $nullAllowed = false, 
			string $parameterName = null) {
		if ($value === null && $nullAllowed) return;
		
		if (!is_array($value)) {
			throw new \InvalidArgumentException(self::buildExMsgStart($parameterName) 
					. ' must be an array. Given type: ' . TypeUtils::getTypeInfo($value));
		}
		
		try {
			self::validateFields($value, $expectedFieldType);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException(self::buildExMsgStart($parameterName) 
					. ' contains invalid array fields.', 0, $e); 
		}
	}
		
	public static function valArrayReturn($value, $object, $method, $expectedFieldType = null, bool $nullAllowed = false) {
		if ($value === null && $nullAllowed) return;
		
		if (!is_array($value)) {
			throw new \InvalidArgumentException(self::buildFunctionName($object, $method) 
					. ' return value must be array. Given type: ' . TypeUtils::getTypeInfo($value));
		}
		
		if ($expectedFieldType === null) return;
		
		try {
			self::validateFields($value, $expectedFieldType);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException(self::buildFunctionName($object, $method) 
					. ' return value contains invalid array fields.', 0, $e);
		}
	}
	
	public static function valArrayLike($value, $expectedFieldType = null, bool $nullAllowed = false, 
			string $parameterName = null) {
		if ($value === null && $nullAllowed) return;
		
		if (!ArrayUtils::isArrayLike($value)) {
			throw new \InvalidArgumentException(self::buildExMsgStart($parameterName) . ' not array like. Given type: '
					. TypeUtils::getTypeInfo($value));
		}
		
		if ($expectedFieldType === null) return;
		
		try {
			self::validateFields($value, $expectedFieldType);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException(self::buildExMsgStart($parameterName) 
					. ' contains invalid array fields.', 0, $e); 
		}
	}
		
	public static function valArrayLikeReturn($value, $object, $method, $expectedFieldType = null, bool $nullAllowed = false) {
		if ($value === null && $nullAllowed) return;
		
		if (!ArrayUtils::isArrayLike($value)) {
			throw new \InvalidArgumentException(self::buildFunctionName($object, $method) 
					. ' return value must be array like. Given type: ' . TypeUtils::getTypeInfo($value));
		}
		
		if ($expectedFieldType === null) return;
		
		try {
			self::validateFields($value, $expectedFieldType);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException(self::buildFunctionName($object, $method) 
					. ' return value contains invalid array fields.', 0, $e);
		}
	}

	private static function validateFields($value, $expectedFieldType, $nullFieldAllowed = false) {
		foreach ($value as $fieldKey => $fieldValue) {
			if (TypeUtils::isValueA($fieldValue, $expectedFieldType, $nullFieldAllowed)) continue;
			
			throw new \InvalidArgumentException('Field with key \'' . $fieldKey . '\' contains invalid type '
					.  TypeUtils::getTypeInfo($fieldValue) . '. ' . self::prettyExpectedType($expectedFieldType) 
					. ' expected.');
		}
	}
	
	public static function toArray($arg) {
		if (is_object($arg)) return array($arg);
		return (array) $arg;
	}
	
	public static function toString($arg) {
		if (is_scalar($arg) || (is_object($arg) && method_exists($arg, '__toString()'))) {
			return (string) $arg;
		}
		
		throw new \InvalidArgumentException(TypeUtils::getTypeInfo($arg) . ' passed for string argument.');
	}
	
	public static function assertTrue($arg, string $exMessage = 'Invalid argument passed.') {
		if ($arg !== true) {
			throw new \InvalidArgumentException($exMessage);
		}
	}
	
	public static function assertTrueReturn($arg, $object, $method, string $exMessage = null) {
		if ($arg === true) return;
		
		throw new \InvalidArgumentException(self::buildFunctionName($object, $method)
				. ' returned invalid value.' . ($exMessage !== null ? ' Reasion: ' . $exMessage : ''));
	}
	
// 	public static function assertTrueForParam($arg, $paramName, $expectedType, $passedValue, $bti = 1) {
// 		if ($arg === true) return;

// 		$lutp = N2N::getLastUserTracePoint($bti);

// 		throw new \InvalidArgumentException('Argument ' . $paramName . ' passed to ' 
// 				. (isset($lutp['class']) ? $lutp['class'] . '::' : '') . $lutp['function']
// 				. '() must have type ' . $expectedType . ', ' . TypeUtils::getTypeInfo($passedValue) . ' given.');
// 	}

	
	/**
	 * @param mixed $arg
	 * @return string or null if $arg is null.
	 * @throws \InvalidArgumentException
	 */
	public static function stringOrNull($arg) {
		try {
			return CastUtils::stringOrNull($arg);
		} catch (TypeCastException $e) {
			throw new \InvalidArgumentException($e->getMessage(), 0, $e);
		}
	}
	
	/**
	 * @param mixed $arg
	 * @return int or null if $arg is null.
	 * @throws \InvalidArgumentException
	 */
	public static function intOrNull($arg) {
		try {
			return CastUtils::intOrNull($arg);
		} catch (TypeCastException $e) {
			throw new \InvalidArgumentException($e->getMessage(), 0, $e);
		}
	}
	
	public static function falseToNull($arg) {
		if ($arg === false) return null;
		return $arg;
	}
}

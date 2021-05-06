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

use n2n\reflection\ReflectionUtils;
use n2n\util\col\ArrayUtils;
use n2n\util\ex\IllegalStateException;

class TypeConstraint {	
	private $typeName;
	private $allowsNull;
	private $arrayFieldTypeConstraint;
	private $whitelistTypes;
	private $convertable = false;
	/**
	 * @param string $typeName
	 * @param string $allowsNull
	 * @param TypeConstraint $arrayFieldTypeConstraint
	 * @param array $whitelistTypes
	 * @throws \InvalidArgumentException
	 */
	protected function __construct(string $typeName, bool $allowsNull, 
			TypeConstraint $arrayFieldTypeConstraint = null, array $whitelistTypes = array(), bool $convertable = false) {
		$this->typeName = $typeName;
		$this->allowsNull = $allowsNull;
		$this->arrayFieldTypeConstraint = $arrayFieldTypeConstraint;
		$this->whitelistTypes = $whitelistTypes;
		$this->setConvertable($convertable);
	}
	
	public function setWhitelistTypes(array $whitelistTypes) {
		$this->whitelistTypes = $whitelistTypes;
		return $this;
	}
	
	public function getWhitelistTypes() {
		return $this->whitelistTypes;
	}
	
	/**
	 * @return bool
	 */
	public function isConvertable() {
		return $this->convertable;
	}
	
	/**
	 * @param bool $convertable
	 * @throws IllegalStateException
	 */
	public function setConvertable(bool $convertable) {
		if ($convertable && !TypeName::isConvertable($this->typeName)) {
			throw new IllegalStateException('Values are not convertable to ' . $this->typeName);
		}
		
		$this->convertable = $convertable;
	}
	
	/**
	 * @return string
	 */
	public function getTypeName() {
		return $this->typeName;
	}
// 	/**
// 	 * @return boolean
// 	 */
// 	public function isArray() {
// 		return $this->type == 'array';
// 	}
	
	public function isArrayLike() {
		return $this->arrayFieldTypeConstraint !== null;
	}
	/**
	 * @return boolean
	 */
	public function allowsNull() {
		return $this->allowsNull;
	}
	
	/**
	 * @return boolean
	 */
	public function isTypeSafe() {
		if (!TypeName::isSafe($this->typeName)) {
			return false;
		}
		
		if (!$this->isArrayLike()) {
			return true;
		}
		
		return $this->arrayFieldTypeConstraint->isTypeSafe();
	}
	
	public function isScalar() {
		return !$this->isArrayLike() && TypeName::isScalar($this->typeName);
	}
	/**
	 * @return TypeConstraint|null
	 */
	public function getArrayFieldTypeConstraint() {
		return $this->arrayFieldTypeConstraint;
	}
	
// 	public function getWhitelistTypes() {
// 		return $this->whitelistTypes;
// 	}
	
	public function isValueValid($value) {
		foreach ($this->whitelistTypes as $whitelistType) {
			if (TypeUtils::isValueA($value, $whitelistType, false)) return true;
		}
		
		if ($value === null) {
			return $this->allowsNull();
		}
		
		if (!TypeUtils::isValueA($value, $this->typeName, false)) {
			return false;
		}
		
		if (!$this->isArrayLike()) return true;
		
		if (!ArrayUtils::isArrayLike($value)) {
			throw new IllegalStateException('Illegal constraint ' . $this->__toString() . ' defined:'
					. $this->typeName . ' is not array like.');
		}
		
		foreach ($value as $fieldValue) {
			if (!$this->arrayFieldTypeConstraint->isValueValid($fieldValue)) {
				return false;
			}
		}
		
		return true;
	}
	/**
	 * @param mixed $value
	 * @throws ValueIncompatibleWithConstraintsException
	 */
	public function validate($value) {
		foreach ($this->whitelistTypes as $whitelistType) {
			if (TypeUtils::isValueA($value, $whitelistType, false)) {
				return $value;
			}
		}
		
		if ($value === null) {
			if ($this->allowsNull()) return $value;
			
			throw new ValueIncompatibleWithConstraintsException(
					'Null not allowed with constraints.');
		}
		
		if (!TypeUtils::isValueA($value, $this->typeName, false)) {
			if (!$this->convertable) {
				throw $this->createIncompatbleValueException($value);
			}
			
			try {
				$value = TypeName::convertValue($value, $this->typeName);
			} catch (\InvalidArgumentException $e) {
				throw $this->createIncompatbleValueException($value, $e);
			}
		}
		
		if ($this->arrayFieldTypeConstraint === null) {
			return $value;
		}
		
		if (!ArrayUtils::isArrayLike($value)) {
			if ($this->typeName === null) {
				throw $this->createIncompatbleValueException($value);
			}
			
			throw new IllegalStateException('Illegal constraint ' . $this->__toString() . ' defined:'
					. $this->typeName . ' is no ArrayType.');
		}
		
		foreach ($value as $key => $fieldValue) {
			try {
				$value[$key] = $this->arrayFieldTypeConstraint->validate($fieldValue);
			} catch (ValueIncompatibleWithConstraintsException $e) {
				throw new ValueIncompatibleWithConstraintsException(
						'Value type not allowed with constraints '
						. $this->__toString() . '. Array field (key: \'' . $key . '\') contains invalid value.', null, $e);
			}
		}
		
		return $value;
	}
	
	private function createIncompatbleValueException($value, $previousE = null) {
		throw new ValueIncompatibleWithConstraintsException(
				'Value type not allowed with constraints. Required type: '
				. $this->__toString() . '; Given type: '
				. TypeUtils::getTypeInfo($value), null, $previousE);
	}
	
	public function isEmpty() {
		return $this->typeName === TypeName::PSEUDO_MIXED && $this->allowsNull 
				&& ($this->arrayFieldTypeConstraint === null || $this->arrayFieldTypeConstraint->isEmpty());
	}
	/**
	 * Returns true if all values which are compatible with the constraints of this instance are also 
	 * compatible with the passed constraints (but not necessary the other way around)
	 * @param TypeConstraint $constraints
	 * @return bool
	 */
	public function isPassableTo(TypeConstraint $constraints, $ignoreNullAllowed = false) {
		if ($constraints->isEmpty()) return true;
		 
		if (!(TypeUtils::isTypeA($this->getTypeName(), $constraints->getTypeName()) 
				&& ($ignoreNullAllowed || $constraints->allowsNull() || !$this->allowsNull()))) return false;
				
		$arrayFieldConstraints = $constraints->getArrayFieldTypeConstraint();
		if ($arrayFieldConstraints === null) return true;
		if ($this->arrayFieldTypeConstraint === null) return true;
		
		return $this->arrayFieldTypeConstraint->isPassableTo($arrayFieldConstraints, $ignoreNullAllowed);
	}
	
	public function isPassableBy(TypeConstraint $constraints, $ignoreNullAllowed = false) {
		if ($this->isEmpty()) return true;

		if (!(TypeUtils::isTypeA($constraints->getTypeName(), $this->getTypeName())
				&& ($ignoreNullAllowed || $this->allowsNull() || !$constraints->allowsNull()))) return false;
		
		if ($this->arrayFieldTypeConstraint === null) return true;
		$arrayFieldConstraints = $constraints->getArrayFieldTypeConstraint();
		if ($arrayFieldConstraints === null) return true;

		return $this->arrayFieldTypeConstraint->isPassableBy($arrayFieldConstraints, $ignoreNullAllowed);
	}
	
	public function getLenientCopy() {
		if (($this->allowsNull && $this->convertable) || $this->isArrayLike()) return $this;
				
		$convertable =  $this->convertable || TypeName::isConvertable($this->typeName);
		
		return new TypeConstraint($this->typeName, true, $this->arrayFieldTypeConstraint, 
				$this->whitelistTypes, $convertable);
	}
	
	public function __toString(): string {
		$str = '';
		
		if ($this->allowsNull) {
			$str .= '?';
		}
		
		$str .= $this->typeName;
		
		if ($this->arrayFieldTypeConstraint !== null) {
			$str .= '<' . $this->arrayFieldTypeConstraint . '>';
		}
		
		return $str;
	}
	
	
	/**
	 * @param \ReflectionParameter $parameter
	 * @return TypeConstraint
	 */
	public static function createFromParameter(\ReflectionParameter $parameter) {
		if (ReflectionUtils::isArrayParameter($parameter)) {
			return new TypeConstraint(TypeName::ARRAY, $parameter->allowsNull(),
					new TypeConstraint(TypeName::PSEUDO_MIXED, true));
		}
		
		$class = ReflectionUtils::extractParameterClass($parameter);
		if ($class !== null && TypeName::isClassArrayLike($class)) {
			return new TypeConstraint($class->getName(), $parameter->allowsNull(),
					new TypeConstraint(TypeName::PSEUDO_MIXED, true));
		}
		
		$typeName = null;
		if (null !== ($type = $parameter->getType())) {
			$typeName = $type->getName();	
		}
		return new TypeConstraint($typeName ?? TypeName::PSEUDO_MIXED, $parameter->allowsNull());
	}
	
	private static function buildTypeName($type) {
		if ($type instanceof \ReflectionClass) {
			return $type->getName();
		}
		
		if ($type === null) {
			return TypeName::PSEUDO_MIXED;
		}
		
		if (!is_scalar($type)) {
			ArgUtils::valType($type, [TypeName::PSEUDO_SCALAR, \ReflectionClass::class], false, 'type');
			throw new IllegalStateException();
		}
		
		if (TypeName::isValid($type)) {
			return $type;
		}
		
		throw new \InvalidArgumentException('Type name contains invalid characters: ' . $type);
		
// 		throw new \InvalidArgumentException(
// 				'Invalid type parameter passed for TypeConstraint (Allowed: string, ReflectionClass): ' 
// 						. TypeUtils::getTypeInfo($type));
	}
	
	private static function createFromExpresion(string $type) {
		$matches = null;
		if (!preg_match('/^(\\?)?([^<>]+)(<(.+)>)?$/', $type, $matches)) {
			throw new \InvalidArgumentException('Invalid TypeConstraint expression: ' . $type);
		}
		
		$typeName = $matches[2];
		
		if (!TypeName::isValid($typeName)) {
			throw new \InvalidArgumentException($type . ' is an invalid TypeConstraint expression. Reason: '
					. $typeName . ' contains invalid characters.');
		}
		
		$allowsNull = $matches[1] == '?';
				
		$arrayFieldTypeConstraint = null;
		if (isset($matches[4])) {
			if (!TypeName::isArrayLike($typeName)) {
				throw new \InvalidArgumentException('Array field generics disabled for ' . $type . '. Reason '
						. $typeName . ' is not arraylike.');
			}
			
			try {
				$arrayFieldTypeConstraint = self::create($matches[4]);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException($type . ' is an invalid TypeConstraint expression. Reason: '
						. $e->getMessage(), 0, $e);
			}
		}
		
		
		return new TypeConstraint($typeName, $allowsNull, $arrayFieldTypeConstraint);
	}
	
	/**
	 * @param string|\ReflectionClass|TypeConstraint|null $type
	 * @return \n2n\util\type\TypeConstraint
	 */
	public static function create($type) {
		if ($type instanceof TypeConstraint) {
			return $type;
		}
		
		if ($type instanceof \ReflectionClass) {
			return self::createSimple($type);
		}
		
		if (!is_scalar($type)) {
			ArgUtils::valType($type, ['scalar', \ReflectionClass::class, TypeConstraint::class]);
			throw new IllegalStateException();
		}
		
		return self::createFromExpresion($type);
	}
	
	/**
	 * @param string|\ReflectionClass|TypeConstraint|null $type
	 * @return \n2n\util\type\TypeConstraint
	 */
	public static function build($type) {
		if ($type === null) {
			return null;
		}
		
		return self::create($type);
	}
	
	/**
	 * @param string|\ReflectionClass|TypeConstraint|null $type
	 * @param bool $allowsNull
	 * @param array $whitelistTypes
	 * @return \n2n\util\type\TypeConstraint
	 */
	public static function createSimple($type, bool $allowsNull = true, bool $convertable = false, 
			array $whitelistTypes = array()) {
		$typeName = self::buildTypeName($type);
		
		if (TypeName::isArrayLike($typeName)) {
			return new TypeConstraint($typeName, $allowsNull, TypeConstraints::mixed(true),
					$whitelistTypes, $convertable);
		}
		
		return new TypeConstraint($typeName, $allowsNull, null, $whitelistTypes, $convertable);
	}
	
	/**
	 * @param string|\ReflectionClass|TypeConstraint|null $type
	 * @param bool $allowsNull
	 * @param string|\ReflectionClass|TypeConstraint|null $arrayFieldType
	 * @param array $whitelistTypes
	 * @return \n2n\util\type\TypeConstraint
	 */
	public static function createArrayLike($type, bool $allowsNull = true, $arrayFieldType = null, 
			array $whitelistTypes = array()) {
		$typeName = null;
		if ($type === null) {
			$typeName = TypeName::PSEUDO_ARRAYLIKE;
		} else {
			$typeName = self::buildTypeName($type);
		}
		
		if (!TypeName::isArrayLike($typeName)) {
			throw new \InvalidArgumentException('Type ' . $typeName . ' is not arraylike.');
		}
				
		return new TypeConstraint($typeName, $allowsNull, 
				($arrayFieldType === null ? TypeConstraints::mixed(true) : self::create($arrayFieldType)), 
				$whitelistTypes);
	}
	
	
}

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
namespace n2n\reflection\property;

use n2n\reflection\ReflectionUtils;
use n2n\util\type\ArgUtils;
use n2n\util\type\TypeUtils;
use n2n\util\type\TypeConstraint;
use n2n\util\type\ValueIncompatibleWithConstraintsException;

class PropertyAccessProxy implements AccessProxy {
	private $propertyName;
	private $property;
	private $setterMethod;
	private $getterMethod;
	private $forcePropertyAccess;
	private $baseConstraint;
	private $constraint;
	private $nullReturnAllowed = false;

	public function __construct($propertyName, \ReflectionProperty $property = null, 
			\ReflectionMethod $getterMethod = null, \ReflectionMethod $setterMethod = null) {
		$this->propertyName = $propertyName;
		$this->property = $property;
		$this->getterMethod = $getterMethod;
		$this->setterMethod = $setterMethod;

		if ($setterMethod === null) {
			$this->constraint = $this->baseConstraint = TypeConstraint::createSimple(null);
		} else {
			$parameter = current($setterMethod->getParameters());
			$this->constraint = $this->baseConstraint = TypeConstraint::createFromParameter($parameter);
		}
	}
	
	public function getBaseConstraint() {
		return $this->baseConstraint;
	}
	
	public function isNullPossible() {
		return $this->baseConstraint->allowsNull();
	}
	
	public function getPropertyName(): string {
		return $this->propertyName;
	}

	public function getProperty() {
		return $this->property;
	}
	
	public function isReadable() {
		return (isset($this->property) && $this->property->isPublic()) || isset($this->getterMethod);
	}
	
	public function isWritable() {
		return (isset($this->property) && $this->property->isPublic()) || isset($this->setterMethod);
	}
	
	public function isNullReturnAllowed() {
		return $this->nullReturnAllowed;
	}
	
	public function setNullReturnAllowed($nullReturnAllowed) {
		$this->nullReturnAllowed = $nullReturnAllowed;
	}

	public function getSetterMethod() {
		return $this->setterMethod;
	}

	public function getGetterMethod() {
		return $this->getterMethod;
	}
	/**
	 *
	 * @return \n2n\util\type\TypeConstraint
	 */
	public function getConstraint(): TypeConstraint {
		return $this->constraint;
	}

	public function setConstraint(TypeConstraint $constraints) {
		if ($constraints->isPassableTo($this->baseConstraint)) {
			$this->constraint = $constraints;
			return;
		}

		if (null === $this->setterMethod) {
			throw new ConstraintsConflictException('Constraints conflict for property ' 
					. $this->property->getDeclaringClass()->getName() . '::$' 
					. $this->property->getName() . '. Constraints ' . $constraints->__toString() 
					. ' are not compatible with ' . $this->baseConstraint->__toString());
		} else {
			throw new ConstraintsConflictException('Constraints conflict for setter-method ' 
							. $this->setterMethod->getDeclaringClass()->getName() . '::' 
							. $this->setterMethod->getName() . '(). Constraints ' . $constraints->__toString() 
							. ' are not compatible with ' . $this->baseConstraint->__toString(),
					0, null, $this->setterMethod);
		}
		
	}

	public function setForcePropertyAccess($forcePropertyAccess) {
		$this->property->setAccessible((boolean) $forcePropertyAccess);
		$this->forcePropertyAccess = (boolean) $forcePropertyAccess;
	}

	public function isPropertyAccessSetterMode() {
		return $this->forcePropertyAccess || null === $this->setterMethod;
	}
	
	public function isPropertyAccessGetterMode() {
		return $this->forcePropertyAccess || null === $this->getterMethod;
	}

	private function createConstraintsConflictException(TypeConstraint $invalidConstraints) {
		
	}

	private function createPassedValueException(ValueIncompatibleWithConstraintsException $e) {
		if ($this->isPropertyAccessSetterMode()) {
			return new PropertyValueTypeMissmatchException('Passed value for ' 
					. $this->property->getDeclaringClass()->getName() . '::$' . $this->property->getName() 
					. ' is incompatible with constraints.', 0, $e);
		} else {
			return new PropertyValueTypeMissmatchException('Passed value for ' 
					. $this->setterMethod->getDeclaringClass()->getName() . '::' . $this->setterMethod->getName() 
					. '() is disallowed for property setter method.', 0, $e);
		}
	}
	
	private function createReturnedValueException(ValueIncompatibleWithConstraintsException $e) {
		if ($this->isPropertyAccessGetterMode()) {
			return new PropertyValueTypeMissmatchException('Property ' 
					. $this->property->getDeclaringClass()->getName() . '::$' 
					. $this->property->getName() . ' contains unexpected type.', 0, $e);
		} else {
			return new PropertyValueTypeMissmatchException('Getter method ' 
					. $this->getterMethod->getDeclaringClass()->getName() . '::' 
					. $this->getterMethod->getName() . '()  returns unexpected type', 0, $e);
		}
	}

	public function setValue(object $object, $value, bool $validate = true) {
		if (isset($this->constraint) && $validate) {
			try {
				$value = $this->constraint->validate($value);
			} catch (ValueIncompatibleWithConstraintsException $e) {
				throw $this->createPassedValueException($e);
			}
		}

		if ($this->isPropertyAccessSetterMode()) {
			try {
				$this->property->setValue($object, $value);
			} catch (\ReflectionException $e) {
				throw new PropertyAccessException('Could not set value for property '
						. $this->property->getDeclaringClass()->getName() . '::$' 
						. $this->property->getName(), 0, $e);
			}
				
			return;
		}

		$setterMethod = $this->findMethod($object, $this->setterMethod);
		try {
			$setterMethod->invoke($object, $value);
		} catch (\ReflectionException $e) {
			throw $this->createMethodInvokeException($setterMethod, $e);
		}				
	}

	public function getValue(object $object) {
		$value = null;

		if ($this->isPropertyAccessGetterMode()) {			
			try {
				$value = $this->property->getValue($object);
			} catch (\ReflectionException $e) {
				throw new PropertyAccessException('Could not get value of property '
								.  $this->property->getDeclaringClass()->getName() . '::$' 
						. $this->property->getName() . ' (Read from object type ' . get_class($object) . ')', 0, $e);
			}
		} else {
			$getterMethod = $this->findMethod($object, $this->getterMethod);
			try {
				$value = $getterMethod->invoke($object);
			} catch (\ReflectionException $e) {
				throw $this->createMethodInvokeException($getterMethod, $e, $object);
			}
		}
		
		if ($value === null && $this->nullReturnAllowed) return $value;
		
		try {
			$value = $this->constraint->validate($value);
		} catch (ValueIncompatibleWithConstraintsException $e) {
			throw $this->createReturnedValueException($e);
		}
		
		return $value;
	}
	
	private function findMethod($object, \ReflectionMethod $method) {
		$declaringClass = $method->getDeclaringClass();
		if (get_class($object) == $declaringClass->getName()) {
			return $method;
		}
	
		$objectClass = new \ReflectionClass($object);
		if (!ReflectionUtils::isClassA($objectClass, $declaringClass)) {
			return $method;
		}
	
		return $objectClass->getMethod($method->getName());
	}
	
	public function createMethodInvokeException(\ReflectionMethod $method, \Exception $previous, $object = null) {
		$message = 'Reflection execution of ' . TypeUtils::prettyReflMethName($method). ' failed.';
				
		if ($object !== null && !ReflectionUtils::isObjectA($object, $method->getDeclaringClass())) {
			$message .= ' Reason: Type of ' . get_class($object) . ' passed as object, type of ' 
					. $method->getDeclaringClass()->getName() . ' expected.';
		}
		
		throw new PropertyAccessException($message, 0, $previous);
	}
	
	public function __toString(): string {
		if ($this->isPropertyAccessGetterMode() && $this->isPropertyAccessSetterMode()) {
			return 'AccessProxy [' . ($this->property !== null ? TypeUtils::prettyReflPropName($this->property) 
					: TypeUtils::prettyPropName('<unknown class>', $this->propertyName) . ']');
		}
		
		$strs = array();
		if ($this->getterMethod !== null) {
			$strs[] = TypeUtils::prettyReflMethName($this->getterMethod);
		}
		if ($this->setterMethod !== null) {
			$strs[] = TypeUtils::prettyReflMethName($this->setterMethod);
		}
		
		return 'AccessProxy [' . implode(', ', $strs) . ']';
	}
}

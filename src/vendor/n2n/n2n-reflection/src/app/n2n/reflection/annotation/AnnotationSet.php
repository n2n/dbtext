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
namespace n2n\reflection\annotation;

use n2n\util\type\ArgUtils;

class AnnotationSet {
	private $class;
	private $method;
	private $classAnnotations;
	private $methodAnnotations;
	private $propertyAnnotations;
	/**
	 * 
	 */
	public function __construct(/*\ReflectionMethod $method*/) {
// 		$this->class = $method->getDeclaringClass();
// 		$this->method = $method;
		$this->classAnnotations = array();
		$this->methodAnnotations = array();
		$this->propertyAnnotations = array();
	}
	/**
	 * @return boolean
	 */
	public function isEmpty() {
		return empty($this->classAnnotations) && empty($this->methodAnnotations)
				&& empty($this->propertyAnnotations);
	}
	/**
	 * @param ClassAnnotation $classAnnotation
	 * @throws \InvalidArgumentException
	 */
	public function annotateClass(ClassAnnotation $classAnnotation) {
		$annotationName = get_class($classAnnotation);
		if (isset($this->classAnnotations[$annotationName])) {
			throw new \InvalidArgumentException('Duplicated annotation ' . $annotationName . ' for class ' 
					. $classAnnotation->getAnnotatedClass()->getName());
		}
		$this->classAnnotations[$annotationName] = $classAnnotation;
	}
	/**
	 * @param string $annotationName
	 * @return boolean
	 */
	public function hasClassAnnotation($annotationName) {
		return isset($this->classAnnotations[$annotationName]);
	}
	/**
	 * @param string $annotationName
	 * @return ClassAnnotation
	 */
	public function getClassAnnotation($annotationName) {
		if (isset($this->classAnnotations[$annotationName])) {
			return $this->classAnnotations[$annotationName];
		}
		return null;
	}
	/**
	 * @return ClassAnnotation[]
	 */
	public function getClassAnnotations() {
		return $this->classAnnotations;
	}
	
	public function annotateProperty(PropertyAnnotation $propertyAnnotation) {
		$property = $propertyAnnotation->getAnnotatedProperty();
		ArgUtils::assertTrue($property instanceof \ReflectionProperty);
		$annotationName = get_class($propertyAnnotation);
		
		if (!isset($this->propertyAnnotations[$annotationName])) {
			$this->propertyAnnotations[$annotationName] = array();
		}
		
		if (isset($this->propertyAnnotations[$annotationName][$property->getName()])) {
			throw new \InvalidArgumentException('Duplicated annotation ' . $annotationName . ' for property '
					. $property->getDeclaringClass()->getName() . '::$' . $property->getName());
		}
		
		$this->propertyAnnotations[$annotationName][$property->getName()] = $propertyAnnotation;
	}
	/**
	 * @param string $propertyName
	 * @param string $annotationName
	 * @return boolean
	 */
	public function hasPropertyAnnotation($propertyName, $annotationName) {
		return isset($this->propertyAnnotations[$annotationName][$propertyName]);
	}
	/**
	 * @param string $propertyName
	 * @param string $annotationName
	 * @return PropertyAnnotation
	 */
	public function getPropertyAnnotation($propertyName, $annotationName) {
		if ($this->hasPropertyAnnotation($propertyName, $annotationName)) {
			return $this->propertyAnnotations[$annotationName][$propertyName];
		}
		return null;
	}
	/**
	 * @param string $name
	 * @return boolean
	 */
	public function containsPropertyAnnotationName($name) {
		return isset($this->propertyAnnotations[$name]);
	}
	/**
	 * @param string $name
	 * 
	 */
	public function getPropertyAnnotationsByName($name) {
		if (isset($this->propertyAnnotations[$name])) {
			return $this->propertyAnnotations[$name];
		}
		return array();
	}

	/**
	 * @return PropertyAnnotation[] 
	 */
	public function getAllPropertyAnnotations() {
		$annotations = array();
		foreach ($this->propertyAnnotations as $groupedAnnotations) {
			foreach ($groupedAnnotations as $annotation) {
				$annotations[] = $annotation;
			}
		}
		return $annotations;
	}
	/**
	 * @param MethodAnnotation $methodAnnotation
	 * @throws \InvalidArgumentException
	 */
	public function annotateMethod(MethodAnnotation $methodAnnotation) {
		$method = $methodAnnotation->getAnnotatedMethod();
		ArgUtils::assertTrue($method instanceof \ReflectionMethod);
		$annotationName = get_class($methodAnnotation);
		
		if (!isset($this->methodAnnotations[$annotationName])) {
			$this->methodAnnotations[$annotationName] = array();
		}
		
		if (isset($this->methodAnnotations[$annotationName][$method->getName()])) {
			throw new \InvalidArgumentException('Duplicated annotation ' . $annotationName . ' for method ' 
					. $method->getDeclaringClass()->getName() . '::' . $method->getName() . '()');
		}
		
		$this->methodAnnotations[$annotationName][$method->getName()] = $methodAnnotation;
	}
	/**
	 * @param string $methodName
	 * @param string $annotationName
	 * @return boolean
	 */
	public function hasMethodAnnotation($methodName, $annotationName) {
		return isset($this->methodAnnotations[$annotationName]) 
				&& isset($this->methodAnnotations[$annotationName][$methodName]);
	}
	/**
	 * @param string $methodName
	 * @param string $annotationName
	 * @return boolean
	 */
	public function getMethodAnnotation($methodName, $annotationName) {
		if ($this->hasMethodAnnotation($methodName, $annotationName)) {
			return $this->methodAnnotations[$annotationName][$methodName];
		}
		return null;
	}
	/**
	 * @param string $name
	 * @return boolean
	 */
	public function containsMethodAnnotationName($name) {
		return isset($this->methodAnnotations[$name]);
	}
	/**
	 * @param string $name
	 * @return MethodAnnotation[]
	 */
	public function getMethodAnnotationsByName($name) {
		if (isset($this->methodAnnotations[$name])) {
			return $this->methodAnnotations[$name];
		}
		return array();
	}
	/**
	 * @return MethodAnnotation[]
	 */
	public function getAllMethodAnnotations() {
		$annotations = array();
		foreach ($this->methodAnnotations as $groupedAnnotations) {
			foreach ($groupedAnnotations as $annotation) {
				$annotations[] = $annotation;
			}
		}
		return $annotations;
	}
}

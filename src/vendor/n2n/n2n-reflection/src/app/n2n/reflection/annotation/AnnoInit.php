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
use n2n\reflection\ReflectionErrorException;

class AnnoInit {
	private $class;
	private $annotationSet;
	
	public function __construct(\ReflectionClass $class, AnnotationSet $annotationSet) {
		$this->class = $class;
		$this->annotationSet = $annotationSet;
	}
	
	private function trace(&$fileName, &$line, $pos = 1) {
		$tps = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $pos + 1);
		$fileName = $tps[$pos]['file'];
		$line = $tps[$pos]['line'];
	}
	
	public function c(ClassAnnotation $classAnnotation, ClassAnnotation $classAnnotation2 = null, 
			ClassAnnotation $classAnnotation3 = null) {
		$classAnnotations = func_get_args();
		if (count($classAnnotations) > 3) {
			ArgUtils::valArray($classAnnotations, 
					'n2n\reflection\annotation\ClassAnnotation');
		}
		$fileName = null;
		$line = null;
		$this->trace($fileName, $line);
		foreach ($classAnnotations as $classAnnotation) {
			$classAnnotation->setFileName($fileName);
			$classAnnotation->setLine($line);
			$classAnnotation->setAnnotatedClass	($this->class);
			
			$this->annotationSet->annotateClass($classAnnotation);
		}
	}
	
	public function p($propertyName, PropertyAnnotation $propertyAnnotation, 
			PropertyAnnotation $propertyAnnotation2 = null, PropertyAnnotation $propertyAnnotation3 = null) {
		$propertyAnnotations = func_get_args();
		array_shift($propertyAnnotations);
		if (count($propertyAnnotations) > 3) {
			ArgUtils::valArray($propertyAnnotations, 
					'n2n\reflection\annotation\PropertyAnnotation');
		}
		
		$property = $this->getProperty($propertyName);
		$fileName = null;
		$line = null;
		$this->trace($fileName, $line);
		
		foreach ($propertyAnnotations as $propertyAnnotation) {
			$propertyAnnotation->setFileName($fileName);
			$propertyAnnotation->setLine($line);
			$propertyAnnotation->setAnnotatedProperty($property);
			
			$this->annotationSet->annotateProperty($propertyAnnotation);
			
			
		}
	}
	
	private function getProperty($propertyName) {
		$property = null;
		$propertyE = null;
		try {
			$property = $this->class->getProperty($propertyName);
		} catch (\ReflectionException $e) {
			$propertyE = $e;
		}
		
		if ($property === null || $property->getDeclaringClass()->getName() != $this->class->getName()) {
			throw new \InvalidArgumentException('Annotated property not found: ' 
					. $this->class->getName() . '::$' . $propertyName, 0, $propertyE);
		}
		
		return $property;
	}
	
	public function m($methodName, MethodAnnotation $methodAnnotation, 
			MethodAnnotation $methodAnnotation2 = null, MethodAnnotation $methodAnnotation3 = null) {
		$methodAnnotations = func_get_args();
		array_shift($methodAnnotations);
		if (count($methodAnnotations) > 3) {
			ArgUtils::valArray($methodAnnotations, 
					'n2n\reflection\annotation\MethodAnnotation');
		}
		
		$method = $this->getMethod($methodName);
		$fileName = null;
		$line = null;
		$this->trace($fileName, $line);
		
		foreach ($methodAnnotations as $methodAnnotation) {
			$methodAnnotation->setFileName($fileName);
			$methodAnnotation->setLine($line);
			$methodAnnotation->setAnnotatedMethod($method);
			
			$this->annotationSet->annotateMethod($methodAnnotation);
		}
	}
	
	private function getMethod($methodName) {
		$method = null;
		$methodE = null;
		try {
			$method = $this->class->getMethod($methodName);
		} catch (\ReflectionException $e) {
			$methodE = $e;
		}
	
		if ($method === null || $method->getDeclaringClass()->getName() != $this->class->getName()) {
			$fileName = null;
			$line = null;
			$this->trace($fileName, $line, 2);
			
			throw new ReflectionErrorException('Annotated method not found: ' . $methodName,
					$fileName, $line, null, null, $methodE);
		}
	
		return $method;
	}
}

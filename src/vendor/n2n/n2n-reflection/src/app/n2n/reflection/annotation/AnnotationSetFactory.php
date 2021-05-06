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

use n2n\reflection\ReflectionErrorException;
use n2n\reflection\ReflectionUtils;

class AnnotationSetFactory {
	const ANNOTATION_METHOD = '_annos';
	/**
	 * @param \ReflectionClass $class
	 * @throws ReflectionErrorException
	 * @return \n2n\reflection\annotation\AnnotationSet
	 */
	public static function create(\ReflectionClass $class) {
		$annotationSet = new AnnotationSet();
		if (!$class->hasMethod(self::ANNOTATION_METHOD)) return $annotationSet;
		
		$method = $class->getMethod(self::ANNOTATION_METHOD);
		if ($class != $method->getDeclaringClass()) {
			return $annotationSet;
		}
		
		$parameters = $method->getParameters();
		$parameter = current($parameters);
		
		if (!$method->isStatic() || !$method->isPrivate() || $method->isAbstract() 
				|| 1 != sizeof($parameters) || is_null(ReflectionUtils::extractParameterClass($parameter)) 
				|| ReflectionUtils::extractParameterClass($parameter)->getName() != 'n2n\reflection\annotation\AnnoInit') {
			throw new ReflectionErrorException('Annotations method signature must match: ' 
					. 'private static function ' . self::ANNOTATION_METHOD 
					. '(n2n\reflection\annotation\AnnoInit);', $method->getFileName(), $method->getStartLine());
		}
		
		$method->setAccessible(true);
		try {
			$method->invoke(null, new AnnoInit($class, $annotationSet));
		} catch (\InvalidArgumentException $e) {
			throw self::decorateException($method, $e);
		}
		return $annotationSet;
	}
	
	private static function decorateException(\ReflectionMethod $method, \Exception $e) {
		foreach ($e->getTrace() as $traceMap) {
			if ($traceMap['file'] == $method->getDeclaringClass()->getFileName() && $traceMap['line'] > $method->getStartLine()
					&& $traceMap['line'] < $method->getEndLine()) {
				return new ReflectionErrorException('Misconfigured annotation', $traceMap['file'], 
						$traceMap['line'], null, null, $e);
			}
		}
		return new ReflectionErrorException('Misconfigured annotation', $traceMap['file'], null, 
				$this->method->getStartLine(), $this->method->getEndLine(), $e);
	}
}

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
namespace n2n\reflection\magic;

use n2n\reflection\ReflectionUtils;
use n2n\reflection\ReflectionErrorException;
use n2n\util\magic\MagicContext;
use n2n\util\type\TypeUtils;

class MagicUtils {
	const MAGIC_INIT_METHOD = '_init';
	/**
	 * @param object $object
	 * @param MagicContext $magicContext
	 */
	public static function init($object, MagicContext $magicContext = null) {
		self::callMethodHierarchy(new \ReflectionClass($object), $object, 
				self::MAGIC_INIT_METHOD, false, $magicContext);
	}
	/**
	 *
	 * @param \ReflectionClass $class
	 * @param object $object
	 * @param string $methodName
	 * @param bool $oneRequired
	 * @param MagicContext $magicContext
	 * @throws ReflectionErrorException
	 */
	public static function callMethodHierarchy(\ReflectionClass $class, $object, string $methodName, 
			bool $oneRequired, MagicContext $magicContext = null) {
		$methods = ReflectionUtils::extractMethodHierarchy($class, $methodName);
		if ($oneRequired && !sizeof($methods)) {
			throw new ReflectionErrorException('Magic method missing: ' . $class->getName() . '::' 
					. $methodName . '()', $class->getFileName(), $class->getStartLine());
		}
	
		$methodInvoker = new MagicMethodInvoker($magicContext);
				
		foreach ($methods as $method) {
			self::validateMagicMethodSignature($method);
				
			$method->setAccessible(true);
			try {
				$methodInvoker->invoke($object, $method);
			} catch (CanNotFillParameterException $e) {
				throw new ReflectionErrorException($e->getMessage(),
						$method->getDeclaringClass()->getFileName(), $method->getStartLine(), null, null, $e);
			}
		}
	}

	/**
	 *
	 * @param \ReflectionMethod $method
	 * @throws ReflectionErrorException
	 */
	public static function validateMagicMethodSignature(\ReflectionMethod $method) {
		if (!$method->isPrivate() || $method->isStatic() || $method->isAbstract()) {
			throw new ReflectionErrorException('Invalid magic method signature of method ' 
							. TypeUtils::prettyReflMethName($method) 
							. '. Required signature: private function ' . $method->getName() . '();',
					$method->getFileName(), $method->getStartLine());
		}
	}
	
	// 	public static function callMethod($object, \ReflectionMethod $method, TypeConstraint $returnConstraints = null) {
	// 		$methodInvoker = new MagicMethodInvoker($method);
	// 		$value = $methodInvoker->invoke($object);
	
	// 		if (!isset($returnConstraints)) return $value;
	// 		$this->checkReturnValue($method, $value, $returnConstraints);
	// 		return $value;
	// 	}
	
	/**
	 *
	 * @param \Closure $closure
	 * @param MagicContext $magicContext
	 * @throws ReflectionErrorException
	 */
	public function callMagicClosure(\Closure $closure, MagicContext $magicContext = null) {
		$magicMethodInvoker = new MagicMethodInvoker($this);
		return $magicMethodInvoker->invoke(null, new \ReflectionFunction($closure));
	}

}

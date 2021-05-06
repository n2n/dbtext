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
namespace n2n\persistence\orm\criteria\item;

use n2n\persistence\orm\query\from\TreePath;
use n2n\persistence\orm\property\EntityProperty;
use n2n\util\type\TypeUtils;

class CrIt {
	/**
	 * @param mixed $propertyExpression
	 * @throws \InvalidArgumentException
	 * @return \n2n\persistence\orm\criteria\item\CriteriaProperty
	 */
	public static function pLenient($propertyExpression): CriteriaItem {
		if ($propertyExpression instanceof CriteriaItem) {
			return $propertyExpression;
		}
		
		return self::p($propertyExpression);
	}
	/**
	 * @param mixed $arg
	 * @throws \InvalidArgumentException
	 * @return \n2n\persistence\orm\criteria\item\CriteriaProperty
	 */
	public static function p(...$args): CriteriaProperty {
		if (empty($args)) {
			return new CriteriaProperty(array());
		}
		
		$crieriaProperty = null;
		
		foreach ($args as $arg) {
			if ($crieriaProperty === null) {
				$crieriaProperty = self::singleP($arg);
			} else {
				$crieriaProperty = $crieriaProperty->ext(self::singleP($arg));
			}
		}
		
		return $crieriaProperty;
	}
	
	private static function singleP($arg): CriteriaProperty {
		if ($arg instanceof CriteriaProperty) {
			return $arg;
		}
			
		if ($arg instanceof EntityProperty) {
			return self::fromEntityProperty($arg);
		}
			
		if (is_array($arg)) {
			return new CriteriaProperty($arg);
		}
			
		if (is_scalar($arg) && null !== ($item = self::testExpressionForProperty($arg))) {
			return $item;
		}
			
		throw new \InvalidArgumentException('Invalid property expression: ' . TypeUtils::prettyValue($arg));
	}
	/**
	 * @param string $functionName
	 * @param string $paramExpression
	 * @param string $paramExpression2
	 * @return \n2n\persistence\orm\criteria\item\CriteriaFunction
	 */
	public static function f(string $functionName, ...$paramExpressions) {
		$paramItems = array();
		foreach ($paramExpressions as $paramExpression) {
			$paramItems[] = self::pfLenient($paramExpression);
		}
		
		return new CriteriaFunction($functionName, $paramItems);
	}
	
	public static function cLenient($value) {
		if ($value instanceof CriteriaItem) return $value;
		
		return new CriteriaConstant($value);
	}
	/**
	 * @param mixed $value
	 * @return \n2n\persistence\orm\criteria\item\CriteriaItem
	 */
	public static function c($value) {
		if ($value instanceof CriteriaConstant) return $value;
		
		return new CriteriaConstant($value);
	}
	
	public static function pfLenient($expression) {
		if ($expression instanceof CriteriaItem) return $expression;

		return self::pf($expression);
	}
	/**
	 * @param string $expression
	 * @throws \InvalidArgumentException
	 * @return \n2n\persistence\orm\criteria\item\CriteriaItem
	 */
	public static function pf($expression) {
		if ($expression instanceof CriteriaProperty || $expression instanceof CriteriaFunction) {
			return $expression;
		}
		
		if (is_scalar($expression)) {
			if (null !== ($item = self::testExpressionForFunction($expression))) {
				return $item;
			}
			
			if (null !== ($item = self::testExpressionForProperty($expression))) {
				return $item;
			}
		}
		
		if (is_array($expression)) {
			return self::p(...$expression);
		}
		
		throw new \InvalidArgumentException('Invalid property or function expression: ' 
				. TypeUtils::prettyValue($expression));
	}
	
	/**
	 * @param string $expression
	 * @throws \InvalidArgumentException
	 * @return \n2n\persistence\orm\criteria\item\CriteriaItem
	 */
	public static function pfc($expression) {
		if ($expression instanceof CriteriaItem || $expression instanceof CriteriaFunction) {
			return $expression;
		}
		
		if (is_numeric($expression)) {
			return new CriteriaConstant($expression);
		}
	
		if (null !== ($item = self::testExpressionForFunction($expression))) {
			return $item;
		}
	
		if (null !== ($item = self::testExpressionForProperty($expression))) {
			return $item;
		}
	
		throw new \InvalidArgumentException('Invalid CriteriaItem expression: '
				. TypeUtils::prettyValue($expression));
	}
	
// 	public static function testEpressionForConstant($expression) {
// 		if (preg_match('/^(\"|\\\')(.*)(\"|\\\')$/', (string) $expression, $matches)) {
// 			return new CriteriaConstant($matches[2]);
// 		}
// 	}

	/**
	 * @param string $expression
	 * @return \n2n\persistence\orm\criteria\item\CriteriaFunction
	 */
	public static function testExpressionForFunction(string $expression) {
		$matches = null;
		if (preg_match('/^(\w+)\((.+)?\)$/', $expression, $matches)) {
			return new CriteriaFunction($matches[1], (isset($matches[2])
					? array_map(function ($expr) { return self::pfc($expr); }, explode(',', $matches[2]))
					: array()));
		}
		
		return null;
	}
	
	/**
	 * @param string $expression
	 * @return \n2n\persistence\orm\criteria\item\CriteriaProperty
	 */
	public static function testExpressionForProperty(string $expression) {
		if (!preg_match('/(\"|\\\'|\(|\))/', $expression)) {
			return new CriteriaProperty(explode(TreePath::PROPERTY_NAME_SEPARATOR, $expression));
		}
		
		return null;
	}
	
	public static function fromEntityProperty(EntityProperty $entityProperty): CriteriaProperty {
		$propertyNames = array();
		do {
			array_unshift($propertyNames, $entityProperty->getName());
		} while (null !== ($entityProperty = $entityProperty->getParent()));
		
		return new CriteriaProperty($propertyNames);
	}
}

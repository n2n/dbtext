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
namespace n2n\reflection;

use n2n\reflection\annotation\AnnotationSetFactory;

class ReflectionContext {
	private static $annotationSets = array();
	private static $annotationAccessProxies = array();
	/**
	 * @param \ReflectionClass $class
	 * @return \n2n\reflection\annotation\AnnotationSet
	 */
	public static function getAnnotationSet(\ReflectionClass $class) {
		$className = $class->getName();
		if (!isset(self::$annotationSets[$className])) {
			self::$annotationSets[$className] = AnnotationSetFactory::create($class);
		}
		return self::$annotationSets[$className];
	}
	
	public static function getLastUserTracePoint($back) {
		foreach(debug_backtrace(false) as $key => $tracePoint) {
			if (!$key) continue;
	
			if ($back-- > 0) continue;
				
			return $tracePoint;
		}
		return null;
	}
	
	
	
// 	public static function getLastUserTracePointOfClass($className, $minBack = 1) {
// 		$back = (int) $minBack;
// 		foreach(debug_backtrace(false) as $key => $tracePoint) {
// 			if (!$key || !isset($tracePoint['file'])) continue;
	
// 			if ($back-- > 0) continue;
				
// 			if (isset($tracePoint['class']) && $tracePoint['class'] == $className) {
// 				return $tracePoint;
// 			}
// 		}
// 		return null;
// 	}
	
	public static function getLastUserTracePointOutOfClass($className, $minBack = 1) {
		$back = (int) $minBack;
		foreach(debug_backtrace(false) as $key => $tracePoint) {
			if (!$key || !isset($tracePoint['file'])) continue;
	
			if ($back-- > 0) continue;
	
			if (!isset($tracePoint['class']) || $tracePoint['class'] != $className) {
				return $tracePoint;
			}
		}
		return null;
	}
	
	public static function getLastUserTracePointOfScript($scriptPath, $minBack = 1) {
		$back = (int) $minBack;
		foreach(debug_backtrace(false) as $key => $tracePoint) {
			if (!$key || !isset($tracePoint['file'])) continue;
		
			if ($back-- > 0) continue;
			
			if ($tracePoint['file'] == $scriptPath) {
				return $tracePoint;
			}
		}
		return null;
	}
	
	public static function getLastUserTracePointOutOfScript($scriptPath, $minBack = 1) {
		$back = (int) $minBack;
		foreach(debug_backtrace(false) as $key => $tracePoint) {
			if (!$key || !isset($tracePoint['file'])) continue;
	
			if ($back-- > 0) continue;
				
			if ($tracePoint['file'] != $scriptPath) {
				return $tracePoint;
			}
		}
		return null;
	}
}

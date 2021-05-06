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

class CastUtils {
	
	public static function assertTrue($arg) {
		if ($arg === true) return;
		
		throw new TypeCastException();
	}

	private static function checkScalarConvertabillity($arg) {
		return is_scalar($arg) || (is_object($arg) && !method_exists($arg, '__toString'));
	}
	
	public static function stringOrNull($arg) {
		if ($arg === null) return $arg;
		
		if (self::checkScalarConvertabillity($arg)) {
			return (string) $arg;
		}
		
		throw new TypeCastException('Can not cast ' . TypeUtils::getTypeInfo($arg) . ' to string: ' 
				. TypeUtils::getTypeInfo($arg));
	}
	
	public static function intOrNull($arg) {
		if ($arg === null) return $arg;
		
		if (self::checkScalarConvertabillity($arg)) {
			return (int) $arg;
		}
		
		throw new TypeCastException('Can not cast ' . TypeUtils::getTypeInfo($arg) . ' to int.');
	}
}

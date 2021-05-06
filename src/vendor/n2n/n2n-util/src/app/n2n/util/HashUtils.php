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
namespace n2n\util;

use n2n\util\col\Hashable;

class HashUtils {
	public static function base36Md5Hash($str, $length = null) {
		$hash = base_convert(md5((string) $str), 16, 36);
		if ($length === null) return $hash;
		return mb_substr($hash, 0, (int) $length);
	}
	
	public static function base36Sha1Hash($str, $length = null) {
		$hash = base_convert(sha1((string) $str), 16, 36);
		if (is_null($length)) return $hash;
		return mb_substr($hash, 0, (int) $length);
	}
	
	public static function base36Uniqid($moreEntropy = true) {
		if ($moreEntropy == false) {
			return base_convert(uniqid(), 16, 36);
		}
		
		return self::baseConvert(str_replace('.', '', uniqid(null, true)), 16, 36);
	}
	
	private static function baseConvert($str, $frombase, $tobase) {
		$str = trim($str);
		if (intval($frombase) != 10) {
			$len = strlen($str);
			$q = 0;
			for ($i=0; $i<$len; $i++) {
				$r = base_convert($str[$i], $frombase, 10);
				$q = bcadd(bcmul($q, $frombase), $r);
			}
		} else {
			$q = $str;
		}
	
		if (intval($tobase) != 10) {
			$s = '';
			while (bccomp($q, '0', 0) > 0) {
				$r = intval(bcmod($q, $tobase));
				$s = base_convert($r, 10, $tobase) . $s;
				$q = bcdiv($q, $tobase, 0);
			}
		} else {
			$s = $q;
		}
	
		return $s;
	}
	
	public static function hashCode($param) {
		if ($param instanceof Hashable) {
			return $param->hashCode();
		} else if (is_object($param)) {
			return spl_object_hash($param);
		} else if (is_scalar($param)) {
			return $param;
		}
	
		throw new \InvalidArgumentException();
	}
}

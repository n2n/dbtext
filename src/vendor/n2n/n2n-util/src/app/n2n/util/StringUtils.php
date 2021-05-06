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

use n2n\util\type\TypeUtils;

class StringUtils {
	const CRLF = "\r\n";
	
	public static function isEmpty($str) {
		return ctype_space($str) || mb_strlen($str) === 0;
	}
	
	public static function camelCased(string $str, bool $ucFirst = false) {
		$str = preg_replace_callback('/[^A-Za-z0-9]+([A-Za-z0-9]?)/',
				function ($found) { return ucfirst($found[1]); },
				$str);
	
		if($ucFirst) $str = ucfirst($str);
	
		return $str;
	}
	
	public static function hyphenated(string $str, $underline = true) {
		$separator = $underline ? '_' : '-';
		
		$str = preg_replace('/[^0-9a-zA-Z]+/', $separator, (string) $str);
		$str = preg_replace('/((?<=[a-z0-9])[A-Z]|(?<=.)[A-Z](?=[a-z]))/', $separator . '${0}', $str);
		return mb_strtolower($str);
	}
	
	/**
	 * Tries to convert a tecnical string to a string which is easy readable. This method may be changed in future
	 * releases.
	 * @param string $str
	 * @return string
	 */
	public static function pretty(string $str) {
		return ucfirst(preg_replace_callback('/[_-]+([^_-]?)/',
				function ($found) { return ' ' . ucfirst($found[1]); },
				$str));
	}
	
	
	public static function strOf($arg, bool $lenient = false) {
		if ($arg === null || is_scalar($arg) || (is_object($arg) && method_exists($arg, '__toString'))) {
			return (string) $arg;
		}
		
		if ($lenient) {
			return TypeUtils::getTypeInfo($arg);
		}
		
		throw new \InvalidArgumentException('Can not be converted to string: '
				. TypeUtils::getTypeInfo($arg));
	}
	
// 	public static function toText($str) {
// 		$str = preg_replace_callback('/_./',
// 			create_function(
// 			// hier sind entweder einfache Anführungszeichen nötig
// 			// oder alternativ die Maskierung aller $ als \$
// 				  '$treffer',
// 				  'return " " . mb_strtoupper($treffer[0][1]);'
// 			), $str);
	
// 		$str = ucfirst($str);
	
// 		return $str;
// 	}
	
// 	public static function toDecimal($number, $precision = 0, $digits = null) {
// 		$number = round($number, $precision);
// 		if (!$digits) $digits = $precision;
	
// 		return sprintf("%01.{$digits}f", $number);
// 	}
	
// 	public static function explodeCamelCase($str, $toLowerCase = false) {
// 		$tmp = preg_replace('/((?<=[a-z0-9])[A-Z]|(?<=.)[A-Z](?=[a-z]))/', '_${0}', (string) $str);
	
// 		if ($toLowerCase) {
// 			$tmp = mb_strtolower($tmp);
// 		}
// 		return explode("_", $tmp);
// 	}

	/**
	 * @param string|array $needle
	 * @param string $str
	 * @return boolean
	 */
	public static function contains($needle, string $str, bool $casesensitive = true) {
		if ($casesensitive) {
			if (!is_array($needle)) return false !== strpos($str, $needle);
			
			foreach ($needle as $needleField) {
				if (false !== strpos($str, $needleField)) return true;
			}
			
			return false;
		}
		
		
		if (!is_array($needle)) return false !== stripos($str, $needle);
			
		foreach ($needle as $needleField) {
			if (false !== stripos($str, $needleField)) return true;
		}
			
		return false;
		
	}
	/**
	 * 
	 * @param string $needle
	 * @param string $str
	 * @return boolean
	 */
	public static function endsWith($needle, $str) {
		return mb_substr($str, -mb_strlen($needle)) == $needle;
	}
	/**
	 * 
	 * @param string $needle
	 * @param string $str
	 * @return boolean
	 */
	public static function startsWith($needle, $str) {
		return mb_substr($str, 0, mb_strlen($needle)) == $needle;
	}
	
// 	/**
// 	 * Replaces the accents of strings
// 	 *
// 	 * @param string $str
// 	 * @return string
// 	 *
// 	 * @deprecated unused and is too buggy
// 	 */
// 	public static function replaceAccents($string) {
// 		$string = htmlentities($string, ENT_COMPAT, "UTF-8");
// 		$string = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde);/', '$1', $string);
// 		return html_entity_decode($string);
// 	}
	
// 	public static function toUrl($link){
// 		//
// 		if (preg_match('/^[a-zA-Z]+:\/\//', $link)){
// 		return $link;
// 		}
// 		return "http://" . $link;
// 	}
	
// 	public static function removeProtocolFromUrl($link) {
// 		return preg_replace('/^[a-zA-Z]+:\/\//', "", $link);
// 	}
	
// 	/**
// 	* if $var is undefined, return $default, otherwise return $var
// 	*
// 	* @param string $var
// 	* @param string $default
// 	* @return string
// 	*/
// 	public static function nvl(&$var, $default = null) {
// 		return isset($var) ? $var : $default;
// 	}

	public static function gzuncompress($data, $length = 0) {
		$str = @gzuncompress($data, $length);
		
		if ($str === false && $err = error_get_last()) {
			throw new GzuncompressFailedException($err['message']);
		}
		
		return $str;
	}
	

	const SER_FALSE = 'b:0;';
	
	/**
	 *
	 * @param string $serializedStr
	 * @throws UnserializationFailedException
	 */
	public static function unserialize($serializedStr) {
// 		if (class_exists('n2n\core\N2N') && N2N::isInitialized()) {
// 			N2N::getExceptionHandler()->ignoreNextTriggeredErrNo(E_NOTICE);
			
// 			$obj = unserialize($serializedStr);
			
// 			if ($obj === false && $errMsg = N2N::getExceptionHandler()->getIgnoredErrorMessage()) {
// 				throw new UnserializationFailedException($errMsg);
// 			}
			
// 			N2N::getExceptionHandler()->ignoreNextTriggeredErrNo(0);
			
// 			return $obj;
// 		}
		
		if ($serializedStr == self::SER_FALSE) {
			return false;
		}
		
		$obj = @unserialize($serializedStr);
		
		if ($obj === false && $err = error_get_last()) {
			throw new UnserializationFailedException($err['message']);
		}
		
		return $obj;
	}
	
	/**
	 * 
	 * @see json_encode() - PHP Core
	 * @param $json
	 * @param $assoc
	 * @param $depth
	 * @throws JsonEncodeFailedException
	 * @return string
	 */
	public static function jsonEncode($value, $options = 0) {
		try {
			return json_encode($value, JSON_THROW_ON_ERROR | $options);
		} catch (\JsonException $e) {
			throw new JsonEncodeFailedException($e->getMessage(), 0, $e);
		}
	}
	/**
	 * 
	 * @param string $json
	 * @param bool $assoc
	 * @param int $depth
	 * @return array|object
	 * @throws JsonDecodeFailedException
	 */
	public static function jsonDecode(string $json, bool $assoc = false, int $depth = 512) {
		try {
			return json_decode($json, $assoc, $depth, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			throw new JsonDecodeFailedException('JSON string could not be decoded.', 0, $e);
		}
	}
	
	public static function insert($originalStr, $pos, $insertStr) {
		$originalStr = (string) $originalStr;
		$pos = (int) $pos;
	
		if ($pos < 0 || $pos > mb_strlen($originalStr)) {
			throw new \OutOfBoundsException('String pos is out of bounds. Pos: ' . $pos . ', Min: 0; Max: ' 
					. mb_strlen($originalStr));
		}
			
		$part1 = mb_substr($originalStr, 0, $pos);
		$part2 = mb_substr($originalStr, $pos);
	
		return $part1 . (string) $insertStr . $part2;
	}
	
	/**
	 * @param string $str
	 * @param int $length
	 * @param string $suffix
	 * @return string
	 */
	public static function reduce(string $str, int $length, string $suffix = '') {
		if (mb_strlen($str) <= $length) {
			return $str;
		}
		
		return mb_substr($str, 0, $length) . $suffix;
	}
	
	/**
	 * @param string $str
	 * @param int $length
	 * @param string $suffix
	 * @return string
	 */
	public static function reduceFront(string $str, int $length, string $suffix = '') {
		if (mb_strlen($str) <= $length) {
			return $str;
		}
		
		return $suffix . mb_substr($str, -$length);
	}
	
	/**
	 * @param string $str
	 * @param int $length
	 * @param string $suffix
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public static function reduceWordish(string $str, int $length, string $suffix = '...') {
		if (mb_strlen($str) <= $length) {
			return $str;
		}
		$suffixLen = mb_strlen($suffix);
		
		if ($suffixLen > $length) {
			throw new \InvalidArgumentException('Suffix is longer than length.');
		}
		
		$str = mb_substr($str, 0, $length);
		
		if (preg_match_all('/[\\s\\-]/', $str, $matches, PREG_OFFSET_CAPTURE)) {
			foreach(array_reverse($matches[0]) as $match) {
				// match[] return ascii pos (NOT UNICODE)
				$str = substr($str, 0, $match[1]);
				if ((mb_strlen($str) + $suffixLen) > $length) {
					continue;
				}
				
				return mb_substr($str, 0, $match[1]) . $suffix;
			}
		}
		
		return mb_substr($str, 0, ($length - $suffixLen)) . $suffix;
	}
	
	public static function removePrefix(string $prefix, ?string $str, bool $returnAnyway = true) {
		if ($str === null) return null;
		
		$len = mb_strlen($prefix);
		
		if ($prefix != mb_substr($str, 0, $len)) {
			return $returnAnyway ? $str : null;
		}
		
		return mb_substr($str, $len);
	}
	
	/**
	 * @param string|null $value
	 * @return string
	 */
	public static function convertNonPrintables(?string $value) {
		if (empty($value)) {
			return $value;
		}
		
		$ret = '';
		$length = strlen($value);
		for($i = 0; $i < $length; $i ++) {
			$current = ord($value[$i]);
			
			if (($current == 0x9) || ($current == 0xA) || ($current == 0xD)
					|| (($current >= 0x20) && ($current <= 0xD7FF)) 
					|| (($current >= 0xE000) && ($current <= 0xFFFD)) 
					|| (($current >= 0x10000) && ($current <= 0x10FFFF))) {
				$ret .= chr($current);
			} else {
				$ret .= ' ';
			}
		}
		return $ret;
	}
	
// 	public static function generateBase36Uid($maxLentgh = null) {
// 		$uid =  base_convert(uniqid(), 16, 36);
		
// 		if ($maxLentgh) {
// 			return substr($uid, 0, $maxLentgh);
// 		}
		
// 		return $uid;
// 	}
	
// 	public static function toBase36Hash($str, $maxLentgh = null) {
// 		$hash = base_convert(md5((string) $str), 16, 36);
		
// 		if ($maxLentgh) {
// 			return substr($hash, 0, $maxLentgh);
// 		}
		
// 		return $hash;
// 	}
	
// 	public static function generatePassword($len = 8, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"){
// 		$string = "";
// 		for ($i = 0; $i < $len; $i++) {
// 			$pos = rand(0, strlen($chars)-1);
// 			$string .= $chars{$pos};
// 		}
// 		return $string;
// 	}
	
// 	public static function shortenString($str, $maxChars, $shorten = ' ...') {
// 		$str = wordwrap($str, $maxChars, '###');
// 		$str = explode('###', $str);
// 		return $str[0] . (count($str) > 1 ? $shorten : '');
// 	}

	public static function doEqual($param1, $param2) {
		if ($param1 === null || $param2 === null) {
			return $param1 === $param2;
		}
		return (string) $param1 === (string) $param2;
	}
	/**
	 * @param string $pattern
	 * @param string $subject
	 * @param array $matches
	 * @param number $flags
	 * @param number $offset
	 * @throws RegexSyntaxException
	 * @return number
	 */
	public static function pregMatch($pattern, $subject, array &$matches = null, $flags = 0, $offset = 0) {
		if (false !== ($result = @preg_match($pattern, $subject, $matches, $flags, $offset))) {
			return $result;
		}
	
		$err = error_get_last();
		throw new RegexSyntaxException($err['message']);
	}
	
	public static function buildAcronym(string $str): string {
		$chars = array();
		$matches = null;
		preg_match_all('/([a-z]([A-Z])|(^|_)([a-zA-Z]))/m', $str, $matches);
	
		foreach ($matches[2] as $key => $value) {
			if (0 < strlen($value)) {
				$chars[] = $value;
				continue;
			}
	
			$chars[] = $matches[4][$key];
		}
	
		if (empty($chars)) return $str;
	
		return implode($chars);
	}
	
	public static function stripNl(string $str, string $replacement = ' ', bool $strpWhitespacePrefixes = true) {
		return preg_replace('/' . ($strpWhitespacePrefixes ? '\\s*' : '') . '[\\r\\n]+/', $replacement, $str);
	}
}

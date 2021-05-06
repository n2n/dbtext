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
namespace n2n\l10n;

class TextCollection {
	const ARG_PREFIX = '{';
	const ARG_SUFFIX = '}';
	const NUM_SEPARATOR = '|';
	const NUM_UTIL = ',';
	
	private $texts;
	/**
	 * @param array $texts
	 */
	public function __construct(array $texts) {
		$this->texts = $texts;
	}
	
	public function containsCode($code) {
		return isset($this->texts[$code]);
	}
	
	/**
	 * @return boolean
	 */
	public function isEmpty() {
		return empty($this->texts);
	}
	
	/**
	 * @return array
	 */
	public function getTexts() {
		return $this->texts;
	}
	/**
	 * @param array $texts
	 */
	public function setTexts(array $texts) {
		$this->texts = $texts;
	}
	/**
	 * @param string $code
	 * @return boolean
	 */
	public function has($code, $num = null) {
		return null !== $this->get($code, $num);
	}
	/**
	 * @param string $code
	 * @param int num
	 * @return mixed string
	 */
	public function get($code, $num = null) {
		if (!isset($this->texts[$code])) return null;
		
		$texts = $this->texts[$code];
		if (!is_array($texts)) return $texts;
		
		if ($num === null) {
			if (count($texts)) return current($texts);
			return null;
		}
		
		if (isset($texts[$num])) {
			return $texts[$num];
		}
		
		$currentText = null;
		foreach ($texts as $key => $text) {
			switch ($this->matchesNum($key, $num)) {
				case self::MATCH_QUALITY_HIGH:
					return $text;
				case self::MATCH_QUALITY_LOW:
					$currentText = $text;
			}
		}
		
		return $currentText;
	}
	
	const MATCH_QUALITY_NONE = 0;
	const MATCH_QUALITY_LOW = 1;
	const MATCH_QUALITY_HIGH = 2;
	
	private function matchesNum($key, $num) {
		$expressions = explode(self::NUM_SEPARATOR, $key);
		if (in_array($num, $expressions)) {
			return self::MATCH_QUALITY_HIGH;
		}
		
		foreach ($expressions as $expression) {
			$range = explode(self::NUM_UTIL, $expression, 2);
			if (count($range) != 2) continue;
			
			if ((!strlen($range[0]) || $num >= $range[0])
					&& (!strlen($range[1]) || $num <= $range[1])) {
				return self::MATCH_QUALITY_LOW;		
			} 
		}
		
		return self::MATCH_QUALITY_NONE;
	}
	
	/**
	 * @param string $code
	 * @param array $args
	 * @param int|null $num
	 * @param bool $fallbackToCode
	 * @return string null if $fallbackToCode is false an no text was found.
	 */
	public function translate($code, array $args = null, int $num = null, bool $fallbackToCode = true) {
		$text = $this->get($code, $num);
		if ($text === null) {
			if (!$fallbackToCode) return null;
			return self::implode($code, $args);
			
		}
		
		return self::fillArgs($text, $args);
	}

	/**
	 * @param string $text
	 * @param array|null $args
	 * @return string
	 */
	public static function fillArgs(string $text, array $args = null): string {
		foreach ((array) $args as $name => $value) {
			$text = str_replace(self::ARG_PREFIX . $name . self::ARG_SUFFIX, $value, $text);
		}
		return $text;
	}
	
	
	
	/**
	 * @param string $langKey
	 * @param array $args
	 * @return string
	 */
	public static function implode($langKey, array $args = null) {
		// replace suffix like _txt, _label, _tooltip...
		$langKey = preg_replace('/_[^_]*$/', '', $langKey);
		
		if (empty($args)) return $langKey;
	
		$argStr = '';
		foreach ($args as $name => $arg) {
			if (strlen($argStr)) $argStr .= ', ';
			$argStr .= $name . ' = ' . $arg;
		}
	
		return $langKey . ' [' . $argStr . ']';
	}
}

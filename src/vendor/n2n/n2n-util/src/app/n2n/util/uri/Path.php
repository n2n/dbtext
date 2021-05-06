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
namespace n2n\util\uri;

use n2n\util\StringUtils;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;

final class Path {
	const DELIMITER = '/';
// 	const SPECIAL_CHARS = array(':', '?', '#', '[', ']', '@');
	
	private $pathParts;
	private $str;
	protected $leadingDelimiter;
	protected $endingDelimiter;
	
	public function __construct(array $pathParts, $leadingDelimiter = false, $endingDelimiter = false) {
		$this->pathParts = array();
		$this->applyPathPartArray($pathParts);
		$this->leadingDelimiter = (boolean) $leadingDelimiter;
		$this->endingDelimiter = (boolean) $endingDelimiter;
		
		if (empty($this->pathParts) && $this->leadingDelimiter != $this->endingDelimiter) {
			$this->leadingDelimiter = true;
			$this->endingDelimiter = true;
		}
	} 
	
	private function applyPathPartArray(array $pathParts) {
		foreach ($pathParts as $pathPart) {
			if (null === $pathPart) continue;
			
			if (is_array($pathPart)) {
				$this->applyPathPartArray($pathPart);
				continue;
			}
				
			if ($pathPart instanceof Path) {
				$this->pathParts = array_merge($this->pathParts, $pathPart->getPathParts());
				continue;
			}
			
			$patPartStr = UrlUtils::urlifyPart($pathPart);
			if (mb_strlen($patPartStr)) {
				$this->pathParts[] = $patPartStr;
			}
		}
	}
	
	protected function setStr($str) {
		$this->pathParts = null;
		$this->str = (string) $str;
	}
	
	protected function setPathParts(array $pathParts) {
		$this->pathParts = $pathParts;
		$this->str = null;
	}
	
	public function isEmpty($checkDelimiters = false) {
		if ($checkDelimiters && ($this->leadingDelimiter || $this->endingDelimiter)) {
			return true;
		}
		
		if  ($this->pathParts !== null) {
			return empty($this->pathParts);
		}
		
		return 0 == mb_strlen($this->str);
	}
	
	public function hasLeadingDelimiter() {
		return $this->leadingDelimiter;
	}
	
	public function hasEndingDelimiter() {
		return $this->endingDelimiter;
	}
	
	/**
	 * @return string[]
	 */
	public function getPathParts() {
		if ($this->pathParts !== null) {
			return $this->pathParts;
		}
		
		$this->pathParts = array();
		foreach (explode(self::DELIMITER, $this->str) as $pathPart) {
			if (mb_strlen($pathPart) === 0) continue;
			
			$this->pathParts[] = urldecode($pathPart);
		}
		return $this->pathParts;
	}
		
	/**
	 * @param bool $required
	 * @throws IllegalStateException
	 * @return string|null
	 */
	public function getFirstPathPart(bool $required = true) {
		$pathParts = $this->getPathParts();
		if (!empty($pathParts)) {
			return reset($pathParts);
		}
		
		if (!$required) return null;
		
		throw new IllegalStateException('Path empty');
	}
	
	/**
	 * @param bool $required
	 * @throws IllegalStateException
	 * @return string|null
	 */
	public function getLastPathPart(bool $required = true) {
		$pathParts = $this->getPathParts();
		if (!empty($pathParts)) {
			return end($pathParts);
		}
		
		if (!$required) return null;
		
		throw new IllegalStateException('Path empty');
	}
	
	public function toEncodedArray() {
		$encArray = array();
		
		if ($this->str !== null) {
			foreach (explode(self::DELIMITER, $this->str) as $pathPart) {
				if (mb_strlen($pathPart) === 0) continue;
					
				$encArray[] = $pathPart;
			}
			
			return $encArray;
		}	

		foreach ($this->pathParts as $pathPart) {
			$encArray[] = rawurlencode($pathPart);
		}
		
		return $encArray;
	}

	public function size() {
		return count($this->getPathParts());
	}
	
	public function __toString(): string {
		return $this->toRealString($this->leadingDelimiter, $this->endingDelimiter);
	}
	
	public function toRealString(bool $leadingDelimiter = null, bool $endingDelimiter = null) {
		if ($leadingDelimiter === null) $leadingDelimiter = $this->leadingDelimiter;
		if ($endingDelimiter === null) $endingDelimiter = $this->endingDelimiter;
		
		$realStr = '';
		if ($leadingDelimiter) {
			$realStr .= self::DELIMITER;
		}
		
		if ($this->isEmpty()) {
// 			if (!$leadingDelimiter && $endingDelimiter) {
// 				$realStr .= self::DELIMITER;
// 			}
			
			return $realStr;
		}
	
		if ($this->str !== null) {
			$realStr .= $this->str;		
		} else {
			$realStr .= $this->str = implode(self::DELIMITER, $this->toEncodedArray());
		}
		
		return $realStr . ($endingDelimiter ? self::DELIMITER : '');
	}
	
	/**
	 * <p>Extends the path with part parts. Passed strings will be interpreted as unencoded path parts. Therefore calls
	 * like <code>ext('path-part-1/path-part2')</code> are not possible because <code>path-part-1/path-part2</code>
	 * would be used and encoded as one single path part (<code>path-part-1%2Fpath-part2</code>). That behavior makes 
	 * this method safer than {@link Path::encExt()} however.</p>
	 * 
	 * <p>
	 * <strong>Usage examples</strong>
	 * <pre>
	 * $path->ext('path-part-1', 'path-part2')
	 * </pre>
	 * <pre>
	 * $path->ext(array('path-part-1', 'path-part2'))
	 * </pre>
	 * </p>
	 * 
	 * @param mixed ...$parts Use string, Path or array with string and Path fields.
	 * @return \n2n\util\uri\Path
	 */
	public function ext(...$pathPartExts): Path {
		if (empty($pathPartExts)) return $this;
		
		ArgUtils::valArray($pathPartExts, ['scalar', Path::class, 'array', null]);
		
		$leadingDelimiter = false;
		$endingDelimiter = false;
		$pathExtStr = null;
		
		foreach ($pathPartExts as $part) {
			$part = Path::create($part);
						
			if ($pathExtStr === null && !$leadingDelimiter) {
				$leadingDelimiter = $part->hasLeadingDelimiter();
			}
			$endingDelimiter = $part->hasEndingDelimiter();
			
			if ($part->isEmpty()) continue;
			
			if ($pathExtStr !== null) {
				$pathExtStr .= Path::DELIMITER;
			}
			$pathExtStr .= $part->toRealString(false, false);
		}
		
		if (!strlen($pathExtStr)) {
			return $this->chEndingDelimiter($this->endingDelimiter || $leadingDelimiter);
		}
		
		if ($this->isEmpty()) {
			$newPath = new Path(array());
			$newPath->setStr($pathExtStr);
			$newPath->leadingDelimiter = $leadingDelimiter || $this->hasLeadingDelimiter();
			$newPath->endingDelimiter = $endingDelimiter;
		}
		
		$newPath = new Path(array());
		$newPath->setStr($this->toRealString(false, true) . $pathExtStr);
		$newPath->leadingDelimiter = $this->leadingDelimiter;
		$newPath->endingDelimiter = $endingDelimiter;
		
		return $newPath;
	}
	
	/**
	 * <p>Extends the path. Passed strings will be interpreted as encoded paths. Strings in arrays will be interpreted 
	 * as unencoded path parts.</p>
	 * 
	 * <p>
	 * <strong>Usage examples</strong>
	 * <pre>
	 * $path->extEnc('path-part-1/path-part2', 'Path%20Part%203/Path%20Part%204');
	 * </pre>
	 * <pre>
	 * $path->extEnc(array('path-part-1', 'path-part2'))
	 * </pre>
	 * </p>
	 * 
	 * @param mixed ...$pathExts Use string, Path or array with string and Path fields.
	 * @return \n2n\util\uri\Path
	 */
	public function extEnc(...$pathExts): Path {
		$paths = array();
		foreach ($pathExts as $pathExt) {
			$paths[] = Path::create($pathExt);
		}
		
		return $this->ext(...$paths);
	}
	
	public function getParent(): Path {
		$pathParts = $this->getPathParts();
		if (null == array_pop($pathParts)) {
			return null;
		}
		
		return new Path($pathParts, $this->leadingDelimiter);
	}
	
	/**
	 * @param int $num
	 * @return \n2n\util\uri\Path
	 */
	public function reduced(int $num) {
		return $this->sub(0, $this->size() - $num);
	}	
		
	/**
	 * @param int $start
	 * @param int $num
	 * @throws \InvalidArgumentException
	 * @return \n2n\util\uri\Path
	 * @todo make new
	 */
	public function sub(int $start, int $num = null) {
		$pathParts = $this->getPathParts();
		$numPathParts = count($pathParts);
		if ($start < 0) {
			$start = $numPathParts + $start;
		}
		
		if ($start < 0 || $start > $numPathParts) {
			throw new \InvalidArgumentException('Start \'' . $start . '\' is out of bound for path: ' 
					. $this->__toString());
		}
		
		$end = null;
		if ($num === null) {
			$end = $numPathParts;
		} else {
			$end = $start + $num;
		}
		
		$subPathParts = array();
		$i = $start;
		for (; $i < $end; $i++) {
			if ($i >= $numPathParts) {
				throw new \InvalidArgumentException('Start \'' . $start . '\' and num \'' . $num 
						. '\' are out of bounds: ' . $this->__toString());
			}
			$subPathParts[] = $pathParts[$i];
		}
		
		$subPath = new Path($subPathParts,
				$this->leadingDelimiter && $start === 0,
				$this->endingDelimiter && $i === $numPathParts);
		
		return $subPath;
	}
	
	public function toUrl($query = null, $fragment = null) {
		return new Url(null, null, $this, Query::create($query), $fragment);
	}
	
// 	public function toRealString($leadingDelimiter = true, $endingDelimiter = null) {
// 		$str = ($leadingDelimiter ? self::DELIMITER : ''); 
// 		if ($this->isEmpty()) return $str;
		
// 		return $str . $this->__toString() 
// 				. ($endingDelimiter === true || ($this->endingDelimiter && $endingDelimiter !== false) ? self::DELIMITER : '');
// 	}



	public function chLeadingDelimiter($leadingDelimiter) {
		if ($this->leadingDelimiter == $leadingDelimiter) return $this;
		return new Path($this->getPathParts(), $leadingDelimiter);
	}
	
	public function chEndingDelimiter($endingDelimiter) {
		if ($this->endingDelimiter == $endingDelimiter) return $this;
		return new Path($this->getPathParts(), $this->leadingDelimiter, $endingDelimiter);
	}
	
	public function chPathParts(array $pathParts) {
		return new Path($pathParts, $this->leadingDelimiter, $this->endingDelimiter);
	}
	
	public function equals($obj) {
		return $obj instanceof Path && $obj->__toString() === $this->__toString();
	}
	
	public static function create($expression, bool $lenient = false) {
		if ($expression instanceof Path) {
			return $expression;
		}
		
		if (is_array($expression)) {
			return new Path($expression);
		}
		
		$expression = UrlUtils::urlifyPart($expression);
		
		if ($expression === null || !mb_strlen($expression = (string) $expression)) {
			return new Path(array());
		}
		
		$path = null;
		if (self::validatePathString($expression)) {
			$path = new Path(array());
			$path->setStr(trim($expression, self::DELIMITER));
		} else if ($lenient) {
			$path = new Path(explode(self::DELIMITER, urldecode($expression)));
		} else {
			throw new \InvalidArgumentException('Path contains invalid characters: ' . $expression);
		}
		
		$path->leadingDelimiter = StringUtils::startsWith(self::DELIMITER, $expression);
		$path->endingDelimiter = StringUtils::endsWith(self::DELIMITER, $expression);
		return $path;
		
// 		return new Path(explode(self::DELIMITER, $expression));
	}
	
// 	public static function createFromStr($str) {
// 		$str = (string) $str; 
		
// 		if (!self::validatePathString($str)) {
// 			throw new UrlComponentParseException('Invalid path: ' . $str);
// 		}
		
// 		$path = new Path(array());
// 		$path->setStr(trim($str, self::DELIMITER));
// 		$path->leadingDelimiter = StringUtils::startsWith(self::DELIMITER, $str);
// 		$path->endingDelimiter = StringUtils::endsWith(self::DELIMITER, $str);
// 		return $path;
// 	}
	
	public static function validatePathString($pathString) {
		return (boolean) preg_match('#^[a-zA-Z0-9-._~!$&\'()*+,;=:@%/]+$#', $pathString);
	}
}

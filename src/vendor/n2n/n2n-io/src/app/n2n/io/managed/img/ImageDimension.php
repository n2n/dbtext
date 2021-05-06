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
namespace n2n\io\managed\img;

use n2n\util\StringUtils;

class ImageDimension {
	const STR_ATTR_SEPARATOR = 'x';
	
	const CROP_ID_KEY = 'ccenter';
	const SCALE_UP_ID_KEY = 's';
	
	private $width;
	private $height;
	private $cropped;
	private $scaledUp;
	private $idExt;
	
	/**
	 * @param int $width
	 * @param int $height
	 * @param bool $cropped
	 * @param bool $scaledUp
	 * @param string $idExt
	 */
	public function __construct(int $width, int $height, bool $cropped, bool $scaledUp, string $idExt = null) {
		$this->width = $width;
		$this->height = $height;
		$this->cropped = $cropped;
		$this->scaledUp = $scaledUp;
		$this->idExt = $idExt;
	}
	
	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * @return int
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * @return bool
	 */
	public function isCropped() {
		return $this->cropped;
	}
	
	/**
	 * @return bool
	 */
	public function isScaledUp() {
		return $this->scaledUp;
	}
	
	/**
	 * @return string|null
	 */
	public function getIdExt() {
		return $this->idExt;
	}
	
	public function __toString(): string {
		$id =  $this->width . self::STR_ATTR_SEPARATOR . $this->height;
		
		if (!$this->cropped && !$this->scaledUp && $this->idExt === null) {
			return $id;
		}
		
		$id .= self::STR_ATTR_SEPARATOR . ($this->cropped ? self::CROP_ID_KEY : '') 
				. ($this->scaledUp ? self::SCALE_UP_ID_KEY : '');
		
		if ($this->idExt === null) {
			return $id;
		}
		
		$id .= self::STR_ATTR_SEPARATOR . $this->idExt;
		
		return $id;
	}
	
	static function createFromString($string): ImageDimension {
		$partParts = explode(self::STR_ATTR_SEPARATOR, trim($string), 4);
		if (2 > count($partParts) || !is_numeric($partParts[0]) || !is_numeric($partParts[1])) {
			throw new \InvalidArgumentException('Dimension is invalid: ' . $string);
		}
		
		$width = (int) $partParts[0];
		$height = (int) $partParts[1];

		if ($width < 1 && $height < 1) {
			throw new \InvalidArgumentException();
		}
		
		$cropped = false;
		$scaledUp = false;
		if (isset($partParts[2])) {
			$part = $partParts[2];
			if (StringUtils::startsWith(self::CROP_ID_KEY, $part)) {
				$cropped = true;
				$part = mb_substr($part, mb_strlen(self::CROP_ID_KEY));
			}
			
			$scaledUp = $part === self::SCALE_UP_ID_KEY;
			
			if ($part !== '' && $part !== self::SCALE_UP_ID_KEY) {
				throw new \InvalidArgumentException('Dimension is invalid: ' . $string);
			}
		}
		
		$idExt = null;
		if (isset($partParts[3])) {
			$idExt = $partParts[3];
		}
			
		return new ImageDimension($width, $height, $cropped, $scaledUp, $idExt);
	}
	
}

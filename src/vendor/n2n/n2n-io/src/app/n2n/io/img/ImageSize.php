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
namespace n2n\io\img;

use n2n\util\type\ArgUtils;
use n2n\io\managed\img\ThumbCut;

class ImageSize {	
	private $width;
	private $height;
	/**
	 * 
	 * @param resource $resource
	 * @param bool $keepHandleAlive if true handle doesn't get destroyed in __destruct()
	 */
	public function __construct(int $width, int $height) {
		$this->width = $width;
		$this->height = $height;
	}
	
	const AUTO_CROP_MODE_CENTER = 'center';
	const AUTO_CROP_MODE_TOP = 'top';
	
	/**
	 * 
	 * @param int $width
	 * @param int $height
	 * @param bool $cropAllowed
	 * @return ThumbCut
	 */
	public function proportionalResize(int $width, int $height, string $autoCropMode = null, bool $scaleUp = true) {
		ArgUtils::valEnum($autoCropMode, self::getAutoCropModes(), null, true);
		
		$cropWidth = null; $cropHeight = null; $x = null; $y = null;
		$this->calcResampleSizes($width, $height, $cropWidth, $cropHeight, $x, $y, $autoCropMode !== null);
		
		if ($autoCropMode == self::AUTO_CROP_MODE_TOP) {
			$y = 0;
		}
		
		$this->resample($x, $y, $cropWidth, $cropHeight, $width, $height);
		return new ThumbCut($x, $y, $cropWidth, $cropHeight);
	}
	/**
	 *
	 * @param int $destWidth
	 * @param int $destHeight
	 * @param int $cropWidth
	 * @param int $cropHeight
	 * @param int $x
	 * @param int $y
	 * @param bool $crop
	 */
	private function calcResampleSizes(&$destWidth, &$destHeight, &$cropWidth, &$cropHeight, &$x, &$y, $crop = false) {
		$maxWidth = $destWidth;
		$maxHeight = $destHeight;
		
		// get aspect ratio of original and destination image
		$orgAr = $this->width / $this->height;
		$destAr = $maxWidth / $maxHeight;
		
		if ($orgAr >= $destAr) {
			// picture to wide, decrease width
			if ($crop) {
				// file has to be croped
				$destWidth = $maxWidth;
				$destHeight = /*($maxHeight > $this->height ? $this->height :*/ $maxHeight/*)*/;
				$cropWidth = /*($maxHeight > $this->height ? $maxWidth :*/ round($this->height * $destAr) /*)*/;
				$cropHeight = $this->height;
				$x = round(($this->width - $cropWidth) / 2);
				$y = 0;
			} else {
				// file has to be shrinked
				$destWidth = $maxWidth;
				$destHeight = round($maxWidth / $orgAr);
				$cropWidth = $this->width;
				$cropHeight = $this->height;
				$x = 0;
				$y = 0;
			}
		} else {
			// picture to high, decrease height
			if ($crop) {
				// file has to be croped
				$destWidth = /*($maxWidth > $this->width ? $this->width : */$maxWidth/*)*/;
				$destHeight = $maxHeight;
				$cropWidth = $this->width;
				$cropHeight = /*($maxWidth > $this->width ? $maxHeight :*/ round($this->width / $destAr) /*)*/;
				$x = 0;
				$y = round(($this->height - $cropHeight)/2);
			} else {
				// file has to be shrinked
				$destWidth = round($maxHeight * $orgAr);
				$destHeight = $maxHeight;
				$cropWidth = $this->width;
				$cropHeight = $this->height;
				$x = 0;
				$y = 0;
			}
		}
	}
}

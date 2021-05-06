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

class ImageResource {	
	private $resource;
	private $width;
	private $height;
	private $keepHandleAlive;
	/**
	 * 
	 * @param mixed $resource type resource in php 7 and GdImage in php 8
	 * @param bool $keepHandleAlive if true handle doesn't get destroyed in __destruct()
	 */
	public function __construct($resource, $keepHandleAlive = false) {
// 		ArgUtils::valType($resource, 'resource');
		
		$this->width = imagesx($resource);
		$this->height = imagesy($resource);
		$this->resource = $resource;
		$this->keepHandleAlive = $keepHandleAlive;
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
	public function proportionalResize(int $width, int $height, string $autoCropMode = null) {
		ArgUtils::valEnum($autoCropMode, self::getAutoCropModes(), null, true);
		
		$cropWidth = null; $cropHeight = null; $x = null; $y = null;
		$this->calcResampleSizes($width, $height, $cropWidth, $cropHeight, $x, $y, $autoCropMode !== null);
		
		if ($autoCropMode == self::AUTO_CROP_MODE_TOP) {
			$y = 0;
		}
		
		$this->resample($x, $y, $cropWidth, $cropHeight, $width, $height);
		return new ThumbCut($x, $y, $cropWidth, $cropHeight);
	}
	
	public static function getAutoCropModes() {
		return array(self::AUTO_CROP_MODE_CENTER, self::AUTO_CROP_MODE_TOP);
	}
	/**
	 * 
	 * @param int $startX
	 * @param int $startY
	 * @param int $destWidth
	 * @param int $destHeight
	 */
	public function crop($startX, $startY, $destWidth, $destHeight) {
		$this->resample($startX, $startY, $destWidth, $destHeight, $this->width, $this->height);
	}
	/**
	 * adds a watermark to the image
	 *
	 * @param ImageResource $watermark
	 * @param int $watermarkPos
	 * @param int $watermarkMargin
	 */
	public function watermark(ImageResource $watermark, $watermarkPos = 4, $watermarkMargin = 10) {
		switch($watermarkPos) {
			// center
			case 0:
				$watermarkdestX = $this->width/2 - $watermark->getWidth()/2;
				$watermarkdestY = $this->height/2 - $watermark->getHeight()/2;
				break;
				// top left
			case 1:
				$watermarkdestX = $watermarkMargin;
				$watermarkdestY = $watermarkMargin;
				break;
				// top right
			case 2:
				$watermarkdestX = $this->width - $watermark->getWidth() - $watermarkMargin;
				$watermarkdestY = $watermarkMargin;
				break;
				// bottom left
			case 3:
				$watermarkdestX = $watermarkMargin;
				$watermarkdestY = $this->height - $watermark->getHeight() - $watermarkMargin;
				break;
				// bottom right
			case 4:
			default:
				$watermarkdestX = $this->width - $watermark->getWidth() - $watermarkMargin;
				$watermarkdestY = $this->height - $watermark->getHeight() - $watermarkMargin;
				break;
		}

		// set transparency true
		imagealphablending($this->resource, true);
		$watermarkRes = $watermark->getHandle();
		imagealphablending($watermarkRes, true);
		imagecopy($this->resource, $watermarkRes, $watermarkdestX, $watermarkdestY, 0, 0, $watermark->getWidth(), $watermark->getHeight());
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
	protected function calcResampleSizes(&$destWidth, &$destHeight, &$cropWidth, &$cropHeight, &$x, &$y, $crop = false) {
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
	/**
	 * 
	 * @param int $startX
	 * @param int $startY
	 * @param int $originalW
	 * @param int $originalH
	 * @param int $destWidth
	 * @param int $destHeight
	 */
	private function resample($startX, $startY, $originalW, $originalH, $destWidth, $destHeight) {
		// check the ratios of the two formats match
		$orgAr = $originalW / $originalH;
		$destAr = $destWidth / $destHeight;
		if (round($orgAr, 2) != round($destAr, 2)) {
			if ($orgAr > $destAr) {
				$destHeight = intval($destWidth / $orgAr);
			} else {
				$destWidth = intval($orgAr * $destHeight);
			}
		}

		$newResource = imagecreatetruecolor($destWidth, $destHeight);
		imagealphablending($newResource, false); // Overwrite alpha

		// bool imagecopyresampled ( resource dst_im, resource src_im, int dstX, int dstY, int srcX, int srcY, int dstW, int dstH, int srcW, int srcH)
		imagecopyresampled($newResource, $this->resource, 0, 0, $startX, $startY, $destWidth, $destHeight, $originalW, $originalH);
		imagedestroy($this->resource);
		$this->resource = $newResource;

		// reset sizes after croping
		$this->width = imagesx($newResource);
		$this->height = imagesy($newResource);
	}
	/**
	 * 
	 * @return int
	 */
	public function getWidth() {
		return $this->width;
	}
	/**
	 * 
	 * @return int
	 */
	public function getHeight() {
		return $this->height;
	}
	/**
	 * 
	 * @return resource
	 */
	public function getHandle() {
		return $this->resource;
	}
	/**
	 * 
	 */
	public function destroy() {
		if ($this->keepHandleAlive) return;
		@imagedestroy($this->resource);
	}
	/**
	 * 
	 */
	public function __destruct() {
		$this->destroy();
	}
}

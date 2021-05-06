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

use n2n\util\type\attrs\DataSet;
use n2n\io\img\ImageResource;
use n2n\io\img\ImageSource;

class ThumbCut implements \JsonSerializable {
	private $x;
	private $y;
	private $width;
	private $height;

	function __construct(int $x, int $y, int $width, int $height) {
		$this->x = $x;
		$this->y = $y;
		$this->width = $width;
		$this->height = $height;
	}
	
	/**
	 * @return int
	 */
	function getX() {
		return $this->x;
	}
	
	/**
	 * @return int
	 */
	function getY() {
		return $this->y;
	}
	
	/**
	 * @return int
	 */
	function getWidth() {
		return $this->width;
	}
	
	/**
	 * @return int
	 */
	function getHeight() {
		return $this->height;
	}

	/**
	 * @param ImageResource $imageResource
	 */
	function cut(ImageResource $imageResource) {
		$imageResource->crop($this->x, $this->y, $this->width, $this->height);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'x' => $this->x,
			'y' => $this->y,
			'width' => $this->width,
			'height' => $this->height
		];
	}
	
	/**
	 * @param ImageSource $imageSource
	 * @param ImageDimension $imageDimension
	 * @return \n2n\io\managed\img\ThumbCut
	 */
	static function auto(ImageSource $imageSource, ImageDimension $imageDimension) {
		$widthRatio = $imageSource->getWidth() / $imageDimension->getWidth();
		$heightRatio = $imageSource->getHeight() / $imageDimension->getHeight();
		
		$ratio = ($widthRatio > $heightRatio ? $heightRatio : $widthRatio);

		$width = $imageDimension->getWidth() * $ratio;
		$height = $imageDimension->getHeight() * $ratio;
		
		$x = max(0, ($imageSource->getWidth() - $width) / 2);
		$y = max(0, ($imageSource->getHeight() - $height) / 2);
		
		return new ThumbCut($x, $y, $width, $height);
	}
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return \n2n\io\managed\img\ThumbCut
	 */
	static function fromArray(array $data) {
		$ds = new DataSet($data);
		
		try {
			return new ThumbCut($ds->reqInt('x'), $ds->reqInt('y'), $ds->reqInt('width'), $ds->reqInt('height'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, 0, $e);
		}
	}
}

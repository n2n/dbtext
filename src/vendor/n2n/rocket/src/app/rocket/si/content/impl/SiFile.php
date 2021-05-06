<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\si\content\impl;

use n2n\io\managed\img\impl\ThSt;
use n2n\util\uri\Url;
use n2n\util\type\ArgUtils;
use n2n\io\managed\img\ThumbCut;

class SiFile implements \JsonSerializable {
	private $id;
	private $name;
	private $url;
	private $thumbUrl;
	private $mimeType;
	private $imageDimensions = [];
	
	/**
	 * @param string $name
	 * @param Url|null $url
	 */
	function __construct(\JsonSerializable $id, string $name, Url $url = null) {
		$this->id = $id;
		$this->name = $name;
		$this->url = $url;
	}
	
	/**
	 * @param \JsonSerializable $id
	 */
	function setId(\JsonSerializable $id) {
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return \JsonSerializable
	 */
	function getId() {
		return $this->id;
	}
		
	/**
	 * @return \n2n\util\uri\Url|null
	 */
	function getUrl() {
		return $this->url;
	}
	
	/**
	 * @param Url|null $url
	 */
	function setUrl(?Url $url) {
		$this->url = $url;
		return $this;
	}
	
	/**
	 * @return \n2n\util\uri\Url|null
	 */
	function getThumbUrl() {
		return $this->thumbUrl;
	}
	
	/**
	 * @param Url|null $thumbUrl
	 */
	function setThumbUrl(?Url $thumbUrl) {
		$this->thumbUrl = $thumbUrl;
		return $this;
	}
	
	function getMimeType() {
		return $this->mimeType;
	}
	
	function setMimeType(?string $mimeType) {
		$this->mimeType = $mimeType;
	}
	
	/**
	 * @return SiImageDimension[]
	 */
	function getImageDimensions() {
		return $this->imageDimensions;
	}
	
	/**
	 * @param SiImageDimension[] $imageDimensions
	 * @return \rocket\si\content\impl\SiFile
	 */
	function setImageDimensions(array $imageDimensions) {
		ArgUtils::valArray($imageDimensions, SiImageDimension::class);
		$this->imageDimensions = $imageDimensions;
		return $this;
	}
	
	function jsonSerialize() {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'url' => ($this->url !== null ? (string) $this->url : null),
			'thumbUrl' => ($this->thumbUrl !== null ? (string) $this->thumbUrl : null),
			'mimeType' => $this->mimeType,
			'imageDimensions' => $this->imageDimensions
		];
	}
	
	/**
	 * @return \n2n\io\managed\img\ThumbStrategy
	 */
	static function getThumbStrategy() {
		return ThSt::crop(40, 30, true);
	}
}

class SiImageDimension implements \JsonSerializable {
	private $id;
	private $name;
	private $width;
	private $height;
	private $ratioFixed;
	private $thumbCut;
	private $exists;
	
	function __construct(string $id, ?string $name, int $width, int $height, bool $ratioFixed, ThumbCut $thumbCut, bool $exists) {
		$this->id = $id;
		$this->name = $name;
		$this->width = $width;
		$this->height = $height;
		$this->ratioFixed = $ratioFixed;
		$this->thumbCut = $thumbCut;
		$this->exists = $exists;
	}
	
	function getId() {
		return $this->id;
	}
	
	function setId(string $id) {
		$this->id = $id;
	}
	
	/**
	 * @param ThumbCut $thumbCut
	 */
	function setThumbCut(ThumbCut $thumbCut) {
		$this->thumbCut = $thumbCut;
	}
	
	/**
	 * @return \n2n\io\managed\img\ThumbCut
	 */
	function getThumbCut() {
		return $this->thumbCut;
	}
	
	function jsonSerialize() {
		$imageCutData = $this->thumbCut->jsonSerialize();
		$imageCutData['exists'] = $this->exists; 
		
		return [
			'id' => $this->id,
			'name' => $this->name,
			'width' => $this->width,
			'height' => $this->height,
			'ratioFixed' => $this->ratioFixed,
			'imageCut' => $imageCutData
		];
	}
}
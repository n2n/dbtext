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
namespace n2n\io\img\impl;

use n2n\io\img\ImageResource;
use n2n\io\IoUtils;
use n2n\io\img\ImageSource;

class WebpFileImageSource extends FsImageSourceAdapter implements ImageSource {	

	public function __construct($fileName) {
		parent::__construct($fileName);
	}
	
	public function getMimeType(): string {
		return ImageSourceFactory::MIME_TYPE_WEBP;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see n2n\io\img.ImageFileWrapper::createResource()
	 */
	public function createImageResource($keepHandleAlive = false): ImageResource {
		return new ImageResource(IoUtils::imageCreateFromWebp($this->filePath), $keepHandleAlive);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see n2n\io\img.ImageFileWrapper::saveImageResource()
	 */
	public function saveImageResource(ImageResource $imageResource) {
		IoUtils::imageWebp($imageResource->getHandle(), $this->getFilePath(), 80);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\img\ImageFileWrapper::calcResourceMemorySize()
	 */
	public function calcResourceMemorySize(): int {
		$imageData = getimagesize($this->filePath);
		$mb = 1048576;  // number of bytes in 1M
		$k64 = 65536;	// number of bytes in 64K
		$tweakfactor = 1.62;
		return round(( $imageData[0] * $imageData[1] * $imageData['bits'] / 8 + $k64) * $tweakfactor);
	}
}

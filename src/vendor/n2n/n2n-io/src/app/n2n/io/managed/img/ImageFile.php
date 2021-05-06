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

use n2n\io\managed\File;
use n2n\io\img\ImageResource;
use n2n\io\managed\impl\CommonFile;
use n2n\io\managed\FileInfo;

class ImageFile {	
	private $file;
	private $imageSource;
	private $thumbCut;
	
	/**
	 * @param File $file 
	 * @throws \n2n\util\ex\IllegalStateException if {@link FileSource} is disposed ({@link self::isValid()}).
	 * @throws \n2n\io\img\UnsupportedImageTypeException if {@link self::isImage()} returns false.
	 */
	function __construct(File $file, ThumbCut $thumbCut = null) {
		$this->file = $file;
		$this->imageSource = $this->file->getFileSource()->createImageSource();
		$this->thumbCut = $thumbCut;
	}
	
	/**
	 * @return File
	 */
	function getFile() {
		return $this->file;
	}
	
	/**
	 * @return \n2n\io\img\ImageSource
	 */
	function getImageSource() {
		return $this->imageSource;
	}
	
	function getWidth() {
		return $this->imageSource->getWidth();
	}
	
	function getHeight() {
		return $this->imageSource->getHeight();
	}
	
	
	public function crop($x, $y, $width, $height) {
		$imageResource = $this->imageSource->createImageResource();
		$imageResource->crop($x, $y, $width, $height);
		$this->imageSource->saveImageResource($imageResource);
		$imageResource->destroy();
	}
	
	public function resize($width, $height){
		$imageResource = $this->imageSource->createImageResource();
		$imageResource->proportionalResize($width, $height);
		$this->imageSource->saveImageResource($imageResource);
		$imageResource->destroy();
	}
	
	public function proportionalResize($width, $height, $cropAllowed = false) {
		$imageResource = $this->imageSource->createImageResource();
		$imageResource->proportionalResize($width, $height,
				($cropAllowed ? ImageResource::AUTO_CROP_MODE_CENTER : null));
		$this->imageSource->saveImageResource($imageResource);
		$imageResource->destroy();
	}
	
	function watermark(ImageResource $watermark, $watermarkPos = 4, $watermarkMargin = 10) {
		$imageResource = $this->imageSource->createImageResource();
		$imageResource->watermark($watermark, $watermarkPos, $watermarkMargin);
		$this->imageSource->saveImageResource($imageResource);
		$imageResource->destroy();
	}
	
	function getThumbFile(ImageDimension $imageDimension) {
		$thumbEngine = $this->file->getFileSource()->getAffiliationEngine()->getThumbManager();
		
		if (null !== ($thumbFileResource = $thumbEngine->getByDimension($imageDimension))) {
			return new CommonFile($thumbFileResource, $this->file->getOriginalName());
		}
		
		return null;
	}
	
	function setThumbCut(ImageDimension $imageDimension, ThumbCut $thumbCut) {
		$fileInfo = $this->file->getFileSource()->readFileInfo(); 
		
		$data = $fileInfo->getCustomInfo(ImageFile::class);
		if (!isset($data['thumbCuts'])) {
			$data['thumbCuts'] = [];
		}
		$data['thumbCuts'][(string) $imageDimension] = $thumbCut;
		$fileInfo->setCustomInfo(ImageFile::class, $data);
		
		$this->file->getFileSource()->writeFileInfo($fileInfo);
	}
	
	/**
	 * @param ImageDimension $imageDimension
	 * @return ThumbCut|null
	 */
	function getThumbCut(ImageDimension $imageDimension) {
		$imgDimStr = (string) $imageDimension;
		$fileInfo = $this->file->getFileSource()->readFileInfo();
		$data = $fileInfo->getCustomInfo(ImageFile::class);
		
		if ($data === null || !isset($data['thumbCuts'][$imgDimStr])) {
			return null;
		}
		
		try {
			return ThumbCut::fromArray($data['thumbCuts'][$imgDimStr]);
		} catch (\InvalidArgumentException $e) {
			return null;
		}
	}

	function removeThumbCut(ImageDimension $imageDimension) {
		$fileInfo = $this->file->getFileSource()->readFileInfo();
		
		$data = $fileInfo->getCustomInfo(ImageFile::class);
		if (!isset($data['thumbCuts'])) {
			$data['thumbCuts'] = [];
		}
		unset($data['thumbCuts'][(string) $imageDimension]);
		$fileInfo->setCustomInfo(ImageFile::class, $data);
		
		$this->file->getFileSource()->writeFileInfo($fileInfo);
	}

	function removeThumbCuts() {
		$fileInfo = $this->file->getFileSource()->readFileInfo();
		$fileInfo->removeCustomInfo(ImageFile::class);
		$this->file->getFileSource()->writeFileInfo($fileInfo);
	}

	function createThumbFile(ImageDimension $imageDimension, ImageResource $imageResource): File {
		$thumbFileSource = $this->file->getFileSource()->getThumbManager()->create($imageResource, $imageDimension);
		return new CommonFile($thumbFileSource, $this->file->getOriginalName());
	}

	function removeThumbFile(ImageDimension $imageDimension) {
		$this->file->getFileSource()->getAffiliationEngine()->getThumbManager()->remove($imageDimension);
	}

	/**
	 * @return ImageFile
	 */
	function getOrCreateThumb(ThumbStrategy $thumbStrategy): ImageFile {
		$thumbEngine = $this->file->getFileSource()->getAffiliationEngine()->getThumbManager();
		$imageDimension = $thumbStrategy->getImageDimension();
		
		$thumbCut = $this->getThumbCut($imageDimension);
		$thumbFileResource = $thumbEngine->getByDimension($imageDimension);
		if ($thumbFileResource !== null) {
			return new ImageFile(new CommonFile($thumbFileResource, $this->file->getOriginalName()), $thumbCut);
		}
		
		if ($thumbStrategy->matches($this->imageSource)) {
			return $this;
		}
		
		$imageResource = $this->imageSource->createImageResource();
		
		if (null !== $thumbCut) {
			$thumbCut->cut($imageResource);
			$thumbStrategy->resize($imageResource);
		} else {
			$thumbCut = $thumbStrategy->resize($imageResource);
			$this->setThumbCut($thumbStrategy->getImageDimension(), $thumbCut);
		}
		
		$thumbFileResource = $thumbEngine->create($imageResource, $imageDimension);
		$imageResource->destroy();
		
		return new ImageFile(new CommonFile($thumbFileResource, $this->file->getOriginalName()), $thumbCut);
	}
	
	function createVariationFile(ImageDimension $imageDimension, ImageResource $imageResource): File {
		$variationManager = $this->file->getFileSource()->getAffiliationEngine()->getVariationManager();
		$variationFileResource = $variationManager->createImage($imageDimension, $imageResource);
		
		return new CommonFile($variationFileResource, $this->file->getOriginalName());
	}
	
	function getOrCreateVariation(ThumbStrategy $thumbStrategy): ImageFile {
		$variationManager = $this->file->getFileSource()->getAffiliationEngine()->getVariationManager();
		$imageDimension = $thumbStrategy->getImageDimension();

		$variationFileResource = $variationManager->getByKey($imageDimension);
		if ($variationFileResource !== null) {
			return new ImageFile(new CommonFile($variationFileResource, $this->file->getOriginalName()));
		}

		if ($thumbStrategy->matches($this->imageSource)) {
			return $this;
		}

		$imageResource = null;
		
		$origFileSource = $this->file->getFileSource()->getOriginalFileSource();
		if ($origFileSource === null || $this->thumbCut === null) {
			$imageResource = $this->imageSource->createImageResource();
		} else {
			$imageResource = $origFileSource->createImageSource()->createImageResource();
			$this->thumbCut->cut($imageResource);
		}

		$thumbStrategy->resize($imageResource);

		$variationFileResource = $variationManager->createImage($imageDimension, $imageResource);
		$imageResource->destroy();

		return new ImageFile(new CommonFile($variationFileResource, $this->file->getOriginalName()));
	}
	
	/**
	 * 
	 * @param ImageDimension $imageDimension
	 * @return \n2n\io\managed\impl\CommonFile|null
	 */
	function getVariationFile(ImageDimension $imageDimension) {
		$variationManager = $this->file->getFileSource()->getAffiliationEngine()->getVariationManager();
		
		if (null !== ($variationFileResource = $variationManager->getByKey($imageDimension))) {
			return new CommonFile($variationFileResource, $this->file->getOriginalName());
		}
		
		return null;
	}
	
	/**
	 * @deprecated use {@link self::getVariationImageDimensions()}
	 * @return \n2n\io\managed\img\ImageDimension[]
	 */
	function getVariationImageDimension() {
		return $this->getVariationImageDimensions();
	}
	
	/**
	 * @return \n2n\io\managed\img\ImageDimension[]
	 */
	function getVariationImageDimensions() {
		$variationManager = $this->file->getFileSource()->getAffiliationEngine()->getVariationManager();
		
		$imageDimensions = array();
		foreach ($variationManager->getAllKeys() as $key) {
			try {
				$imageDimensions[] = ImageDimension::createFromString($key);
			} catch (\InvalidArgumentException $e) {}
		}
		
		return $imageDimensions;
	}
}



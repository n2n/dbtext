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
namespace rocket\impl\ei\component\prop\file\command\model;

use n2n\impl\web\dispatch\map\val\ValEnum;
use n2n\impl\web\dispatch\map\val\ValNumeric;
use n2n\io\managed\img\ImageFile;
use n2n\web\dispatch\Dispatchable;
use n2n\util\StringUtils;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\io\managed\img\ImageDimension;
use n2n\util\type\ArgUtils;
use n2n\impl\web\dispatch\map\val\ValNotEmpty;

class ThumbModel implements Dispatchable{
	private $imageFile;
	private $imageDimensions;
	private $groupedImageDimensionOptions;	
	private $ratiosOptions;
	/**
	 * @var ImageDimension []
	 */
	private $largestDimensions;
	
	public $selectedStr;
	public $x;
	public $y;
	public $width;
	public $height;
	public $keepAspectRatio = true;
	
	/**
	 * @param ImageFile $imageFile
	 * @param ImageDimension[] $imageDimensions
	 */
	public function __construct(ImageFile $imageFile, array $imageDimensions) {
		ArgUtils::valArray($imageDimensions, ImageDimension::class, false, 'imageDimensions');
		
		$this->imageFile = $imageFile;
		
// 		for ($i = 1; $i < 5; $i++) {
// 			$factor = $i * 10;
// 			$imageDimension = new ImageDimension(16 * $factor, 9 * $factor);
// 			$imageDimensions[$imageDimension->__toString()] = $imageDimension;
// 			$imageDimension = new ImageDimension(4 * $factor, 3 * $factor);
// 			$imageDimensions[$imageDimension->__toString()] = $imageDimension;
// 			$imageDimension = new ImageDimension(3 * $factor, 4 * $factor);
// 			$imageDimensions[$imageDimension->__toString()] = $imageDimension;
// 		}
		$this->imageDimensions = $imageDimensions;
		
		$this->groupedImageDimensionOptions = [];
		$this->ratiosOptions = [];
		$this->largestDimensions = [];
		
		foreach ($imageDimensions as $imageDimensionString => $imageDimension) {
			$ratio = ThumbRatio::create($imageDimension);
			$ratioStr = $ratio->__toString();
			if (!isset($this->ratiosOptions[$ratioStr])) {
				$this->ratiosOptions[$ratioStr] = $ratio->buildLabel();
				$this->groupedImageDimensionOptions[$ratioStr] = [];
				$this->largestDimensions[$ratioStr] = $imageDimension;
			} else {
				if ($this->largestDimensions[$ratioStr]->getHeight() < $imageDimension->getHeight()) {
					$this->largestDimensions[$ratioStr] = $imageDimension;
				}
			}
			
			$idExt = $imageDimension->getIdExt();
			$this->groupedImageDimensionOptions[$ratioStr][$imageDimensionString] = $imageDimension->getWidth() 
					. ' x ' . $imageDimension->getHeight() 
					. ($idExt !== null ? ' (' . StringUtils::pretty($idExt) . ')' : '');
		}
	}
	
	public function getImageDimensionOptions($ratioStr) {
		if (!isset($this->groupedImageDimensionOptions[$ratioStr])) return [];
		
		return $this->groupedImageDimensionOptions[$ratioStr];
	}
	
	public function getLargestDimension($ratioStr) {
		if (!isset($this->largestDimensions[$ratioStr])) return null;
		
		return $this->largestDimensions[$ratioStr];
	}
	
	public function getRatioOptions() {
		return $this->ratiosOptions;
	}
	
	public function getImageFile(): ImageFile {
		return $this->imageFile;
	}
	
	public function getImageDimension($imageDimensionStr) {
		if (!isset($this->imageDimensions[$imageDimensionStr])) return null;
		
		return $this->imageDimensions[$imageDimensionStr];
	}
	
	private function _validation(BindingDefinition $bd) {
		$bd->val(array('x', 'y', 'width', 'height'), new ValNumeric(null, 0), new ValNotEmpty());
		$bd->val('selectedStr', new ValEnum(array_merge(array_keys($this->imageDimensions), array_keys($this->ratiosOptions))));
	}
	
	public function save() {
		$imageDimensions = [];
		if (isset($this->groupedImageDimensionOptions[$this->selectedStr])) {
			foreach (array_keys($this->groupedImageDimensionOptions[$this->selectedStr]) as $imageDimensionStr) {
				$imageDimensions[] = $this->imageDimensions[$imageDimensionStr];
			}
		} else if (isset($this->imageDimensions[$this->selectedStr])) {
			$imageDimensions[] = $this->imageDimensions[$this->selectedStr];
		}
		
		foreach ($imageDimensions as $imageDimension) {
			$imageResource = $this->imageFile->getImageSource()->createImageResource();
			$imageResource->crop($this->x, $this->y, $this->width, $this->height);
			$imageResource->proportionalResize($imageDimension->getWidth(), $imageDimension->getHeight()/*, 
					ImageResource::AUTO_CROP_MODE_CENTER*/);
			$thumbImageFile = new ImageFile($this->imageFile->createThumbFile($imageDimension, $imageResource));
			$imageResource->destroy();
			
			foreach ($thumbImageFile->getVariationImageDimension() as $imageDimension) {
				$imageResource = $thumbImageFile->getImageSource()->createImageResource();
				$imageResource->proportionalResize($imageDimension->getWidth(), $imageDimension->getHeight()/*, 
						ImageResource::AUTO_CROP_MODE_CENTER*/);
				$thumbImageFile->createVariationFile($imageDimension, $imageResource);
				$imageResource->destroy();
			}
		}
	}
}

// if (isset($_POST['formatbreite']) && isset($_POST['formathoehe']) && isset($_POST['xwert'])
// 		&& isset($_POST['ywert']) && isset($_POST['ausschnittbreite']) && isset($_POST['ausschnitthoehe'])
// 		&& isset($_POST['anschneiden'])
// ) {
// 	$dimension = $this->getDimension($imageEiProp, $_POST['formatbreite'], $_POST['formathoehe']);
// 	if (!$dimension) {
// 		$mc->addError($text->get('err_image_resize_invalid_format',
// 				array('width' => $_POST['formatbreite'], 'height' => $_POST['formathoehe'])));
// 	} else {
// 		// @todo: @_POST['anschneiden'] has to be inverted, in order to give correct result!
// 		$resizeModel->updateThumb($dimension, $_POST['xwert'], $_POST['ywert'], $_POST['ausschnittbreite'],
// 				$_POST['ausschnitthoehe'], !(boolean) $_POST['anschneiden']);
	
// 		$mc->addInfo($text->get('image_resize_thumb_created',
// 				array('width' => $dimension->getWidth(), 'height' => $dimension->getHeight())));

// 	}
// }

// public function updateThumb(NN6FileImageDimension $dimension, $x, $y, $width, $height, $crop) {
// 	$fileManager = $this->image->getFileManager();

// 	$endWidth = $dimension->getWidth();
// 	$endHeight = $dimension->getHeight();
// 	$crop = ($dimension->getCrop() || $crop);
// 	$imageResource = $this->image->createResource();
// 	$imageResource->crop($x, $y, $width, $height);
// 	$imageResource->resize($endWidth, $endHeight, $crop);

// 	$fileManager->setImageResFromResource($this->image, $dimension, $imageResource);
// 	$imageResource->destroy();
// }

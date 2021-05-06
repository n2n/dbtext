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
namespace rocket\impl\ei\component\prop\file\conf;

use rocket\impl\ei\component\prop\file\FileEiProp;
use n2n\impl\web\dispatch\mag\model\StringArrayMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\impl\ei\component\prop\file\command\ThumbEiCommand;
use n2n\persistence\meta\structure\Column;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\io\managed\img\ImageDimension;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use rocket\ei\manage\generic\UnknownScalarEiPropertyException;
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\file\command\MultiUploadEiCommand;
use rocket\impl\ei\component\prop\file\command\controller\MultiUploadEiController;
use n2n\io\img\impl\ImageSourceFactory;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use n2n\util\type\attrs\AttributesException;
use rocket\ei\util\Eiu;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\DataSet;
use n2n\config\InvalidConfigurationException;
use n2n\io\IoUtils;
use n2n\util\type\CastUtils;
use n2n\io\orm\ManagedFileEntityProperty;
use n2n\impl\web\dispatch\mag\model\MagCollectionArrayMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\MagForm;

class FileConfig extends PropConfigAdaption {
	const ATTR_IMAGE_RECOGNIZED_KEY = 'imageRecognized';
	
	const ATTR_ALLOWED_EXTENSIONS_KEY = 'allowedExtensions';
	const ATTR_ALLOWED_MIME_TYPES_KEY = 'allowedMimeTypes';
	
	const ATTR_MAX_SIZE_KEY = 'maxSize';
	
	const ATTR_DIMENSION_IMPORT_MODE_KEY = 'dimensionImportMode';
	
	const ATTR_EXTRA_THUMB_DIMENSIONS_KEY = 'extraThumbDimensions';
	
	const ATTR_MULTI_UPLOAD_AVAILABLE_KEY = 'multiUploadAvailable';
	
	const ATTR_MULTI_UPLOAD_NAMING_EI_PROP_PATH_KEY = 'multiUploadNamingProp';
	
	const ATTR_MULTI_UPLOAD_ORDER_KEY = 'multiUploadOrder'; 
	
	const ATTR_ID_EXT_NAMES_KEY = 'idExtNames';
	
	private $fileModel;
	/**
	 * @var ThumbResolver
	 */
	private $thumbResolver;
	/**
	 * @var FileVerificator
	 */
	private $fileVerificator;
	
	public function __construct(FileModel $fileModel, ThumbResolver $thumbResolver, FileVerificator $fileVerificator) {
		$this->fileModel = $fileModel;
		$this->thumbResolver = $thumbResolver;
		$this->fileVerificator = $fileVerificator;
	}
	
	public function setup(Eiu $eiu, DataSet $dataSet) {
		$this->fileVerificator->setAllowedExtensions(
				$dataSet->optScalarArray(self::ATTR_ALLOWED_EXTENSIONS_KEY));
		
		$this->fileVerificator->setAllowedMimeTypes(
				$dataSet->optScalarArray(self::ATTR_ALLOWED_MIME_TYPES_KEY));
		
		$maxSizeStr = $dataSet->optString(self::ATTR_MAX_SIZE_KEY);
		if ($maxSizeStr !== null) {
			$this->fileVerificator->setMaxSize(IoUtils::parsePhpIniSize($maxSizeStr));
		}
		
		$this->fileVerificator->setImageRecognized(
				$dataSet->optBool(self::ATTR_IMAGE_RECOGNIZED_KEY, true));
		
		$this->thumbResolver->setImageDimensionImportMode(
				$dataSet->optEnum(self::ATTR_DIMENSION_IMPORT_MODE_KEY, 
						ThumbResolver::getImageDimensionImportModes()));
		
		if ($dataSet->contains(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY)) {
			$this->setupExtraImageDimensions($dataSet);
		}
		
		$thumbEiCommand = new ThumbEiCommand();
		$eiu->mask()->supremeMask()->addEiCommand($thumbEiCommand);
		$this->thumbResolver->setThumbEiCommand($thumbEiCommand);
		
		if ($dataSet->optBool(self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, false)) {
			$this->setupMulti($eiu, $dataSet);
		}
		
		$entityProperty = $this->getPropertyAssignation()->getEntityProperty(true);
		CastUtils::assertTrue($entityProperty instanceof ManagedFileEntityProperty);
		$this->thumbResolver->setTargetFileManager($eiu->lookup($entityProperty->getFileManagerClassName()));
		$this->thumbResolver->setTargetFileLocator($entityProperty->getFileLocator());
	}
	
	private function setupExtraImageDimensions(DataSet $dataSet) {
		$extraImageDimensions = array();
		
		foreach ($dataSet->reqScalarArray(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY) as $imageDimensionStr) {
			try {
				$extraImageDimensions[$imageDimensionStr] = ImageDimension::createFromString($imageDimensionStr);
			} catch (\InvalidArgumentException $e) {
				throw new InvalidConfigurationException('Invalid ImageDimension string: ' . $imageDimensionStr, $e);
			}
		}
		
		$this->thumbResolver->setExtraImageDimensions($extraImageDimensions);
	}
	
	private function setupMulti(Eiu $eiu, DataSet $dataSet) {
		$eiuMask = $eiu->mask();
		
		$multiUploadEiCommand = new MultiUploadEiCommand($this->fileModel, null,
				$dataSet->getString(self::ATTR_MULTI_UPLOAD_ORDER_KEY, false, MultiUploadEiController::ORDER_NONE, true));
		$eiuMask->addEiCommand($multiUploadEiCommand);
		
		if ($dataSet->contains(self::ATTR_MULTI_UPLOAD_NAMING_EI_PROP_PATH_KEY)) {
			$fileModel = $this->fileModel;
			$eiuMask->onEngineReady(function (EiuEngine $eiuEngine) use ($fileModel, $dataSet) {
				try {
					$fileModel->setNamingEiPropPath($eiuEngine
							->getScalarEiProperty($dataSet->reqString(self::ATTR_MULTI_UPLOAD_NAMING_EI_PROP_PATH_KEY))
							->getEiPropPath());
				} catch (\InvalidArgumentException $e) {
					throw new InvalidConfigurationException('Invalid base ScalarEiProperty configured.', $e);
				} catch (UnknownScalarEiPropertyException $e) {
					throw new InvalidConfigurationException('Configured base ScalarEiProperty not found.', $e);
				}
			});
		}
	}
	
	public function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		if (false !== stripos($this->requirePropertyName(), 'image')) {
			$dataSet->set(self::ATTR_ALLOWED_MIME_TYPES_KEY, ImageSourceFactory::getSupportedMimeTypes());
			$dataSet->set(self::ATTR_DIMENSION_IMPORT_MODE_KEY, ThumbResolver::DIM_IMPORT_MODE_ALL);
		}
	}
	
	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($dataSet);
				
		$magCollection->addMag(self::ATTR_ALLOWED_EXTENSIONS_KEY, new StringArrayMag('Allowed Extensions', 
				$lar->getScalarArray(self::ATTR_ALLOWED_EXTENSIONS_KEY, $this->fileVerificator->getAllowedExtensions()), 
				false));
		
		$magCollection->addMag(self::ATTR_ALLOWED_MIME_TYPES_KEY, new StringArrayMag('Allowed Mime Types',
				$lar->getScalarArray(self::ATTR_ALLOWED_MIME_TYPES_KEY, $this->fileVerificator->getAllowedMimeTypes()),
				false));
		
		$magCollection->addMag(self::ATTR_DIMENSION_IMPORT_MODE_KEY, new EnumMag('Dimensions import mode', 
				array(ThumbResolver::DIM_IMPORT_MODE_ALL => 'All possible dimensions',
						ThumbResolver::DIM_IMPORT_MODE_USED_ONLY => 'Only for current image used dimensions'),
				$lar->getString(self::ATTR_DIMENSION_IMPORT_MODE_KEY, 
						$this->thumbResolver->getImageDimensionImportMode())));
		
		$extraImageDimensionStrs = array();
		if ($lar->contains(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY)) {
			$extraImageDimensionStrs = $lar->getScalarArray(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY);
		} else {
			foreach ((array) $this->thumbResolver->getExtraImageDimensions() as $extraImageDimension) {
				$extraImageDimensionStrs[] = (string) $extraImageDimension;
			}
		}
		$magCollection->addMag(self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY, new StringArrayMag('Extra Thumb Dimensions', 
				$extraImageDimensionStrs, false));
		
		$magCollection->addMag(self::ATTR_IMAGE_RECOGNIZED_KEY, new BoolMag('Check Image Resource Memory',
				$lar->getBool(self::ATTR_IMAGE_RECOGNIZED_KEY, $this->fileVerificator->isImageRecognized())));
		
		$enablerMag = new TogglerMag('Multi upload',
				$lar->getBool(self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, false));
		$magCollection->addMag(self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY, $enablerMag);
		
		if ($eiu->mask()->isEngineReady()) {
			$namingMag = new EnumMag('Naming Field', $eiu->engine()->getScalarEiPropertyOptions(),
					$lar->getString(self::ATTR_MULTI_UPLOAD_NAMING_EI_PROP_PATH_KEY));
			$sortMag = new EnumMag('Upload Order', $this->getMultiUploadSortOptions(),
					$lar->getString(self::ATTR_MULTI_UPLOAD_ORDER_KEY));
			
			$enablerMag->setOnAssociatedMagWrappers([
				$magCollection->addMag(self::ATTR_MULTI_UPLOAD_NAMING_EI_PROP_PATH_KEY, $namingMag),
				$magCollection->addMag(self::ATTR_MULTI_UPLOAD_ORDER_KEY, $sortMag)
			]);
		}
		
		$idExtNamesMag = new MagCollectionArrayMag('Id Ext Names', function () {
			$magCollection = new MagCollection();
			$magCollection->addMag('idExt', new StringMag('Id Ext'));
			$magCollection->addMag('name', new StringMag('Title'));
			return new MagForm($magCollection);
		});
		$idExtNamesMag->setValue($lar->getArray(self::ATTR_ID_EXT_NAMES_KEY));
		$magCollection->addMag(self::ATTR_ID_EXT_NAMES_KEY, $idExtNamesMag);
	}
	
	private function readIdExtNames($key, LenientAttributeReader $lar): array {
		$idExtNames = [];
		foreach ($lar->getArray(self::ATTR_ID_EXT_NAMES_KEY) as $huii) {
			
		}
		return $idExtNames;
	}
	
	
	private function getMultiUploadSortOptions() {
		return array(MultiUploadEiController::ORDER_NONE => 'None',
				MultiUploadEiController::ORDER_FILE_NAME_ASC => 'Filename Ascending',
				MultiUploadEiController::ORDER_FILE_NAME_DESC => 'Filename Descending');
	}
	
	// 	private function getNamingEiPropIdOptions() {
	// 		$namingEiPropIdOptions = array();
	// 		foreach ($this->eiComponent->getEiMask()->getEiEngine()->getScalarEiDefinition()->getMap()
	// 				as $id => $genericScalarProperty) {
	// 			if ($id === $this->eiComponent->getId()) continue;
	// 			$namingEiPropIdOptions[$id] = (string) $genericScalarProperty->getLabelLstr();
	// 		}
	// 		return $namingEiPropIdOptions;
	// 	}
	
	public function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$curEiPropPath = null;
		try {
			if (null !== ($val = $dataSet->optString(self::ATTR_MULTI_UPLOAD_NAMING_EI_PROP_PATH_KEY))) {
				$curEiPropPath = EiPropPath::create($val);
			}
		} catch (AttributesException $e) {
		} catch (\InvalidArgumentException $e) {
		}
		
		$dataSet->appendAll($magCollection->readValues(array(
				self::ATTR_ALLOWED_EXTENSIONS_KEY, self::ATTR_ALLOWED_MIME_TYPES_KEY, 
				self::ATTR_DIMENSION_IMPORT_MODE_KEY, self::ATTR_EXTRA_THUMB_DIMENSIONS_KEY, 
				self::ATTR_IMAGE_RECOGNIZED_KEY, self::ATTR_MULTI_UPLOAD_AVAILABLE_KEY,
				self::ATTR_ID_EXT_NAMES_KEY), true), true);
		
		if ($magCollection->containsPropertyName(self::ATTR_MULTI_UPLOAD_NAMING_EI_PROP_PATH_KEY)) {
			$dataSet->appendAll($magCollection
					->readValues(array(self::ATTR_MULTI_UPLOAD_NAMING_EI_PROP_PATH_KEY, self::ATTR_MULTI_UPLOAD_ORDER_KEY), true), true);
		} else if ($curEiPropPath !== null) {
			$dataSet->set(self::ATTR_MULTI_UPLOAD_NAMING_EI_PROP_PATH_KEY, (string) $curEiPropPath);
		}
	}
	
	
}

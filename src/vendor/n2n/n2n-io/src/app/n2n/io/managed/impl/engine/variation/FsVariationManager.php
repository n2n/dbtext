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
namespace n2n\io\managed\impl\engine\variation;

use n2n\io\img\ImageResource;
use n2n\io\img\impl\ImageSourceFactory;
use n2n\io\fs\FsPath;
use n2n\io\managed\FileManagingException;
use n2n\io\managed\FileSource;
use n2n\io\managed\VariationManager;
use n2n\io\managed\impl\engine\FileSourceAdapter;
use n2n\io\managed\impl\engine\QualifiedNameBuilder;

class FsVariationManager implements VariationManager {
	const KEY_PREFIX = 'var-';

	private $fileSource;
	private $dirPerm;
	private $filePerm;
	
	public function __construct(FileSourceAdapter $fileSource, string $dirPerm, string $filePerm) {
		$this->fileSource = $fileSource;
		$this->dirPerm = $dirPerm;
		$this->filePerm = $filePerm;
	}
	
	public static function keyToDirName(string $key) {
		try {
			return QualifiedNameBuilder::buildResFolderName(self::KEY_PREFIX . $key);
		} catch (\InvalidArgumentException $e) {
			throw new FileManagingException('Failed to use variation key: ' . $key, 0, $e);
		}
	}
	
	public static function dirNameToKey(string $dirName) {
		$resDirName = null;
		try {
			$resDirName = QualifiedNameBuilder::parseResName($dirName);
		} catch (\InvalidArgumentException $e) {
			throw new FileManagingException('Failed to determine variation key of dir name: ' . $dirName, 0, $e);
		}
		
		$prefixLength = mb_strlen(self::KEY_PREFIX);
		if (self::KEY_PREFIX !== mb_substr($resDirName, 0, $prefixLength)) {
			return null;
		}
		
		return mb_substr($resDirName, $prefixLength);
	}
	
// 	public static function isDimensionDirName(string $dirName): bool {
// 		return QualifiedNameBuilder::isResDirName($dirName);
// 	}
	
// 	public static function dirNameToDimension(string $dirName): ImageDimension {
// 		if (null !== ($resName = QualifiedNameBuilder::parseResName($dirName))) {
// 			return ImageDimension::createFromString($resName);
// 		}
		
// 		return null;
// 	}
	
// 	private function dirNameToDimension($dirName) {
// 		$dimAttrs = explode(self::THUMB_FOLDER_ATTRIBUTE_SEPARATOR,
// 				mb_substr($dirName, mb_strlen(self::THUMB_FOLDER_PREFIX)));
	
// 		if (sizeof($dimAttrs) < 3) {
// 			throw new \InvalidArgumentException();
// 		}
	
// 		return new ImageDimension((int) $dimAttrs[0], (int) $dimAttrs[1], (boolean)$dimAttrs[2]);
// 	}
	
	private function createVariationFileFsPath(string $key) {
		$fsPath = $this->fileSource->getFileFsPath();
		return $fsPath->getParent()->ext(self::keyToDirName($key))->ext($fsPath->getName());
	}
	
	private function createVariationFileSource(FsPath $fileFsPath, string $key) {
		$thumbFileSource = new FsAffiliationFileSource($fileFsPath);
		if ($this->fileSource->isHttpaccessible()) {
			$fileUrl = $this->fileSource->getUrl();
			$thumbUrl = $fileUrl->chPath($fileUrl->getPath()->getParent()->ext($fileFsPath->getParent()->getName(), $fileFsPath->getName()));
			$thumbFileSource->setUrl($thumbUrl);
		}
		
		$thumbFileSource->setAffiliationEngine($this->fileSource->getAffiliationEngine());
		
		return $thumbFileSource;
	}
	
	public function getByKey(string $key): ?FileSource {
		$fileFsPath = $this->createVariationFileFsPath($key);
		if ($fileFsPath->exists()) {
			return $this->createVariationFileSource($fileFsPath, $key);
		}
		return null;
	}
	
	public function createImage(string $key, ImageResource $imageResource): FileSource {
		$fileFsPath = $this->createVariationFileFsPath($key);
		$fileFsPath->mkdirsAndCreateFile($this->dirPerm, $this->filePerm);	
		
		ImageSourceFactory::createFromFileName($fileFsPath, $this->fileSource->getMimeType())->saveImageResource($imageResource);
				
		return $this->createVariationFileSource($fileFsPath, $key);
	}
	
	public function create(string $key): FileSource {
		$fileFsPath = $this->createVariationFileFsPath($key);
		$fileFsPath->mkdirsAndCreateFile($this->dirPerm, $this->filePerm);
		
		return $this->createVariationFileSource($fileFsPath, $key);
	}
	
// 	/**
// 	 * @return \n2n\io\managed\img\ImageDimension[]
// 	 */
// 	public function getPossibleImageDimensions(): array {
// 		$imageDimensions = array();
// 		foreach ($this->fileSource->getFileFsPath()->getParent()
// 				->getChildren(QualifiedNameBuilder::RES_FOLDER_PREFIX . '*') as $thumbFsPath) {
// 			$imageDimensions[] = self::dirNameToDimension($thumbFsPath->getName());
// 		}
// 		return $imageDimensions; 
// 	}
	
// 	/**
// 	 * @return \n2n\io\managed\img\ImageDimension[]
// 	 */
// 	public function getUsedImageDimensions(): array {
// 		$imageDimensions = array();
// 		foreach ($this->findThumbFsPaths() as $thumbFsPath) {
// 			$imageDimensions[] = self::dirNameToDimension($thumbFsPath->getParent()->getName());
// 		}
// 		return $imageDimensions;
// 	}
	
	private function findVariationFsPaths() {
		$fsPath = $this->fileSource->getFileFsPath();
		return $fsPath->getParent()->getChildren(QualifiedNameBuilder::RES_FOLDER_PREFIX . self::KEY_PREFIX
				. '*' . DIRECTORY_SEPARATOR . $fsPath->getName());
	}
	
	public function clear() {
// 		$fsPath = $this->fileSource->getFileFsPath();
		
		foreach ($this->findVariationFsPaths() as $filePath) {
			$filePath->delete();
			
			if ($filePath->getParent()->isEmpty()) {
				$filePath->getParent()->delete();
			}
		}
	}
	
	public function getAll() {
		$variationFileSources = array();
		foreach ($this->findVariationFsPaths() as $fileFsPath) {
			$key = self::dirNameToKey($fileFsPath->getParent()->getName());
			if ($key === null) continue;
			$variationFileSources[$key] = $this->createVariationFileSource($fileFsPath, $key);
		}
		return $variationFileSources;
	}
	

	public function getAllKeys() {
		$variationKeys = array();
		foreach ($this->findVariationFsPaths() as $fileFsPath) {
			$key = self::dirNameToKey($fileFsPath->getParent()->getName());
			if ($key === null) continue;
			$variationKeys[] = $key;
		}
		return $variationKeys;
	}
}

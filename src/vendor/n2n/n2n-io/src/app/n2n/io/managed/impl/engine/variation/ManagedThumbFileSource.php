// <?php
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the N2N FRAMEWORK.
//  *
//  * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
//  * the GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg.....: Architect, Lead Developer
//  * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
//  * Thomas Günther.......: Developer, Hangar
//  */
// namespace n2n\io\managed\impl\engine;

// use n2n\io\managed\FileSource;
// use n2n\io\fs\FsPath;
// use n2n\io\IoUtils;
// use n2n\io\managed\FileManagingConstraintException;
// use n2n\io\managed\img\ImageDimension;
// use n2n\io\img\impl\ImageSourceFactory;
// use n2n\io\InputStream;
// use n2n\io\img\ImageSource;
// use n2n\io\managed\ThumbManager;
// use n2n\io\managed\AffiliationEngine;
// use n2n\io\managed\VariationManager;

// class ManagedThumbFileSource extends FileSourceAdapter implements FileSource, AffiliationEngine {
// 	private $imageDimension;
// 	private $mimeType;
// 	private $originalFileSource;

// 	/**
// 	 * @param FsPath $fsPath
// 	 * @param ImageDimension $imageDimension
// 	 * @param ManagedFileSource $originalFileSource
// 	 */
// 	public function __construct(FsPath $fsPath, ImageDimension $imageDimension, $mimeType, ManagedFileSource $originalFileSource) {
// 		parent::__construct(null, $fsPath);
// 		$this->imageDimension = $imageDimension;
// 		$this->mimeType = $mimeType;
// 		$this->originalFileSource = $originalFileSource;
// 	}
	
// 	/**
// 	 * @return ImageDimension
// 	 */
// 	public function getImageDimension(): ImageDimension {
// 		return $this->imageDimension;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\io\managed\FileSource::createInputStream()
// 	 */
// 	public function createInputStream(): InputStream {
// 		return IoUtils::createSafeFileInputStream($this->fileFsPath);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\io\managed\FileSource::move()
// 	 */
// 	public function move(FsPath $fsPath, $filePerm, $overwrite = false) {
// 		throw new FileManagingConstraintException('Managed thumb file can not be relocated: ' . $this->fileFsPath);
// 	}
	
// 	/* (non-PHPdoc)
// 	 * @see \n2n\io\managed\FileSource::delete()
// 	 */
// 	public function delete() {
// 		$this->ensureValid();
		
// 		throw new FileManagingConstraintException('Managed thumb file can not be delete: '
// 				. $this->fileFsPath);
// 	}
	
// 	public function getSize(): int {
// 		return IoUtils::filesize($this->fileFsPath);
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\io\managed\FileSource::isImage()
// 	 */
// 	public function isImage(): bool {
// 		return true;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\io\managed\FileSource::createImageSource()
// 	 */
// 	public function createImageSource(): ImageSource {
// 		$this->ensureValid();
// 		return ImageSourceFactory::createFromFileName($this->fileFsPath, $this->mimeType);
// 	}
	
// 	public function getAffiliationEngine(): AffiliationEngine {
// 		return $this;
// 	}
	
// 	/* (non-PHPdoc)
// 	 * @see \n2n\io\managed\FileSource::isThumbSupportAvailable()
// 	 */
// 	public function hasThumbSupport(): bool {
// 		return $this->originalFileSource->hasThumbSupport();
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\io\managed\FileSource::getThumbManager()
// 	 */
// 	public function getThumbManager(): ThumbManager {
// 		return $this->originalFileSource->getThumbManager();
// 	}
	
// 	public function hasVariationSupport(): bool {
// 		return true;
// 	}
	
// 	public function getVariationManager(): VariationManager {
// 		$mimeType = null;
// 		if ($this->isImage()) {
// 			$mimeType = ImageSourceFactory::getMimeTypeOfFile($this->fileFsPath);
// 		}
		
// 		return new ManagedVariationManager($this, $mimeType, $this->originalFileSource->getDirPerm(), 
// 				$this->originalFileSource->getFilePerm());
// 	}
	
// 	public function clear() {
// 		if ($this->hasVariationSupport()) {
// 			$this->getVariationManager()->clear();
// 		}
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \n2n\io\managed\FileSource::__toString()
// 	 */
// 	public function __toString(): string {
// 		return $this->fileFsPath . ' (thumb file ' . $this->imageDimension . ')';	
// 	}
// }

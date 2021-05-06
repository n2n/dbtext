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
namespace n2n\io\managed\impl\engine;

use n2n\util\uri\Url;
use n2n\io\managed\FileSource;
use n2n\io\fs\FsPath;
use n2n\io\IoUtils;
use n2n\io\managed\InaccessibleFileSourceException;
use n2n\io\img\impl\ImageSourceFactory;
use n2n\io\InputStream;
use n2n\io\img\ImageSource;
use n2n\util\ex\IllegalStateException;
use n2n\io\managed\AffiliationEngine;
use n2n\io\OutputStream;
use n2n\io\managed\FileInfo;

abstract class FileSourceAdapter implements FileSource {
	protected $qualifiedName;
	protected $fileManagerName;
	protected $fileFsPath;
	protected $originalFileSource;
	
	protected $valid = true;
	protected $url;
	protected $affiliationEngine;
	
	public function __construct(?string $qualifiedName, ?string $fileManagerName, FsPath $fileFsPath, 
			FileSource $originalFileSource = null) {
		$this->qualifiedName = $qualifiedName;
		$this->fileManagerName = $fileManagerName;
		$this->fileFsPath = $fileFsPath;
		$this->originalFileSource = $originalFileSource;
	}
	/**
	 * @return string
	 */
	function getQualifiedName(): ?string {
		return $this->qualifiedName;
	}
	
	function getFileManagerName(): ?string {
		return $this->fileManagerName;	
	}
	
	function getOriginalFileSource(): ?FileSource {
		return $this->originalFileSource;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\FileSource::hasFsPath()
	 */
	public function hasFsPath(): bool {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\FileSource::getFsPath()
	 */
	public function getFsPath(): FsPath {
		return $this->fileFsPath;
	}
	
	/**
	 * @return FsPath
	 */
	public function getFileFsPath(): FsPath {
		return $this->fileFsPath;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\FileSource::getLastModified()
	 */
	public function getLastModified(): ?\DateTime {
		$this->ensureValid();
		return $this->fileFsPath->getLastMod();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\FileSource::buildHash()
	 */
	public function buildHash(): string {
		$this->ensureValid();
		$fs = IoUtils::stat($this->fileFsPath);
		return sprintf('%x-%x-%s', $fs['ino'], $fs['size'], base_convert(str_pad($fs['mtime'], 16, '0'), 10, 16));
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::isValid()
	 */
	public function isValid(): bool {
		return $this->valid;
	}
	
	/**
	 * @throws IllegalStateException
	 */
	protected function ensureValid() {
		if ($this->valid) return;
		
		throw new InaccessibleFileSourceException('FileSource no longer valid: ' . $this->__toString());
	}	
	
	public function isHttpaccessible(): bool {
		return $this->url !== null;
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::createInputStream()
	*/
	public function createInputStream(): InputStream {
		$this->ensureValid();
		return IoUtils::createSafeFileInputStream($this->fileFsPath);
	}
	
	public function createOutputStream(): OutputStream {
		$this->ensureValid();
		return IoUtils::createSafeFileOutputStream($this->fileFsPath);
	}
	
	public function out() {
		$this->ensureValid();
		IoUtils::readfile($this->fileFsPath);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::getSize()
	*/
	public function getSize(): int {
		$this->ensureValid();
		return $this->fileFsPath->getSize();
	}
	
	public function getMimeType(): string {
		$this->ensureValid();
		return mime_content_type((string) $this->fileFsPath);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::move()
	*/
	public function move(FsPath $fsPath, $filePerm, $overwrite = false) {
		$this->ensureValid();
	
		$this->valid = false;
		$this->fileFsPath->moveFile($fsPath, $filePerm, $overwrite);
		
		(new FileInfoDingsler($this->fileFsPath))->delete();
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::copy()
	*/
	public function copy(FsPath $fsPath, $filePerm, $overwrite = false) {
		$this->ensureValid();
	
		$this->fileFsPath->copyFile($fsPath, $filePerm, $overwrite);
	}
	

	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::delete()
	*/
	public function delete() {
		$this->ensureValid();
	
		$this->valid = false;
		
		$this->fileFsPath->delete();
		$fileInfoDingsler = new FileInfoDingsler($this->fileFsPath);
		$fileInfoDingsler->delete();
		
		if ($this->affiliationEngine !== null) {
			$this->affiliationEngine->clear();
		}
	}
	
	public function getUrl(): Url {
		if ($this->url === null) {
			throw new InaccessibleFileSourceException('FileSource not accessible through http: '
					. $this->__toString());
		}
		
		return $this->url;
	}
	
	public function setUrl(Url $url) {
		$this->url = $url;
	}
	
	public function isImage(): bool {
		$this->ensureValid();
		return ImageSourceFactory::isFileSupported($this->fileFsPath);
	}
	
	public function createImageSource(): ImageSource {
		$this->ensureValid();
		
		return ImageSourceFactory::createFromFileName($this->fileFsPath,
				ImageSourceFactory::getMimeTypeOfFile($this->fileFsPath, true));
	}
	
	/**
	 * Hack for older rocket versions.
	 * @return \n2n\io\managed\ThumbManager
	 */
	function getThumbManager() {
		return $this->getAffiliationEngine()->getThumbManager();
	}
	
	public function getAffiliationEngine(): AffiliationEngine {
		$this->ensureValid();
		
		IllegalStateException::assertTrue($this->affiliationEngine !== null);
		
		return $this->affiliationEngine;
	}
	
	public function setAffiliationEngine(AffiliationEngine $affiliationEngine) {
		$this->affiliationEngine = $affiliationEngine;
	}
	
	function writeFileInfo(FileInfo $fileInfo) {
		$fileInfoDingsler = new FileInfoDingsler($this->fileFsPath);
		$fileInfoDingsler->write($fileInfo);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\FileSource::readFileInfo()
	 */
	function readFileInfo(): FileInfo {
		$fileInfoDingsler = new FileInfoDingsler($this->fileFsPath);
		return $fileInfoDingsler->read();
	}
	
}

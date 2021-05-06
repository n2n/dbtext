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

use n2n\io\fs\FsPath;
use n2n\io\managed\FileInfo;
use n2n\io\managed\FileSource;
use n2n\io\InputStream;
use n2n\util\uri\Url;
use n2n\io\img\ImageSource;
use n2n\io\managed\AffiliationEngine;
use n2n\io\OutputStream;
use n2n\util\ex\NotYetImplementedException;
use n2n\util\ex\UnsupportedOperationException;

class UncommittedManagedFileSource implements FileSource {
	private $srcFileSource;
	private $newManagedFileSource;
	
	public function __construct(FileSource $srcFileSource, FileSource $newManagedFileSource) {
		$this->srcFileSource = $srcFileSource;
		$this->newManagedFileSource = $newManagedFileSource;
	}
	
	public function getFileManagerName(): ?string {
		return $this->newManagedFileSource->getFileManagerName();
	}
	
	public function getQualifiedName(): ?string {
		return $this->newManagedFileSource->getQualifiedName();
	}
	
	public function getNewManagedFileSource() {
		return $this->newManagedFileSource;
	}
	
	public function createInputStream(): InputStream {
		return $this->srcFileSource->createInputStream();
	}
	
	public function createOutputStream(): OutputStream {
		return $this->srcFileSource->createOutputStream();
	}
	
	public function out() {
		return $this->srcFileSource->out();
	}
	
	public function getSize(): int {
		return $this->srcFileSource->getSize();
	}
	
	public function getLastModified(): ?\DateTime {
		return $this->srcFileSource->getLastModified();
	}
	
	public function buildHash(): string {
		return $this->srcFileSource->buildHash();
	}
	
	public function isValid(): bool {
		return $this->srcFileSource->isValid();
	}
	
	public function isHttpaccessible(): bool {
		return $this->srcFileSource->isHttpaccessible();
	}
	
	public function setUrl(Url $url) {
		return $this->srcFileSource->setUrl($url);
	}
	
	public function getUrl(): Url {
		return $this->srcFileSource->getUrl();
	}
	
	public function hasFsPath(): bool {
		return $this->srcFileSource->hasFsPath();
	}
	
	public function getFsPath(): FsPath {
		return $this->srcFileSource->getFsPath();
	}
	
	public function isImage(): bool {
		return $this->srcFileSource->isImage();
	}
	
	public function createImageSource(): ImageSource {
		return $this->srcFileSource->createImageSource();
	}
	
	public function getAffiliationEngine(): AffiliationEngine {
		return $this->srcFileSource->getAffiliationEngine();
	}
	
	public function move(FsPath $fsPath, $filePerm, $overwrite = false) {
		$this->srcFileSource->move($fsPath, $filePerm, $overwrite);
	}
	
	public function copy(FsPath $fsPath, $filePerm, $overwrite = false) {
		$this->srcFileSource->copy($fsPath, $filePerm, $overwrite);
	}
	
	public function delete() {
		$this->srcFileSource->delete();
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::__toString()
	 */
	public function __toString(): string {
		return 'uncommitted ' . $this->srcFileSource . ' > ' . $this->newManagedFileSource;		
	}
	public function getMimeType(): string {
		return $this->srcFileSource->getMimeType();
	}
	
	public function getOriginalFileSource(): ?FileSource {
		throw new UnsupportedOperationException();
	}

	public function readFileInfo(): FileInfo {
		return $this->newManagedFileSource->readFileInfo();
	}

	public function writeFileInfo(FileInfo $fileInfo) {
		$this->newManagedFileSource->writeFileInfo($fileInfo);
	}
}

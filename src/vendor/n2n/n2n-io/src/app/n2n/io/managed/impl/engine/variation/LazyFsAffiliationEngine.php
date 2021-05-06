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

use n2n\io\managed\AffiliationEngine;
use n2n\util\ex\IllegalStateException;
use n2n\io\managed\ThumbManager;
use n2n\io\managed\VariationManager;
use n2n\io\managed\FileSource;
use n2n\io\img\impl\ImageSourceFactory;

class LazyFsAffiliationEngine implements AffiliationEngine {
	
	private $origFileSource;
	private $mimeType;
	private $dirPerm;
	private $filePerm;
	private $thumbDisabled = false;
	/**
	 * @var ThumbManager|null
	 */
	private $thumbManager;
	/**
	 * @var VariationManager|null
	 */
	private $variationManager;
	
	/**
	 * @param ThumbManager|null $thumbManager
	 * @param VariationManager|null $variationManager
	 */
	function __construct(FileSource $origFileSource, string $dirPerm, string $filePerm) {
		$this->origFileSource = $origFileSource;
		$this->dirPerm = $dirPerm;
		$this->filePerm = $filePerm;
	}
	
	private function getMimeType() {
		if ($this->mimeType === null) {
			$this->mimeType = ImageSourceFactory::getMimeTypeOfFile($this->origFileSource->getFileFsPath());
		}
		
		return $this->mimeType;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::hasThumbSupport()
	 */
	function hasThumbSupport(): bool {
		return !$this->thumbDisabled && ($this->thumbManager !== null || $this->origFileSource->isImage());
	}
	
	/**
	 * @param ThumbManager|null $thumbManager
	 */
	function setThumbDisabled(bool $thumbDisabled) {
		$this->thumbDisabled = $thumbDisabled;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::getThumbManager()
	 */
	function getThumbManager(): ThumbManager {
		if (!$this->hasThumbSupport()) {
			throw new IllegalStateException('No thumb support avaialble.');
		}
		
		if ($this->thumbManager !== null) {
			return $this->thumbManager;
		}
		
		return $this->thumbManager = new FsThumbManager($this->origFileSource, $this->getMimeType(), 
				$this->dirPerm, $this->filePerm);
		
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::hasVariationSupport()
	 */
	function hasVariationSupport(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::getVariationManager()
	 */
	public function getVariationManager(): VariationManager {
		if (!$this->hasVariationSupport()) {
			throw new IllegalStateException('No variation support available.');
		}
		
		if ($this->variationManager !== null) {
			return $this->variationManager;
		}
		
		return $this->variationManager = new FsVariationManager($this->origFileSource, /*$this->getMimeType(),*/
				$this->dirPerm, $this->filePerm);
	}
	
	/**	
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::clear()
	 */
	public function clear() {
		if ($this->hasVariationSupport()) {
			$this->getVariationManager()->clear();
		}
		
		if ($this->hasThumbSupport()) {
			$this->getThumbManager()->clear();
		}
	}
}
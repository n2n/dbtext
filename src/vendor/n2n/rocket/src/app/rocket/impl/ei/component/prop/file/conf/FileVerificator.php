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

use n2n\l10n\Message;
use n2n\validation\lang\ValidationMessages;
use n2n\io\managed\File;
use n2n\io\managed\img\ImageFile;
use n2n\validation\plan\impl\ValidationUtils;

class FileVerificator {
	
	private $imageRecognized = true;
	private $allowedExtensions = [];
	private $allowedMimeTypes = [];
	private $maxSize;
	
	function __construct() {
		
	}
	
	function getAllowedExtensions() {
		return $this->allowedExtensions;
	}
	
	function setAllowedExtensions(array $allowedExtensions) {
		$this->allowedExtensions = $allowedExtensions;
	}
	
	function getAllowedMimeTypes() {
		return $this->allowedMimeTypes;
	}
	
	function setAllowedMimeTypes(array $allowedMimeTypes) {
		$this->allowedMimeTypes = $allowedMimeTypes;
	}
	
	function setMaxSize(int $maxSize = null) {
		$this->maxSize = $maxSize;
	}
	
	function getMaxSize() {
		return $this->maxSize;
	}

	function isImageRecognized(): bool {
		return $this->imageRecognized;
	}
	
	function setImageRecognized(bool $imageRecognized) {
		$this->imageRecognized = $imageRecognized;
	}
	
	
	public function test(File $file): bool {
		return $this->testSize($file) && $this->testType($file) && $this->testResolution($file);
	}
	
	
	public function validate(File $file): ?Message {
		if (!$this->testSize($file)) {
			return ValidationMessages::uploadMaxSize($this->maxSize, $file->getOriginalName(), 
					$file->getFileSource()->getSize());
		}
		
		if (!$this->testType($file)) {
			return ValidationMessages::fileType($file, 
					array_merge($this->allowedExtensions, $this->allowedMimeTypes));
		}
		
		if (!$this->testResolution($file)) {
			return ValidationMessages::imageResolution($file->getOriginalName());
		}
		
		return null;
	}
	
	/**
	 * @param File $file
	 * @return boolean
	 */
	private function testType($file) {
		return ValidationUtils::isFileTypeSupported($file,
				(empty($this->allowedMimeTypes) ? null : $this->allowedMimeTypes),
				(empty($this->allowedExtensions) ? null : $this->allowedExtensions));
	}
	
	/**
	 * @param File $file
	 * @return boolean
	 */
	private function testSize($file) {
		return $this->maxSize === null || $file->getFileSource()->getSize() <= $this->maxSize;
	}
	
	/**
	 * @param File $file
	 * @return boolean
	 */
	private function testResolution($file) {
		return !$this->imageRecognized || !$file->getFileSource()->isImage()
				|| ValidationUtils::isImageResolutionManagable(new ImageFile($file));
	}
}
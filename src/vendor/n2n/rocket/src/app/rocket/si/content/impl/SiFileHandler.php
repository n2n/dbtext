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
namespace rocket\si\content\impl;

use n2n\web\http\UploadDefinition;

interface SiFileHandler {
	
	/**
	 * @param UploadDefinition $uploadDefinition
	 * @return SiUploadResult
	 */
	function upload(UploadDefinition $uploadDefinition): SiUploadResult;
	
	/**
	 * @param array $idData
	 * @throws \InvalidArgumentException
	 * @return SiFile|null
	 */
	function getSiFileByRawId(array $rawId): ?SiFile;
}

class SiUploadResult {
	/**
	 * @var SiFile|null
	 */
	private $siFile;
	/**
	 * @var string|null
	 */
	private $errorMessage;
	
	/**
	 * @param SiFile|null $siFile
	 * @param string|null $errorMessage
	 */
	private function __construct($siFile, $errorMessage) {
		$this->siFile = $siFile;
		$this->errorMessage = $errorMessage;
	}
	
	/**
	 * @return boolean
	 */
	function isSuccess() {
		return $this->siFile !== null;
	}
	
	/**
	 * @return \rocket\si\content\impl\SiFile|null
	 */
	function getSiFile() {
		return $this->siFile;
	}
	
	/**
	 * @return string|null
	 */
	function getErrorMessage() {
		return $this->errorMessage;
	}
	
	/**
	 * @param SiFile $siFile
	 * @return \rocket\si\content\impl\SiUploadResult
	 */
	static function createSuccess(SiFile $siFile) {
		return new SiUploadResult($siFile, null);
	}
	
	/**
	 * @param string $errorMessage
	 * @return \rocket\si\content\impl\SiUploadResult
	 */
	static function createError(string $errorMessage) {
		return new SiUploadResult(null, $errorMessage);
	}
}
	
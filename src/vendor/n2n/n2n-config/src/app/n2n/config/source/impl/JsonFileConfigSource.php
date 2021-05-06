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
namespace n2n\config\source\impl;

use n2n\util\StringUtils;
use n2n\io\IoUtils;
use n2n\util\JsonDecodeFailedException;
use n2n\config\source\WritableConfigSource;
use n2n\config\source\CorruptedConfigSourceException;

class JsonFileConfigSource implements WritableConfigSource {
	private $filePath;
	
	public function __construct(string $filePath) {
		$this->filePath = $filePath;
	}
	
	public function readArray(): array {
		try {
			$fileContents = IoUtils::getContents($this->filePath);
			if (empty($fileContents)) {
				return array();
			} else {
				return (array) StringUtils::jsonDecode($fileContents, true);
			}
		} catch (JsonDecodeFailedException $e) {
			throw $this->createCorruptedConfigSourceException($e);
		}
	}
	
	public function writeArray(array $array) {
		IoUtils::putContentsSafe($this->filePath, json_encode($array,
				(defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : null)));
	}
	
	public function createCorruptedConfigSourceException(\Exception $previous = null) {
		return new CorruptedConfigSourceException('corrupted json file source: ' . $this->filePath,
				0, $previous);
	}
	
	public function __toString(): string {
		return 'json file (' . $this->filePath . ')';
	}
	
	public function hashCode() {
		return $this->filePath . IoUtils::filemtime($this->filePath);
	}
}

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

use n2n\io\IoUtils;
use n2n\io\IoException;
use n2n\util\ini\IniRepresentation;
use n2n\config\source\WritableConfigSource;
use n2n\config\source\CorruptedConfigSourceException;

class IniFileConfigSource implements WritableConfigSource {
	private $filePath;
	
	public function __construct($filePath) {
		$this->filePath = $filePath;
	}
	/* (non-PHPdoc)
	 * @see \n2n\config\source\ConfigSource::readArray()
	 */
	public function readArray(): array {
		try {
			return IoUtils::parseIniFile($this->filePath, true);
		} catch (IoException $e) {
			throw $this->createCorruptedConfigSourceException($e);
		}
	}
	/* (non-PHPdoc)
	 * @see \n2n\config\source\ConfigSource::createCorruptedScriptRawDataException()
	 */
	public function createCorruptedConfigSourceException(\Exception $previous = null) {
		throw new CorruptedConfigSourceException('Corrupted ini file source: ' . $this->filePath, 
				0, $previous);
	}
	/* (non-PHPdoc)
	 * @see \n2n\config\source\ConfigSource::hashCode()
	 */
	public function hashCode() {
		return IoUtils::filemtime($this->filePath) . '-' . IoUtils::filesize($this->filePath);
	}
	/* (non-PHPdoc)
	 * @see \n2n\config\source\ConfigSource::__toString()
	 */
	public function __toString(): string {
		return 'ini file (' . $this->filePath . ')';
	}
	/* (non-PHPdoc)
	 * @see \n2n\config\source\WritableConfigSource::writeArray()
	 */
	public function writeArray(array $rawData) {
		$iniRepresentation = new IniRepresentation(IoUtils::getContents($this->filePath));
		$iniRepresentation->replace($rawData);
		IoUtils::putContentsSafe($this->filePath, $iniRepresentation->__toString());
	}
	
	public function getFileName() {
		return $this->filePath;
	}
}

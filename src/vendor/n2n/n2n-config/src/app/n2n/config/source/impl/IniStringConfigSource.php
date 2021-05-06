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
use n2n\util\StringUtils;
use n2n\util\ini\IniRepresentation;
use n2n\config\source\WritableConfigSource;
use n2n\config\source\CorruptedConfigSourceException;

class IniStringConfigSource implements WritableConfigSource {
	private $iniString;
	
	public function __construct($iniString) {
		$this->iniString = (string) $iniString;
	}
	
	public function readArray() {
		try {
			return IoUtils::parseIniString($this->iniString, true);
		} catch (IoException $e) {
			throw $this->createCorruptedScriptRawDataException($e);
		}
	}
	/* (non-PHPdoc)
	 * @see \n2n\config\source\ConfigSource::createCorruptedConfigSourceException()
	 */
	public function createCorruptedConfigSourceException(\Exception $previous = null) {
		throw new CorruptedConfigSourceException('Corrupted ini string: ' . StringUtils::reduce($this->iniString, 100),
				0, $previous);
	}
	
	public function hashCode() {
		return null;
	}
	
	public function __toString(): string {
		return 'ini string (' . StringUtils::reduce($this->iniString, 100, '...') . ')';
	}
	/* (non-PHPdoc)
	 * @see \n2n\config\source\WritableConfigSource::writeArray()
	 */
	public function writeArray(array $rawData) {
		$iniRepresentation = new IniRepresentation($this->iniString);
		$iniRepresentation->replace($rawData);
		$this->iniString = $iniRepresentation->__toString();
	}

}

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
namespace n2n\io\managed\impl\engine\tmp;

use n2n\io\fs\FsPath;
use n2n\util\uri\Url;
use n2n\util\StringUtils;
use n2n\util\UnserializationFailedException;
use n2n\io\managed\impl\engine\FileSourceAdapter;

class TmpFileSource extends FileSourceAdapter implements \Serializable {
	private $sessionId;
	
	public function __construct(?string $qualifiedName, string $fileManagerName, FsPath $fileFsPath, string $sessionId = null) {
		parent::__construct($qualifiedName, $fileManagerName, $fileFsPath);
		$this->sessionId = $sessionId;
	}
		
	/**
	 * @return string
	 */
	public function getSessionId() {
		return $this->sessionId;
	}
	
	public function serialize() {
		return serialize(array('qualifiedName' => $this->qualifiedName, 'fileFsPath' => $this->fileFsPath,
				'infoFsPath' => $this->infoFsPath, 'url' => $this->url, 'sessionId' => $this->sessionId));
	}
	
	public function unserialize($serialized) {
		$data = StringUtils::unserialize($serialized);
		
		UnserializationFailedException::assertTrue(isset($data['qualifiedName']) && is_scalar($data['qualifiedName'])
				&& isset($data['fileFsPath']) && $data['fileFsPath'] instanceof FsPath
				&& array_key_exists('infoFsPath', $data) && ($data['infoFsPath'] === null || $data['infoFsPath'] instanceof FsPath)
				&& array_key_exists('url', $data) && ($data['url'] === null || $data['url'] instanceof Url)
				&& array_key_exists('sessionId', $data) && ($data['sessionId'] === null || is_scalar($data['sessionId'])));
		
		$this->qualifiedName = $data['qualifiedName'];
		$this->fileFsPath = $data['fileFsPath'];
		$this->infoFsPath = $data['infoFsPath'];
		$this->url = $data['url'];
		$this->sessionId = $data['sessionId'];
		
		if (!$this->fileFsPath->exists()) {
			$this->valid = false;
			return;
		}
			
		$this->fileFsPath->touch();
		if ($this->infoFsPath !== null) {
			$this->infoFsPath->touch();
		}
	}

	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::__toString()
	 */
	public function __toString(): string {
		return 'tmp file ' . $this->fileFsPath;
	}
	
	public function __destruct() {
		if ($this->sessionId === null && $this->isValid()) {
			$this->delete();
			
			if ($this->affiliationEngine !== null) {
				$this->affiliationEngine->clear();
			}
		}
	}
}
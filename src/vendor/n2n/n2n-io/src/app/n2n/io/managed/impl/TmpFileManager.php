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
namespace n2n\io\managed\impl;

use n2n\core\N2N;
use n2n\core\VarStore;
use n2n\context\RequestScoped;
use n2n\web\http\Session;
use n2n\core\config\IoConfig;
use n2n\io\managed\impl\engine\tmp\TmpFileEngine;
use n2n\util\ex\IllegalStateException;
use n2n\io\managed\File;
use n2n\reflection\ObjectAdapter;
use n2n\io\managed\impl\engine\QualifiedNameFormatException;

class TmpFileManager extends ObjectAdapter implements RequestScoped {
	const TMP_DIR = 'files';

	private $tmpFileEngine;

	private function _init(VarStore $varStore, IoConfig $ioConfig) {
		$tmpDirFsPath = $varStore->requestDirFsPath(VarStore::CATEGORY_TMP, N2N::NS, self::TMP_DIR);

		$this->tmpFileEngine = new TmpFileEngine($tmpDirFsPath, $ioConfig->getPrivateDirPermission(), 
				$ioConfig->getPrivateFilePermission(), self::class);

		$this->cleanUp();
	}

	/**
	 * @throws IllegalStateException
	 * @return \n2n\io\managed\impl\engine\tmp\TmpFileEngine
	 */
	private function getTmpFileEngine() {
		if ($this->tmpFileEngine !== null) {
			return $this->tmpFileEngine;
		}

		throw new IllegalStateException('TmpFileManager not initialized.');
	}

	/**
	 * @param string $originalName
	 * @param Session $session
	 * @throws \n2n\io\managed\FileManagingException on internal FileManager error
	 * @return File
	 */
	public function createFile($originalName = null) {
		return $this->getTmpFileEngine()->createFile(null, $originalName);
	}

	public function createFileFromUrl($url, $originalName = null) {
		return $this->getTmpFileEngine()->createFileFromUrl($url, null, $originalName);
	}
	
	function createFromDataUrl(string $dataUrl, $originalName = null) {
		return FileFactory::createFromDataUrl($dataUrl, $this->createFile($originalName));
	}

	/**
	 * @param File $file
	 * @return File
	 * @throws \n2n\io\managed\FileManagingException on internal FileManager error
	 * @throws \n2n\io\managed\FileManagingConstraintException if creating a temp file from passed File violates any
	 * constraints.
	 */
	public function createCopyFromFile(File $file, Session $session = null) {
		$sessionId = null;
		if ($session !== null) {
			$sessionId = $session->getId();
		}
		
		return $this->getTmpFileEngine()->createCopyFromFile($file, $sessionId);
	}
	/**
	 *
	 * @param File $file
	 * @param Session $session
	 * @return string qualified name
	 * @throws \n2n\io\managed\FileManagingConstraintException if converting passed file to a temp file violates any
	 * constraints.
	 * @throws \n2n\io\managed\FileManagingException on internal FileManager error
	 */
	public function add(File $file, Session $session = null) {
		$sessionId = null;
		if ($session !== null) {
			$sessionId = $session->getId();
		}

		return $this->getTmpFileEngine()->addFile($file, $sessionId);

	}
	/**
	 * @param string $fileName
	 * @return File or null if File not found or is not accessible by this session.
	 * @throws QualifiedNameFormatException
	 * @throws \n2n\io\managed\FileManagingException on internal FileManager error
	 */
	public function getSessionFile(string $qualifiedName, Session $session) {
		return $this->getTmpFileEngine()->getSessionFile($qualifiedName, $session->getId());
	}

	/**
	 * @param File $file
	 * @param Session $session
	 * @return boolean
	 * @throws \n2n\io\managed\FileManagingException on internal FileManager error
	 */
	public function containsSessionFile(File $file, Session $session) {
		return $this->getTmpFileEngine()->containsSessionFile($file, $session->getId());
	}
	
	/**
	 * @throws \n2n\io\managed\FileManagingException on internal FileManager error
	 */
	public function cleanUp() {
		$this->tmpFileEngine->deleteOldSessionFiles((int) ini_get('session.gc_maxlifetime'));
	}


// 	public function equals($obj) {
// 		return $obj instanceof TmpFileManager && $this->getDataDirPath() == $obj->getDataDirPath();
// 	}

}

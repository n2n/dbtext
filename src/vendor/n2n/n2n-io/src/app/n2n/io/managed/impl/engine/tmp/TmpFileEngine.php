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
use n2n\io\IoUtils;
use n2n\util\StringUtils;
use n2n\io\managed\File;
use n2n\io\managed\FileManagingException;
use n2n\io\managed\impl\CommonFile;
use n2n\util\uri\Url;
use n2n\io\managed\impl\engine\variation\LazyFsAffiliationEngine;
use n2n\io\managed\impl\engine\FileInfoDingsler;
use n2n\io\managed\impl\engine\QualifiedNameBuilder;
use n2n\io\managed\FileInfo;

class TmpFileEngine {
	const INFO_SUFFIX = '.inf';
	const SESS_PREFIX = 's_';
	const THREAD_PREFIX = 't_';

	const INFO_ORIGINAL_NAME_KEY = 'originalName';
	const INFO_SESSION_ID_KEY = 'sessionId';

	private $fsPath;
	private $dirPerm;
	private $filePerm;
	private $fileManagerName;

	public function __construct(FsPath $fsPath, string $dirPerm, string $filePerm, string $fileManagerName) {
		$this->fsPath = $fsPath;
		$this->dirPerm = $dirPerm;
		$this->filePerm = $filePerm;
		$this->fileManagerName = $fileManagerName;
	}

	private function createThreadTmpFileSource() {
		$fileFsPath = new FsPath(tempnam((string) $this->fsPath, self::THREAD_PREFIX));
		$fileFsPath->chmod($this->filePerm);

		$tfs = new TmpFileSource(null, $this->fileManagerName, $fileFsPath);
		$tfs->setAffiliationEngine(new LazyFsAffiliationEngine($tfs, $this->dirPerm, $this->filePerm));
		return $tfs;
	}

	private function createSessionTmpFileSource($sessionId, $originalName) {
		$fileFsPath = new FsPath(tempnam($this->fsPath, self::SESS_PREFIX));
		$fileFsPath->chmod($this->filePerm);

// 		$fileInfoDingsler = new FileInfoDingsler($fileFsPath);
// 		$fileInfoDingsler->write();

		$tfs = new TmpFileSource($fileFsPath->getName(), $this->fileManagerName, $fileFsPath, $sessionId);
		$fileInfo = new FileInfo($originalName);
		$fileInfo->setCustomInfo(TmpFileEngine::class, [self::INFO_SESSION_ID_KEY => $sessionId]);
		$tfs->writeFileInfo($fileInfo);
		$tfs->setAffiliationEngine(new LazyFsAffiliationEngine($tfs, $this->dirPerm, $this->filePerm));
		return $tfs;
	}

	private function createTmpFileSource($sessionId, $originalName) {
		if ($sessionId === null) {
			return $this->createThreadTmpFileSource();
		}

		return $this->createSessionTmpFileSource($sessionId, $originalName);
	}

	public function createFile(string $sessionId = null, string $originalName = null) {
		$tmpFileSource = $this->createTmpFileSource($sessionId, $originalName);

		if ($originalName === null) {
			$originalName = $tmpFileSource->getFileFsPath()->getName();
		}

		return new CommonFile($tmpFileSource, $originalName);
	}

	public function createFileFromUrl($url, $sessionId = null, $originalName = null) {
		if ($originalName === null) {
			$pathParts = Url::create($url)->getPath()->getPathParts();
			$originalName = array_pop($pathParts);
		}
		$tmpFileSource = $this->createTmpFileSource($sessionId, $originalName);
		IoUtils::copy($url, $tmpFileSource->getFileFsPath());

		return new CommonFile($tmpFileSource, $originalName);
	}

	public function addFile(File $file, $sessionId = null) {
		$originalName = $file->getOriginalName();
		$tmpFileSource = $this->createTmpFileSource($sessionId, $originalName);

		$file->getFileSource()->move($tmpFileSource->getFileFsPath(), $this->filePerm, true);
		$file->setFileSource($tmpFileSource);

		return $tmpFileSource->getQualifiedName();
	}

	public function createCopyFromFile(File $file, $sessionId = null) {
		$originalName = $file->getOriginalName();
		$tmpFileSource = $this->createTmpFileSource($sessionId, $originalName);

		$file->getFileSource()->copy($tmpFileSource->getFileFsPath(), $this->filePerm, true);
		return new CommonFile($tmpFileSource, $originalName);
	}

	public function containsSessionFile(File $file, $sessionId) {
		return $file->getFileSource() instanceof TmpFileSource
				&& $file->getFileSource()->getSessionId() === $sessionId;
	}

	public function getSessionFile($qualifiedName, $sessionId) {
		QualifiedNameBuilder::validateLevel($qualifiedName);

		$fileFsPath = $this->fsPath->ext($qualifiedName);
		if (!$fileFsPath->exists() || !StringUtils::startsWith(self::SESS_PREFIX, $fileFsPath->getName())) return null;

		$fileInfoDingsler = new FileInfoDingsler($fileFsPath);
		$infoFsPath = $fileInfoDingsler->getInfoFsPath();

		$infoFile = null;
		$infoData = null;
		try {
			$infoFile = $fileInfoDingsler->read();
			$infoData = $infoFile->getCustomInfo(TmpFileEngine::class);
		} catch (FileManagingException $e) { }

		if ($infoFile === null || $infoFile->getOriginalName() === null 
				|| $infoData === null || !array_key_exists(self::INFO_SESSION_ID_KEY, $infoData)) {
			$fileFsPath->delete();
			$fileInfoDingsler->delete();
			return null;
		}

		if ($infoData[self::INFO_SESSION_ID_KEY] !== $sessionId) {
			return null;
		}

		$fileFsPath->touch();
		$infoFsPath->touch();

// 		$originalName = $infoData[self::INFO_ORIGINAL_NAME_KEY];
// 		if ($originalName === null) {
// 			$originalName = $fileFsPath->getName();
// 		}

		try {
			$tfs = new TmpFileSource($qualifiedName, $this->fileManagerName, $fileFsPath, $sessionId);
			$tfs->setAffiliationEngine(new LazyFsAffiliationEngine($tfs, $this->dirPerm, $this->filePerm));
			return new CommonFile($tfs, $infoFile->getOriginalName());
		} catch (\InvalidArgumentException $e) {
			return null;
		}
	}

	public function deleteOldSessionFiles($gcMaxLifetime) {
		foreach ($this->fsPath->getChildren(self::SESS_PREFIX . '*') as $fsPath) {
			if ($gcMaxLifetime < (time() - $fsPath->getMTime())) {
				$fsPath->delete();
			}
		}
	}
}

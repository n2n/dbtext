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
namespace n2n\io\managed\impl\engine\transactional;

use n2n\core\Lock;
use n2n\util\ex\IllegalStateException;
use n2n\io\managed\File;
use n2n\io\IoException;
use n2n\io\managed\FileManagingException;

class FilePersistJob {
	private $file;
	private $managedFileSource;
	private $lock;
	private $filePerm;
	private $executed = false;

	public function __construct(File $file, ManagedFileSource $managedFileSource, Lock $lock, string $filePerm) {
		$this->file = $file;
		$this->managedFileSource = $managedFileSource;
		$this->lock = $lock;
		$this->filePerm = $filePerm;
	}
	
	public function getFile() {
		return $this->file;
	}

	public function execute() {
		IllegalStateException::assertTrue(!$this->executed);
		$this->executed = true;

		try {
			$this->file->getFileSource()->move($this->managedFileSource->getFileFsPath(), $this->filePerm);
			$this->file->setFileSource($this->managedFileSource);
			$this->lock->release();
		} catch (IoException $e) {
			$this->lock->release();
			throw new FileManagingException($this->managedFileSource->getFileManagerName() 
					. ' could not persist file source: ' . $this->managedFileSource, 0, $e);
		}
	}
	
	public function dispose() {
		$this->lock->release();
	}
}

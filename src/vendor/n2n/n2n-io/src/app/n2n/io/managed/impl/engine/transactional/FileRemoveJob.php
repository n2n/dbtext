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

use n2n\util\ex\IllegalStateException;
use n2n\io\IoException;
use n2n\io\managed\FileManagingException;

class FileRemoveJob {
	private $managedFileSource;
	private $executed = false;

	public function __construct(ManagedFileSource $managedFileSource) {
		$this->managedFileSource = $managedFileSource;
	}

	public function execute() {
		IllegalStateException::assertTrue(!$this->executed);
		$this->executed = true;

		$this->managedFileSource->getAffiliationEngine()->clear();
		
		$fsPath = $this->managedFileSource->getFileFsPath();
		if (!$fsPath->exists()) return;
			
		try {
			$fsPath->delete();
		} catch (IoException $e) {
			throw new FileManagingException($this->managedFileSource->getFileManagerName() 
					. ' could not remove file source: ' . $this->managedFileSource, 0, $e);
		}
	}
}

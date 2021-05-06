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

use n2n\io\fs\FsPath;
use n2n\io\managed\FileManagingConstraintException;
use n2n\io\managed\impl\engine\FileSourceAdapter;

class ManagedFileSource extends FileSourceAdapter {
// 	private $persistent = false;
	
	public function __construct(FsPath $fileFsPath, string $fileManagerName, string $qualifiedName) {
		parent::__construct($qualifiedName, $fileManagerName, $fileFsPath);
	}
	
// 	public function setPersisent($persistent) {
// 		$this->persistent = (boolean) $persistent;
// 	}
	
// 	public function isPersistent() {
// 		return $this->persistent;
// 	}
	
	public function move(FsPath $fsPath, $filePerm, $overwrite = false) {
		$this->ensureValid();
		
		throw new FileManagingConstraintException('File is managed by ' . $this->fileManagerName 
				. ' and can not be relocated: ' . $this->fileFsPath);
	}
	
	public function delete() {
		$this->ensureValid();
		
		throw new FileManagingConstraintException('File is managed by ' . $this->fileManagerName 
				. ' and can not be deleted: ' . $this->fileFsPath);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::__toString()
	 */
	public function __toString(): string {
		return $this->fileFsPath . ' (managed by ' . $this->fileManagerName . ')';		
	}
}

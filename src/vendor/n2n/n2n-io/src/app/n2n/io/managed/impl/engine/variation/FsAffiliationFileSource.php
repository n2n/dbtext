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
namespace n2n\io\managed\impl\engine\variation;

use n2n\io\managed\FileSource;
use n2n\io\fs\FsPath;
use n2n\io\managed\FileManagingConstraintException;
use n2n\io\managed\impl\engine\FileSourceAdapter;

class FsAffiliationFileSource extends FileSourceAdapter implements FileSource {

	/**
	 * @param FsPath $fsPath
	 */
	public function __construct(FsPath $fsPath, FileSource $originalFileSource = null) {
		parent::__construct(null, null, $fsPath, $originalFileSource);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::move()
	 */
	public function move(FsPath $fsPath, $filePerm, $overwrite = false) {
		throw new FileManagingConstraintException('Variation file can not be relocated: ' . $this->fileFsPath);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::delete()
	 */
	public function delete() {
		$this->getAffiliationEngine()->clear();
		$this->fileFsPath->delete();
		
// 		$this->ensureValid();
		
// 		throw new FileManagingConstraintException('Variation file can not be delete: '
// 				. $this->fileFsPath);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileSource::__toString()
	 */
	public function __toString(): string {
		return $this->fileFsPath . ' (variation file)';	
	}
}

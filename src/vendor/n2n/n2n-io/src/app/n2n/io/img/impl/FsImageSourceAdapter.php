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
namespace n2n\io\img\impl;

use n2n\io\img\ImageSource;
use n2n\io\IoUtils;

abstract class FsImageSourceAdapter implements ImageSource {
	protected $filePath;	
	
	private $width;
	private $height;
	
	public function __construct($fileFsPath) {
		$this->filePath = (string) $fileFsPath;
	}
	
	public function getFilePath() {
		return $this->filePath;
	}
	
	public function getWidth(): int {
		if ($this->width === null) {
			$this->initSize();
		}
		
		return $this->width;
	}
	
	public function getHeight(): int {
		if ($this->height === null) {
			$this->initSize();
		}
		return $this->height;
	}
	
	private function initSize() {
		$size = IoUtils::getimagesize($this->filePath);
		$this->width = $size[0];
		$this->height = $size[1];
	}
}

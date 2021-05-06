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
namespace n2n\io\fs;

use n2n\io\OutputStream;
use n2n\io\InputStream;
use n2n\io\IoUtils;
use n2n\io\ResourceStream;

class FileResourceStream extends ResourceStream implements InputStream, OutputStream {
	private $fileName;
	private $fh;
	private $lock;
	/**
	 *
	 * @param string $fileName
	 * @param string $mode
	 * @param int $lock
	 * @throws \n2n\io\CouldNotAchieveFlockException
	 */
	public function __construct(string $fileName, string $mode = 'c+', $lock = null) {
		$this->fileName = $fileName;
		$resource = IoUtils::fopen($this->fileName, $mode);
		
		$this->lock = $lock;
		if (!empty($this->lock)) {
			IoUtils::flock($resource, $lock);
		}
		
		parent::__construct($resource);
	}
	/**
	 * @return string
	 */
	public function getFileName() {
		return $this->fileName;
	}
	
	public function available() {
		return filesize($this->getFileName());
	}
	
	public function read($length = null) {
		if ($length === null) {
			$length = filesize($this->getFileName());
		}
		if ($length == 0) {
			return '';
		}
		return IoUtils::fread($this->getResource(), $length);
	}
	
	public function close() {
		if (!$this->isOpen()) return;
		
		if (!empty($this->lock)) {
			IoUtils::flock($this->getResource(), LOCK_UN);
		}
		
		parent::close();
	}
}

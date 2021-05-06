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
namespace n2n\io;

use n2n\util\type\ArgUtils;
use n2n\util\ex\NotYetImplementedException;

class ResourceStream implements InputStream, OutputStream {
	private $resource;
	
	public function __construct($resource) {
		ArgUtils::assertTrue(is_resource($resource));
		$this->resource = $resource;
	}
	/**
	 * (non-PHPdoc)
	 * @see n2n\io.Stream::isOpen()
	 */
	public function isOpen() {
		return $this->resource !== null;
	}
	/* (non-PHPdoc)
	 * @see \n2n\io\Stream::getResource()
	 */
	public function getResource() {
		if ($this->resource === null) {
			throw new StreamResourceUnavailbaleException('Io file stream closed');
		}
	
		return $this->resource;
	}
	/* (non-PHPdoc)
	 * @see \n2n\io\OutputStream::write()
	 */
	public function write(string $contents) {
		IoUtils::fwrite($this->getResource(), $contents);
	}
	
	public function read($length = null) {
		if ($length === null) {
			return IoUtils::streamGetContents($this->getResource());
		}

		return IoUtils::fread($this->getResource(), $length);
	}
	
	public function truncate($size = 0) {
		IoUtils::ftruncate($this->getResource(), $size);
		fseek($this->getResource(), 0);
	}
	
	public function close() {
		if (!$this->isOpen()) return;
	
		fclose($this->resource);
		$this->resource = null;
	}
	
	public function flush() { }
	/* (non-PHPdoc)
	 * @see \n2n\io\Stream::hasResource()
	 */
	public function hasResource() {
		return $this->resource !== null;
	}
	/* (non-PHPdoc)
	 * @see \n2n\io\InputStream::available()
	 */
	public function available() {
		throw new NotYetImplementedException();
	}


}

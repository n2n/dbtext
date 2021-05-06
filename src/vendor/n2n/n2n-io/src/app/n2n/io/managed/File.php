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
namespace n2n\io\managed;

use n2n\util\uri\Linkable;

interface File extends Linkable {
	
	/**
	 * @return bool 
	 */
	public function isValid(): bool;
	
	/**
	 * @return string
	 */
	public function getOriginalName(): string;
	
	/**
	 * @param string $originalName
	 * @throws \InvalidArgumentException if originalName is empty
	 */
	public function setOriginalName(string $originalName);
	
	/**
	 * @return string or null
	 */
	public function getOriginalExtension(): ?string;
	
	/**
	 * @param FileSource $fileSource
	 */
	public function setFileSource(FileSource $fileSource);
	
	/**
	 * @return FileSource
	 */
	public function getFileSource(): FileSource;
	
	/**
	 * @return string
	 */
	public function __toString(): string;
	
	/**
	 * 
	 */
	public function delete();
	
	/**
	 * @param mixed $fsPath
	 */
	public function move($fsPath, string $filePerm, bool $overwrite = true);
	
	/**
	 * @param mixed $fsPath
	 * @return File
	 */
	public function copy($fsPath, string $filePerm, bool $overwrite = true): File;
	
	/**
	 * @param mixed $o
	 * @return bool
	 */
	public function equals($o): bool;
}

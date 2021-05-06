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
namespace n2n\util\ex\err;

class AdditionalError {
	private $description;
	private $filePath;
	private $lineNo;
	private $startLineNo;
	private $endLineNo;
	
	public function __construct(string $description = null, string $fileFsPath, $line = null, $startLine = null, $endLine = null) {
		$this->description = $description;
		$this->filePath = $fileFsPath;
		$this->lineNo = $line;
		$this->startLineNo = $startLine;
		$this->endLineNo = $endLine;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getFilePath(): string {
		return $this->filePath;
	}
	
	public function getLineNo() {
		return $this->lineNo;
	}
	
	public function getStartLineNo() {
		return $this->startLineNo;
	}
	
	public function getEndLineNo() {
		return $this->endLineNo;
	}
}

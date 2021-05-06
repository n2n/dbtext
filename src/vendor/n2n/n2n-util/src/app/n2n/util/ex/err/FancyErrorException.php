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

use n2n\util\ex\Documentable;

class FancyErrorException extends \ErrorException implements Documentable, EnhancedError {
	private $startLine;
	private $endLine;
	private $documentId;
	private $additionalErrors = array();
	/**
	 * 
	 * @param string $message
	 * @param int $code
	 * @param int $severity
	 * @param string $file
	 * @param int $line
	 * @param int $startLine
	 * @param int $endLine
	 * @param \Exception $previous
	 */
	public function __construct(string $message = null, string $file = null, int $line = null, 
			int $startLine = null, int $endLine = null, \Exception $previous = null, 
			string $documentId = null, int $code = null, int $severity = E_USER_ERROR) {
		parent::__construct((string) $message, $code, $severity, $file, $line, $previous);
		 
		$this->startLine = $startLine;
		$this->endLine = $endLine;
		$this->documentId = $documentId;
	}

	public function getStartLine() {
		return $this->startLine;
	}

	public function getEndLine() {
		return $this->endLine;
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\ex\Documentable::getDocumentId()
	 */
	public function getDocumentId() {
		return $this->documentId;
	}
	
	public function addAdditionalError(string $fileFsPath, int $line = null, int $startLine = null, 
			int $endLine = null, string $description = null) {
		$this->additionalErrors[] = new AdditionalError($description, $fileFsPath, $line, $startLine, $endLine);
	}
	
	public function getAdditionalErrors(): array {
		return $this->additionalErrors;
	}
}

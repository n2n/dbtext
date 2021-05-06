<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\util\entry;

use n2n\l10n\Message;
use rocket\ei\EiPropPath;
use rocket\ei\util\EiuAnalyst;

class EiuField {
	private $eiPropPath;
	private $eiuEntry;
	private $eiuAnalyst;
	private $eiuProp;
	
	public function __construct(EiPropPath $eiPropPath, EiuEntry $eiuEntry, EiuAnalyst $eiuAnalyst = null) {
		$this->eiPropPath = $eiPropPath;
		$this->eiuEntry = $eiuEntry;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuProp
	 */
	public function getEiuProp() {
		if ($this->eiuProp === null) {
			$this->eiuProp = $this->getEiuEntry()->getEiuFrame()->getEiuEngine()->prop($this->eiPropPath);
		}
		
		return $this->eiuProp;
	}
	
	/**
	 * @return \rocket\ei\EiPropPath
	 */
	public function getEiPropPath() {
		return $this->eiPropPath;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiFieldWrapper
	 */
	private function getEiFieldWrapper() {
		return $this->eiuEntry->getEiEntry()->getEiFieldWrapper($this->eiPropPath);
	}
	
	function isDirty() {
		return $this->getEiFieldWrapper()->hasChanges();
	}
	
	function read() {
		return $this->getEiFieldWrapper()->read();
	}
	
	public function getEiuEntry() {
		return $this->eiuEntry;
	}
	
	function isWritable() {
		return $this->getEiuEntry()->isFieldWritable($this->eiPropPath);
	}
	
	public function getValue() {
		return $this->getEiuEntry()->getValue($this->eiPropPath);
	}
	
	public function setValue($value) {
		return $this->getEiuEntry()->setValue($this->eiPropPath, $value);
	}
	
	public function getMessages(bool $recursive = false) {
		return $this->getEiuEntry()->getMessages($this->eiPropPath, $recursive);
	}
	
	public function getMessagesAsStrs(bool $recursive = false) {
		return $this->getEiuEntry()->getMessagesAsStrs($this->eiPropPath, $recursive);
	}
	
	/**
	 * @param mixed $scalarValue
	 * @return EiuField
	 */
	public function setScalarValue($scalarValue) {
		$this->getEiuEntry()->setScalarValue($this->eiPropPath, $scalarValue);
		return $this;
	}
	
	/**
	 * @param Message $message
	 * @return EiuField
	 */
	public function addError(Message $message) {
		$this->getEiuEntry()->getEiEntry()->getValidationResult()->getEiFieldValidationResult($this->eiPropPath)
				->addError($message);
		return $this;
	}
}

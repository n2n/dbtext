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
namespace rocket\ei\manage\entry;

use n2n\l10n\Message;
use rocket\ei\EiPropPath;
use rocket\si\input\SiEntryError;
use n2n\l10n\N2nLocale;

class EiEntryValidationResult {
	/**
	 * @var EiFieldValidationResult[]
	 */
	private $eiFieldValidationResults = array();
	
	public function isValid(bool $checkRecursive = true): bool {
		 foreach ($this->eiFieldValidationResults as $eiEiFieldValidationResult) {
		 	if (!$eiEiFieldValidationResult->isValid($checkRecursive)) return false;
		 }
		 
		 return true;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param bool $checkRecursive
	 * @return boolean
	 */
	function isEiFieldValid(EiPropPath $eiPropPath, bool $checkRecursive) {
		$eiPropPathStr = (string) $eiPropPath;
		return !isset($this->eiFieldValidationResults[$eiPropPathStr]) 
				||  $this->eiFieldValidationResults[$eiPropPathStr]->isValid($checkRecursive);
	}
	
	/**
	 * @param bool $checkRecursive
	 * @return \rocket\ei\manage\entry\EiFieldValidationResult[]
	 */
	function getInvalidEiFieldValidationResults(bool $checkRecursive) {
		$results = [];
		foreach ($this->eiFieldValidationResults as $eiPropPathStr => $eiFieldValidationResult) {
			if ($eiFieldValidationResult->isValid($checkRecursive)) continue;
			
			$results[$eiPropPathStr] = $eiFieldValidationResult;
		}
		return $results;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return \rocket\ei\manage\entry\EiFieldValidationResult
	 */
	public function getEiFieldValidationResult(EiPropPath $eiPropPath) {
		$eiPropPathStr = (string) $eiPropPath;
		if (!isset($this->eiFieldValidationResults[$eiPropPathStr])) {
			$this->eiFieldValidationResults[$eiPropPathStr] = new EiFieldValidationResult($eiPropPath);
		}
		return $this->eiFieldValidationResults[$eiPropPathStr];
	}

	public function getEiFieldValidationResults() {
		return $this->eiFieldValidationResults;
	}

	/**
	 * @return Message[]
	 */
	public function getMessages(bool $recursive = false) {
		$messages = array();
		foreach ($this->eiFieldValidationResults as $eiEiFieldValidationResult) {
			$messages = array_merge($messages, $eiEiFieldValidationResult->getMessages($recursive));
		}
		return $messages;
	}
	
	public function processMessage(bool $recursive) {
		foreach ($this->eiFieldValidationResults as $result) {
			if (null !== ($message = $result->processMessage(true))) {
				return $message;
			}
		}
	}

// 	/**
// 	 * @return SiEntryError|null 
// 	 */
// 	function toSiEntryError(N2nLocale $n2nLocale) {
// 		$error = new SiEntryError();
				
// 		foreach ($this->getInvalidEiFieldValidationResults(true) as $key => $eiFieldValidationResult) {
// 			$error->putFieldError($key, $eiFieldValidationResult->toSiFieldError($n2nLocale));	
// 		}
		
// 		if ($error->isEmpty()) {
// 			return null;
// 		}
		
// 		return $error;
// 	}

}

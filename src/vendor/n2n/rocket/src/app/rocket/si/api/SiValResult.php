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
namespace rocket\si\api;

use rocket\si\meta\SiDeclaration;
use rocket\si\content\SiEntry;
use n2n\util\type\ArgUtils;

class SiValResult implements \JsonSerializable {
	/**
	 * @var bool
	 */
	private $valid;
	/** 
	 * @var SiValGetResult[]
	 */
	private $getResults = [];

	function __construct(bool $valid) {
		$this->valid = $valid;
	}
	
	/** 
	 * @return SiValGetResult[]
	 */
	function getGetResults() {
		return $this->getResults;
	}

	/**
	 * @param SiValGetResult[]
	 */
	function setGetResults(array $getResults) {
		ArgUtils::valArray($getResults, SiValGetResult::class);
		$this->getResults = $getResults;
	}
	
	/**
	 * @param string $key
	 * @param SiValGetResult $getResult
	 */
	function putGetResult(string $key, SiValGetResult $getResult) {
		$this->getResults[$key] = $getResult;
	}

	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	public function jsonSerialize() {
		return [
			'valid' => $this->valid,
			'getResults' => $this->getResults
		];
	}
}

class SiValGetResult implements \JsonSerializable {
	/**
	 * @var SiDeclaration|null
	 */
	private $declaration = null;
	/**
	 * @var SiEntry|null
	 */
	private $entry = null;
	
	function __construct() {
	}
	
	/**
	 * @return \rocket\si\meta\SiDeclaration|null
	 */
	public function getDeclaration() {
		return $this->declaration;
	}

	/**
	 * @param \rocket\si\meta\SiDeclaration|null $declaration
	 */
	public function setDeclaration(?SiDeclaration $declaration) {
		$this->declaration = $declaration;
	}

	/**
	 * @return \rocket\si\content\SiEntry
	 */
	public function getEntry() {
		return $this->entry;
	}

	/**
	 * @param \rocket\si\content\SiEntry|null $entries
	 */
	public function setEntry(?SiEntry $entry) {
		$this->entry = $entry;
	}

	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	public function jsonSerialize() {
		return [
			'declaration' => $this->declaration,
			'entry' => $this->entry
		];
	}	
}

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
namespace rocket\si\content;

use n2n\util\type\ArgUtils;

class SiPartialContent implements \JsonSerializable {
	private $count;
	private $offset = 0;
	private $entries;
	

	/**
	 * @param int $count
	 * @param SiEntry[] $entries
	 */
	function __construct(int $count, array $entries = []) {
		$this->count = $count;
		$this->setEntries($entries);
	}
	
	/**
	 * @return int
	 */
	public function getOffset() {
		return $this->offset;
	}
	
	/**
	 * @param int $offset
	 */
	public function setOffset(int $offset) {
		$this->offset = $offset;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getCount() {
		return $this->count;
	}
	
	/**
	 * @param int $count
	 * @return \rocket\si\content\SiPartialContent
	 */
	function setCount(int $count) {
		$this->count = $count;
		return $this;
	}

	/**
	 * @param SiEntry[] $siEntries
	 * @return \rocket\si\meta\SiDeclaration
	 */
	function setEntries(array $entries) {
		ArgUtils::valArray($entries, SiEntry::class);
		$this->entries = $entries;
		return $this;
	}
	
	/**
	 * @return SiEntry[]
	 */
	function getEntries() {
		return $this->entries;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'entries' => $this->entries,
			'count' => $this->count,
			'offset' => $this->offset
		];
	}
}
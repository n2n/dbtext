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
namespace rocket\si\content\impl\split;

use n2n\util\uri\Url;
use rocket\si\content\SiEntry;
use rocket\si\meta\SiStyle;

class SiSplitContent implements \JsonSerializable {
	private $label;
	private $shortLabel;
	
	private $apiGetUrl;
	private $entryId;
	private $style;
	private $propIds = null;
// 	/**
// 	 * @var SiDeclaration
// 	 */
// 	private $declaration;
	/**
	 * @var SiEntry
	 */
	private $entry;
	
	private function __construct() {
	}
	
	/**
	 * @return string
	 */
	function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string|null
	 */
	function getShortLabel() {
		return $this->shortLabel;
	}
	
	/**
	 * @param string $shortLabel
	 * @return \rocket\si\content\impl\split\SiSplitContent
	 */
	function setShortLabel(?string $shortLabel) {
		$this->shortLabel = $shortLabel;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	function getPropIds() {
		return $this->propIds;
	}
	
	/**
	 * @param string[] $propIds
	 * @return \rocket\si\content\impl\split\SiSplitContent
	 */
	function setPropIds(array $propIds) {
		$this->propIds = array_values($propIds);
		return $this;
	}
	
	/**
	 * @return \rocket\si\content\SiEntry|null
	 */
	function getEntry() {
		return $this->entry;
	}
	
	function jsonSerialize() {
		$data = [ 'label' => $this->label, 'shortLabel' => $this->shortLabel ?? $this->label ];
		
// 		if ($this->apiUrl !== null) {
			$data['apiGetUrl'] = (string) $this->apiGetUrl;
			$data['entryId'] = $this->entryId;
			$data['propIds'] = $this->propIds;
			$data['style'] = $this->style;
// 		}
		
// 		if ($this->entry !== null) {
// 			$data['declaration'] = $this->declaration;
			$data['entry'] = $this->entry;
// 		}
		
		return $data;
	}
	
	static function createUnavaialble(string $label) {
		$split = new SiSplitContent();
		$split->label = $label;
		return $split;
	}
	
	/**
	 * @param string $label
	 * @param Url $apiGetUrl
	 * @param string $entryId
	 * @param bool $bulky
	 * @return \rocket\si\content\impl\split\SiSplitContent
	 */
	static function createLazy(string $label, Url $apiGetUrl, ?string $entryId, SiStyle $style) {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->apiGetUrl = $apiGetUrl;
		$split->entryId = $entryId;
		$split->style = $style;
		return $split;
	}
	
	/**
	 * @param string $label
	 * @param SiEntry $entry
	 * @return \rocket\si\content\impl\split\SiSplitContent
	 */
	static function createEntry(string $label, SiEntry $entry) {
		$split = new SiSplitContent();
		$split->label = $label;
		$split->entry = $entry;
		return $split;
	}
}

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
namespace rocket\si\content\impl\relation;

use rocket\si\content\impl\basic\BulkyEntrySiGui;
use rocket\si\content\impl\basic\CompactEntrySiGui;
use rocket\si\SiPayloadFactory;

class SiEmbeddedEntry implements \JsonSerializable {
	/**
	 * @var BulkyEntrySiGui
	 */
	private $content;
	/**
	 * @var CompactEntrySiGui|null
	 */
	private $summaryContent;
	
	/**
	 * @param BulkyEntrySiGui $content
	 * @param CompactEntrySiGui|null $summaryContent
	 */
	function __construct(BulkyEntrySiGui $content, CompactEntrySiGui $summaryContent = null) {
		$this->content = $content;
		$this->summaryContent = $summaryContent;
	}
	
	/**
	 * @return \rocket\si\content\impl\basic\BulkyEntrySiGui
	 */
	function getContent() {
		return $this->content;
	}
	
	/**
	 * @param \rocket\si\content\impl\basic\BulkyEntrySiGui $content
	 */
	function setContent(BulkyEntrySiGui $content) {
		$this->content = $content;
	}
	
	/**
	 * @return \rocket\si\content\impl\basic\CompactEntrySiGui|null
	 */
	function getSummaryContent() {
		return $this->summaryContent;
	}
	
	/**
	 * @param \rocket\si\content\impl\basic\CompactEntrySiGui|null $summaryContent
	 */
	function setSummaryContent(CompactEntrySiGui $summaryContent) {
		$this->summaryContent = $summaryContent;
	}

	function jsonSerialize() {		
		return [
			'content' => SiPayloadFactory::buildDataFromComp($this->content),
			'summaryContent' => SiPayloadFactory::buildDataFromComp($this->summaryContent)
		];
	}

}

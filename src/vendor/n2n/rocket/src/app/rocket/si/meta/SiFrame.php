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
namespace rocket\si\meta;

use n2n\util\uri\Url;

class SiFrame implements \JsonSerializable {
	private $apiUrl;
	private $typeContext;
	/**
	 * @var bool
	 */
	private $sortable = false;
	
	/**
	 * @param Url $apiUrl
	 * @param SiTypeContext $typeContext
	 */
	function __construct(array $apiUrlMap, SiTypeContext $typeContext) {
		$this->apiUrlMap = $apiUrlMap;
		$this->typeContext = $typeContext;
	}
	
	/**
	 * @return \rocket\si\meta\SiTypeContext
	 */
	function getTypeContext() {
		return $this->typeContext;
	}
	
	/**
	 * @param bool $sortable
	 * @return \rocket\si\meta\SiFrame
	 */
	function setSortable(bool $sortable) {
		$this->sortable = $sortable;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isSortable() {
		return $this->sortable;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'apiUrlMap' => array_map(fn ($url) => (string) $url, $this->apiUrlMap),
			'typeContext' => $this->typeContext,
			'sortable' => $this->sortable
		];
	}
}
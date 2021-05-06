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
namespace rocket\si\content\impl\meta;

use n2n\util\type\ArgUtils;

class SiCrumbGroup implements \JsonSerializable {
	private $crumbs = [];
	
	/**
	 * @param SiCrumb[] $crumbs
	 */
	function __construct(array $crumbs) {
		$this->setCrumbs($crumbs);
	}
	
	/**
	 * @return SiCrumb[]
	 */
	function getCrumbs() {
		return $this->crumbs;
	}
	
	/**
	 * @return boolean
	 */
	function isEmpty() {
		return empty($this->crumbs);
	}
	
	/**
	 * @param SiCrumb[] $crumbs
	 * @return \rocket\si\content\impl\meta\SiCrumbGroup
	 */
	function setCrumbs(array $crumbs) {
		ArgUtils::valArray($crumbs, SiCrumb::class);
		$this->crumbs = $crumbs;
		return $this;
	}
	
	/**
	 * @param SiCrumb ...$siCrumbs
	 * @return \rocket\si\content\impl\meta\SiCrumbGroup
	 */
	function add(SiCrumb ...$siCrumbs) {
		array_push($this->crumbs, ...$siCrumbs);
		return $this;
	}
	
	/**
	 * @return array
	 */
	function jsonSerialize() {
		return [
			'crumbs' => $this->crumbs
		];
	}
}

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

use n2n\util\type\attrs\DataSet;
use rocket\si\meta\SiStyle;

class SiValGetInstruction {
	private $style;
	private $declarationRequested = true;
	private $controlsIncluded = true;

	function __construct(SiStyle $style) {
		$this->style = $style;
	}
	
	/**
	 * @return SiStyle
	 */
	public function getStyle() {
		return $this->style;
	}
	
	/**
	 * @param SiStyle $bulky
	 */
	public function setStyle(SiStyle $style) {
		$this->style = $style;
	}
	
	/**
	 * @return boolean
	 */
	function isDeclarationRequested() {
		return $this->declarationRequested;
	}
	
	/**
	 * @param bool $declarationRequest
	 */
	function setDeclarationRequested(bool $declarationRequest) {
		$this->declarationRequested = $declarationRequest;
	}
	
	/**
	 * @return boolean
	 */
	public function areControlsIncluded() {
		return $this->controlsIncluded;
	}
	
	/**
	 * @param boolean $controlsIncluded
	 */
	public function setControlsIncluded(bool $controlsIncluded) {
		$this->controlsIncluded = $controlsIncluded;
	}
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return \rocket\si\api\SiValRequest
	 */
	static function createFromData(array $data) {
		$ds = new DataSet($data);
		
		$getInstruction = new SiValGetInstruction(SiStyle::createFromData($ds->reqArray('style')));
		$getInstruction->setDeclarationRequested($ds->reqBool('declarationRequested'));
		$getInstruction->setControlsIncluded($ds->reqBool('controlsIncluded'));
		return $getInstruction;
	}
	
}

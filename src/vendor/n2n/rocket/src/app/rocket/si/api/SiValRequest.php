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

use n2n\util\type\attrs\AttributesException;
use n2n\util\type\attrs\DataSet;
use n2n\util\type\ArgUtils;

class SiValRequest {
	private $instructions = [];
	
	/**
	 * 
	 */
	function __construct() {
	}
	
	/**
	 * @return SiValInstruction[]
	 */
	function getInstructions() {
		return $this->instructions;
	}

	/**
	 * @param SiValInstruction[]
	 */
	function setInstructions(array $instructions) {
		ArgUtils::valArray($instructions, SiValInstruction::class);
		$this->instructions = $instructions;
	}
	
	function putInstruction(string $key, SiValInstruction $instruction) {
		$this->instructions[$key] = $instruction;
	}

	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return \rocket\si\api\SiValRequest
	 */
	static function createFromData(array $data) {
		$ds = new DataSet($data);
		
		$getRequest = new SiValRequest();
		try {
			foreach ($ds->reqArray('instructions') as $key => $instructionData) {
				$getRequest->putInstruction($key, SiValInstruction::createFromData($instructionData));
			}
		} catch (AttributesException $e) {
			throw new \InvalidArgumentException(null, 0, $e);
		}
		return $getRequest;
	}
}

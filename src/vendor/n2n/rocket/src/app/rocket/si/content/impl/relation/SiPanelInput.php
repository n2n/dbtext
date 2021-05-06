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

use rocket\si\input\SiEntryInput;
use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\DataSet;
use rocket\si\input\CorruptedSiInputDataException;

class SiPanelInput {
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var SiEntryInput[]
	 */
	private $entryInputs = [];

	/**
	 * @param string $name
	 */
	function __construct(string $name) {
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	function setName(string $name) {
		$this->name = $name;
	}
	
	function getEntryInputs() {
		return $this->entryInputs;
	}
	
	function setEntryInputs(array $entryInputs) {
		ArgUtils::valArray($entryInputs, SiEntryInput::class);
		$this->entryInputs = $entryInputs;
	}
	
	/**
	 * @param array $data
	 * @return SiEntryInput
	 * @throws CorruptedSiInputDataException
	 */
	static function parse(array $data) {
		$dataSet = new DataSet($data);
		
		try {
			$panelInput = new SiPanelInput($dataSet->reqString('name'));
			$entryInputs = [];
			foreach ($dataSet->reqArray('entryInputs', 'array') as $entryInputData) {
				$entryInputs[] = SiEntryInput::parse($entryInputData);
			}
			$panelInput->setEntryInputs($entryInputs);
			return $panelInput;
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new CorruptedSiInputDataException(null, 0, $e);
		}
	}
}

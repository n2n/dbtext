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

use n2n\util\type\attrs\DataSet;
use rocket\si\content\impl\SiFieldAdapter;

class SplitPlaceholderSiField extends SiFieldAdapter {
	/**
	 * @var string
	 */
	private $refPropId;
	/**
	 * @var SplitStyle
	 */
	private $copyStyle;
	
// 	/**
// 	 * @var \Closure|null
// 	 */
// 	private $saveCallback = null;
	
	/**
	 * @param string $refPropId
	 */
	function __construct(string $refPropId) {
		$this->refPropId = $refPropId;
		$this->copyStyle = new SplitStyle(null, null);
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'split-placeholder';
	}
	
	/**
	 * @param SplitStyle $splitStyle
	 * @return SplitContextInSiField
	 */
	function setCopyStyle(SplitStyle $splitStyle) {
		$this->copyStyle = $splitStyle;
		return $this;
	}
	
	/**
	 * @return \rocket\si\content\impl\split\SplitStyle
	 */
	function getCopyStyle() {
		return $this->copyStyle;
	}
	
// 	/**
// 	 * @param string $key
// 	 * @param SiField $field
// 	 * @return \rocket\si\content\impl\split\SplitPlaceholderSiField
// 	 */
// 	function putInputHandler(string $key, SiLazyInputHandler $inputHandler) {
// 		$this->inputHandlers[$key] = $inputHandler;
// 		return $this;
// 	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'refPropId' => $this->refPropId,
			'copyStyle' => $this->copyStyle
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::isReadOnly()
	 */
	function isReadOnly(): bool {
		return empty($this->inputHandlers);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$dataMap = (new DataSet($data))->reqArray('value', 'array');
		
		foreach ($this->inputHandlers as $key => $inputHandler) {
			if (isset($dataMap[$key])) {
				$inputHandler->handleInput($dataMap[$key]);
			}
		}
	}
}
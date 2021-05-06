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
namespace rocket\si\content\impl\string;

use n2n\util\type\attrs\DataSet;
use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\string\cke\conf\CkeEditorConfig;
use rocket\si\content\impl\InSiFieldAdapter;

class CkeInSiField extends InSiFieldAdapter {
	/**
	 * @var string|null
	 */
	protected $value;
	/**
	 * @var int|null
	 */
	private $minlength;
	/**
	 * @var int|null
	 */
	private $maxlength;
	/**
	 * @var bool
	 */
	private $mandatory = false;
	/**
	 * @var CkeEditorConfig
	 */
	private $ckeConfig;
	/**
	 * @var CkeStyle[]
	 */
	private $styles = [];
	
	function __construct(?string $value) {
		$this->value = $value;
		$this->ckeConfig = new CkeEditorConfig();
	}
	
	/**
	 * @param string|null $value
	 * @return \rocket\si\content\impl\string\CkeInSiField
	 */
	function setValue(?string $value) {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * @param int|null $minlength
	 * @return \rocket\si\content\impl\string\CkeInSiField
	 */
	function setMinlength(?int $minlength) {
		$this->minlength = $minlength;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMinlength() {
		return $this->minlength;
	}
	
	/**
	 * @param int|null $maxlength
	 * @return \rocket\si\content\impl\string\CkeInSiField
	 */
	function setMaxlength(?int $maxlength) {
		$this->maxlength = $maxlength;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMaxlength() {
		return $this->maxlength;
	}

	/**
	 * @param bool $mandatory
	 * @return \rocket\si\content\impl\string\CkeInSiField
	 */
	function setMandatory(bool $mandatory) {
		$this->mandatory = $mandatory;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isMandatory() {
		return $this->mandatory;
	}
	
	/**
	 * @return CkeEditorConfig
	 */
	function getCkeConfig() {
		return $this->ckeConfig;
	}
	
	/**
	 * @param CkeEditorConfig $ckeConfig
	 * @return CkeInSiField
	 */
	function setCkeConfig(CkeEditorConfig $ckeConfig) {
		$this->ckeConfig = $ckeConfig;
		return $this;
	}
	
	/**
	 * @return CkeStyle[]
	 */
	function getStyles() {
		return $this->styles;
	}
	
	/**
	 * @param CkeStyle[] $styles
	 * @return \rocket\si\content\impl\string\CkeInSiField
	 */
	function setCkeStyles(array $styles) {
		ArgUtils::valArray($styles, CkeStyle::class);
		$this->styles = $styles;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'cke-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'value' => $this->value,
			'minlength' => $this->minlength,
			'maxlength' => $this->maxlength,
			'mandatory' => $this->mandatory,
			'styles' => $this->styles,
			'messages' => $this->getMessageStrs()
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$this->value = (new DataSet($data))->reqString('value', true);
	}
}

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
use rocket\si\content\impl\InSiFieldAdapter;

class PasswordInSiField extends InSiFieldAdapter {
	/**
	 * @var string|null
	 */
	private $rawPassword;
	private $mandatory = false;
	private $minLength;
	private $maxLength;
	private $passwordSet = false;
	
	/**
	 * @return string|null
	 */
	function getRawPassword() {
		return $this->rawPassword;
	}
	
	/**
	 * @param bool $mandatory
	 * @return PasswordInSiField
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
	
	function isPasswordSet() {
		return $this->passwordSet;
	}
	
	/**
	 * @param bool $passwordSet
	 * @return \rocket\si\content\impl\string\PasswordInSiField
	 */
	function setPasswordSet(bool $passwordSet) {
		$this->passwordSet = $passwordSet;
		
		return $this;
	}
	
	/**
	 * @param int|null $maxlength
	 * @return PasswordInSiField
	 */
	function setMaxlength(?int $maxlength) {
		$this->maxLength = $maxlength;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMaxlength() {
		return $this->maxLength;
	}
	
	/**
	 * @param int|null $minlength
	 * @return PasswordInSiField
	 */
	function setMinlength(?int $minlength) {
		$this->minLength = $minlength;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMinlength() {
		return $this->minLength;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'password-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return ['minlength' => $this->minLength,
			'maxlength' => $this->maxLength,
			'mandatory' => $this->mandatory,
			'passwordSet' => $this->passwordSet,
			'messages' => $this->getMessageStrs()
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$this->rawPassword = (new DataSet($data))->reqString('rawPassword', true);
	}
}

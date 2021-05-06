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
namespace rocket\si\content\impl\date;

use n2n\util\type\attrs\DataSet;
use rocket\si\content\impl\InSiFieldAdapter;
use n2n\util\DateUtils;

class DateTimeInSiField extends InSiFieldAdapter {
	/**
	 * @var bool
	 */
	private $value;
	/**
	 * @var bool
	 */
	private $mandatory = false;
	/**
	 * @var bool
	 */
	private $dateChoosable = true;
	/**
	 * @var bool
	 */
	private $timeChoosable = true;
	
	/**
	 * @param int $value
	 */
	function __construct(?\DateTime $value) {
		$this->value = $value;	
	}
	
	/**
	 * @param \DateTime|null $value
	 * @return \rocket\si\content\impl\date\DateTimeInSiField
	 */
	function setValue(?int $value) {
		$this->value = $value;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getValue() {
		return $this->value;
	}
	
	/**
	 * @param bool $mandatory
	 * @return \rocket\si\content\impl\date\DateTimeInSiField
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
	 * @param bool $dateChoosable
	 * @return \rocket\si\content\impl\date\DateTimeInSiField
	 */
	function setDateChoosable(bool $dateChoosable) {
		$this->dateChoosable = $dateChoosable;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isDateChoosable() {
		return $this->dateChoosable;
	}
	
	/**
	 * @param bool $timeChoosable
	 * @return \rocket\si\content\impl\date\DateTimeInSiField
	 */
	function setTimeChoosable(bool $timeChoosable) {
		$this->timeChoosable = $timeChoosable;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isTimeChoosable() {
		return $this->timeChoosable;
	}
	

	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'datetime-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'value' => DateUtils::dateTimeToSql($this->value),
			'mandatory' => $this->mandatory,
			'dateChoosable' => $this->dateChoosable,
			'timeChoosable' => $this->timeChoosable,
			'messages' => $this->getMessageStrs()
		];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$this->value = DateUtils::sqlToDateTime((new DataSet($data))->reqString('value', true));
	}
}

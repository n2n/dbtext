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

use n2n\util\type\attrs\DataSet;
use rocket\si\content\SiEntryQualifier;
use n2n\util\uri\Url;
use n2n\util\type\ArgUtils;
use rocket\si\content\impl\InSiFieldAdapter;
use rocket\si\meta\SiFrame;

class QualifierSelectInSiField extends InSiFieldAdapter {
	/**
	 * @var SiFrame
	 */
	private $frame;
	/**
	 * @var SiEntryQualifier[]
	 */
	private $values;
	/**
	 * @var int
	 */
	private $min = 0;
	
	/**
	 * @var int|null
	 */
	private $max = null;
	
	/**
	 * @var SiEntryQualifier[]
	 */
	private $pickables = null;
	
	/**
	 * @param Url $apiUrl
	 * @param SiEntryQualifier[] $values
	 */
	function __construct(SiFrame $frame, array $values = []) {
		$this->frame = $frame;
		$this->setValues($values);	
	}
	
	/**
	 * @param SiEntryQualifier[] $values
	 * @return QualifierSelectInSiField
	 */
	function setValues(array $values) {
		$typeContext = $this->frame->getTypeContext();
		foreach ($values as $value) {
			ArgUtils::assertTrue($typeContext->containsEntryBuildupId($value->getIdentifier()->getEntryBuildupId()));
		}
		$this->values = $values;
		return $this;
	}
	
	/**
	 * @return SiEntryQualifier[]
	 */
	function getValues() {
		return $this->values;
	}
	
	/**
	 * @param int $min
	 * @return QualifierSelectInSiField
	 */
	function setMin(int $min) {
		$this->min = $min;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int|null $max
	 * @return QualifierSelectInSiField
	 */
	function setMax(?int $max) {
		$this->max = $max;
		return $this;
	}
	
	/**
	 * @return int|null
	 */
	function getMax() {
		return $this->max;
	}
	
	/**
	 * @param SiEntryQualifier[] $pickables
	 * @return QualifierSelectInSiField
	 */
	function setPickables(?array $pickables) {
		$typeContext = $this->frame->getTypeContext();
		foreach ((array) $pickables as $pickable) {
			ArgUtils::assertTrue($pickable instanceof SiEntryQualifier 
					&& $typeContext->containsEntryBuildupId($pickable->getIdentifier()->getTypeId()));
		}
		$this->pickables = $pickables;
		return $this;
	}
	
	/**
	 * @return SiEntryQualifier[]
	 */
	function getPickables() {
		return $this->pickables;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'qualifier-select-in';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'frame' => $this->frame,
			'values' => $this->values,
			'min' => $this->min,
			'max' => $this->max,
			'pickables' => $this->pickables,
			'messages' => $this->getMessageStrs()
		];
	}
	 
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::handleInput()
	 */
	function handleInput(array $data) {
		$siQualifiers = [];
		foreach ((new DataSet($data))->reqArray('values', 'array') as $data) {
			$siQualifiers[] = SiEntryQualifier::parse($data);
		}
		
		$this->values = $siQualifiers;
	}
}

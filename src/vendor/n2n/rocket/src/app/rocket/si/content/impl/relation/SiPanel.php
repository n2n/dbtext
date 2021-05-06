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

use n2n\util\type\ArgUtils;
use rocket\si\content\impl\SiFieldErrorTrait;

class SiPanel implements \JsonSerializable {
	use SiFieldErrorTrait;
	
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $label;
	/**
	 * @var int
	 */
	private $min = 0;
	/**
	 * @var int|null
	 */
	private $max = null;
	/**
	 * @var bool
	 */
	private $reduced = true;
	/**
	 * @var bool
	 */
	private $sortable = false;
	/**
	 * @var string[]|null
	 */
	private $allowedTypeIds = null;
	/**
	 * @var bool
	 */
	private $nonNewRemovable = true;
	/**
	 * @var SiGridPos|null
	 */
	private $gridPos = null;
	/**
	 * @var SiEmbeddedEntry[]
	 */
	private $values = [];

	/**
	 * @param string $name
	 * @param string $label
	 */
	function __construct(string $name, string $label) {
		$this->name = $name;
		$this->label = $label;
	}
	
	/**
	 * @return string
	 */
	function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 * @return SiPanel
	 */
	function setName(string $name) {
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return string
	 */
	function getLabel() {
		return $this->label;
	}
	
	/**
	 * @param string $label
	 * @return SiPanel
	 */
	function setLabel(string $label) {
		$this->label = $label;
		return $this;
	}
		
	/**
	 * @return int
	 */
	function getMin() {
		return $this->min;
	}
	
	/**
	 * @param int $min
	 * @return SiPanel
	 */
	function setMin(int $min) {
		$this->min = $min;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getMax() {
		return $this->max;
	}
	
	/**
	 * @param int|null $max
	 * @return SiPanel
	 */
	function setMax(?int $max) {
		$this->max = $max;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	function isReduced() {
		return $this->reduced;
	}
	
	/**
	 * @param boolean $reduced
	 * @return SiPanel
	 */
	function setReduced(bool $reduced) {
		$this->reduced = $reduced;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isNonNewRemovable() {
		return $this->nonNewRemovable;
	}
	
	/**
	 * @param bool $nonNewRemovable
	 * @return SiPanel
	 */
	function setNonNewRemovable(bool $nonNewRemovable) {
		$this->nonNewRemovable = $nonNewRemovable;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isSortable() {
		return $this->sortable;
	}
	
	/**
	 * @param bool $sortable
	 * @return EmbeddedEntryPanelsInSiField
	 */
	function setSortable(bool $sortable) {
		$this->sortable = $sortable;
		return $this;
	}
	
	/**
	 * @return string[]|null
	 */
	function getAllowedTypeIds() {
		return $this->allowedTypeIds;
	}
	
	/**
	 * @param string[]|null $allowedTypeIds
	 * @return SiPanel
	 */
	function setAllowedTypeIds(?array $allowedTypeIds) {
		ArgUtils::valArray($allowedTypeIds, 'string', true);
		$this->allowedTypeIds = $allowedTypeIds === null ? null : array_values($allowedTypeIds);
		return $this;
	}
	
	/**
	 * @return \rocket\si\content\impl\relation\SiGridPos|null
	 */
	function getGridPos() {
		return $this->gridPos;
	}
	
	/**
	 * @param \rocket\si\content\impl\relation\SiGridPos|null $gridPos
	 * @return SiPanel
	 */
	function setGridPos(?SiGridPos $gridPos) {
		$this->gridPos = $gridPos;
		return $this;
	}
	
	/**
	 * @return SiEmbeddedEntry[]
	 */
	function getEmbedddedEntries() {
		return $this->values;
	}
	
	/**
	 * @param SiEmbeddedEntry[] $embeddedEntries
	 * @return SiPanel
	 */
	function setEmbeddedEntries(array $embeddedEntries) {
		ArgUtils::valArray($embeddedEntries, SiEmbeddedEntry::class);
		$this->values = $embeddedEntries;
		return $this;
	}
	
	/**
	 * @param SiEmbeddedEntry $embeddedEntry
	 * @return SiPanel
	 */
	function addEmbeddedEntry(SiEmbeddedEntry $embeddedEntry) {
		$this->values[] = $embeddedEntry;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'name' => $this->name,
			'label' => $this->label,
			'min' => $this->min,
			'max' => $this->max,
			'reduced' => $this->reduced,
			'nonNewRemovable' => $this->nonNewRemovable,
			'sortable' => $this->sortable,
			'allowedTypeIds' => $this->allowedTypeIds,
			'gridPos' => $this->gridPos,
			'values' => $this->values
		];
	}
}

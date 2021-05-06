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
namespace rocket\si\meta;

class SiProp implements \JsonSerializable {
	private $id;
	private $label;
	private $helpText;
	private $descendantPropIds = [];
	
	/**
	 * @param string $id
	 * @param string $label
	 */
	function __construct(?string $id, ?string $label) {
		$this->id = $id;
		$this->label = $label;
	}
	
	/**
	 * @return string
	 */
	public function getPropId() {
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return \rocket\si\meta\SiProp
	 */
	public function setPropId(string $id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $label
	 * @return \rocket\si\meta\SiProp
	 */
	public function setLabel(string $label) {
		$this->label = $label;
		return $this;
	}

	/**
	 * @return string|null
	 */
	function getHelpText() {
		return $this->helpText;
	}

	/**
	 * @param string|null $helpText
	 * @return \rocket\si\meta\SiProp
	 */
	function setHelpText(?string $helpText) {
		$this->helpText = $helpText;
		return $this;
	}
	
	/**
	 * @return string[]
	 */
	function getDescendantPropIds() {
		return $this->descendantPropIds;
	}
	
	/**
	 * @param string[] $descendantPropIds
	 * @return \rocket\si\meta\SiProp
	 */
	function setDescendantPropIds(array $descendantPropIds) {
		$this->descendantPropIds = $descendantPropIds;
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'id' => $this->id,
			'label' => $this->label,
			'helpText' => $this->helpText,
			'descendantPropIds' => $this->descendantPropIds
		];
	}

	
	
	
}
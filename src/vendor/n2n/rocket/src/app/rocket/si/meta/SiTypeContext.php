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

use n2n\util\type\ArgUtils;

class SiTypeContext implements \JsonSerializable {
	private $typeId = null;
	private $entryBuildupIds = [];
	private $treeMode = false;
	
	/**
	 * @param string $typeId
	 * @param array $entryBuildupIds
	 */
	function __construct(string $typeId, array $entryBuildupIds) {
		$this->typeId = $typeId;
		ArgUtils::valArray($entryBuildupIds, 'string');
		$this->entryBuildupIds = array_values($entryBuildupIds);
	}
	
	/**
	 * @param string $entryBuildupId
	 * @return bool
	 */
	function containsEntryBuildupId(string $entryBuildupId) {
		return in_array($entryBuildupId, $this->entryBuildupIds);
	}
	
	/**
	 * @param bool $treeMode
	 * @return SiTypeContext
	 */
	function setTreeMode(bool $treeMode) {
		$this->treeMode = $treeMode;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isTreeMode() {
		return $this->treeMode;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'typeId' => $this->typeId,
			'entryBuildupIds' => $this->entryBuildupIds,
			'treeMode' => $this->treeMode
		];
	}
}
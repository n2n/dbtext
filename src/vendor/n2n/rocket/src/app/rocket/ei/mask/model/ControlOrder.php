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
namespace rocket\ei\mask\model;

class ControlOrder {
	const SEPARATOR = '?';
	
	private $controlIds;
	
	public function __construct(array $controlIds) {
		$this->controlIds = $controlIds;
	}
	
	public function getControlIds(): array {
		return $this->controlIds;
	}
	
	public function sort(array $items) {
		$sortedItems = array();
		foreach ($this->controlIds as $controlId) {
			if (!isset($items[$controlId])) continue;
			$sortedItems[$controlId] = $items[$controlId];
			unset($items[$controlId]);
		}
	
		return array_merge($sortedItems, $items);
	}
	
	/**
	 * @param string $eiCommandId
	 * @param string $controlId
	 * @return string
	 */
	public static function buildControlId($eiCommandId, $controlId) {
		return $eiCommandId . self::SEPARATOR . $controlId;
	}
}

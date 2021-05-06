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
namespace rocket\si\control;

use n2n\util\type\ArgUtils;

class SiEntryEvent implements \JsonSerializable {
	const TYPE_ADDED = 'added';
	const TYPE_UPDATED = 'updated';
	const TYPE_REMOVED = 'removed';
	
	/**
	 * @var string
	 */
	private $category;
	/**
	 * @var string|null
	 */
	private $id;
	/**
	 * @var string
	 */
	private $type;
	
	/**
	 * @param string $category
	 * @param string|null $id
	 * @param string $type
	 */
	function __construct(string $category, ?string $id, string $type) {
		ArgUtils::valEnum($type, self::getTypes());
		
		$this->category = $category;
		$this->id = $id;
		$this->type = $type;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'category' => $this->category,
			'id' => $this->id,
			'type' => $this->type
		];
	}
	
	/**
	 * @return string[]
	 */
	static function getTypes() {
		return [self::TYPE_ADDED, self::TYPE_REMOVED, self::TYPE_UPDATED];
	}

}
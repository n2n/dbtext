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
namespace rocket\ei;

use n2n\io\IoUtils;
use rocket\spec\InvalidEiMaskConfigurationException;

class EiTypeExtensionCollection implements \IteratorAggregate, \Countable {
	private $eiType;
	private $eiTypeExtensions = array();
	private $defaultId;
	private $createdDefault = null;
	
	public function __construct(EiType $eiType) {
		$this->eiType = $eiType;
	}
	
	public function add(EiTypeExtension $eiTypeExtension) {
		$id = $eiTypeExtension->getId();
		if (0 == mb_strlen($id)) {
			$eiTypeExtension->setId($this->makeUniqueId(''));
		} else if (IoUtils::hasSpecialChars($id)) {
			throw new InvalidEiMaskConfigurationException('Id of passed EiTypeExtension contains invalid characters: ' . $id);
		}
	
		$this->eiTypeExtensions[$eiTypeExtension->getId()] = $eiTypeExtension;
	}
	
	/**
	 * @param string $id
	 * @return EiTypeExtension
	 * @throws UnknownEiTypeExtensionException
	 */
	public function getById($id) {
		if (isset($this->eiTypeExtensions[$id])) {
			return $this->eiTypeExtensions[$id];
		}
	
		throw new UnknownEiTypeExtensionException('No EiTypeExtension with id \'' . (string) $id
				. '\' found in  \'' . $this->eiType->getId() . '\'.');
	}
	
	
	public function isEmpty(): bool {
		return empty($this->eiTypeExtensions);
	}
	
	/**
	 * @param string $idBase
	 * @return string
	 */
	public function makeUniqueId(string $idBase) {
		$idBase = IoUtils::stripSpecialChars($idBase, true);
		if (mb_strlen($idBase) && !$this->containsId($idBase)) {
			return $idBase;
		}
	
		for ($ext = 1; true; $ext++) {
			$id = $idBase . $ext;
			if (!$this->containsId($id)) {
				return $id;
			}
		}
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsId(string $id): bool {
		return isset($this->eiTypeExtensions[$id]);
	}
	
	/* (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new \ArrayIterator($this->toArray());
	}
	
	/**
	 * @return EiTypeExtension[]
	 */
	public function toArray() {
		return $this->eiTypeExtensions;
	}
	/* (non-PHPdoc)
	 * @see Countable::count()
	 */
	public function count() {
		return count($this->eiTypeExtensions);
	}
}

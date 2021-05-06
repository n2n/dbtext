<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\config\source\impl;

use n2n\util\cache\CacheStore;
use n2n\config\source\WritableConfigSource;

class CacheStoreConfigSource implements WritableConfigSource {
	private $cacheStore;
	private $name;
	private $characteristics;
	
	public function __construct(CacheStore $cacheStore, string $name, array $characteristics = array()) {
		$this->cacheStore = $cacheStore;
		$this->name = $name;
		$this->characteristics = $characteristics;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\config\source\ConfigSource::readArray()
	 */
	public function readArray(): array {
		$cacheItem = $this->cacheStore->get($this->name, $this->characteristics);
		
		if ($cacheItem !== null && is_array($data = $cacheItem->getData())) {
			return $data;
		}
		
		return array();
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\config\source\WritableConfigSource::writeArray()
	 */
	public function writeArray(array $rawData) {
		$this->cacheStore->store($this->name, $this->characteristics, $rawData);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\config\source\ConfigSource::hashCode()
	 */
	public function hashCode() {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\config\source\ConfigSource::__toString()
	 */
	public function __toString() {
		return 'ConfigSource ' . $this->name . ' of CacheStore ' . get_class($this->cacheStore); 
	}


}

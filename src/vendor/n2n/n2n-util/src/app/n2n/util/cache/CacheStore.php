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
namespace n2n\util\cache;

interface CacheStore {
	/**
	 * @param string $name
	 * @param array $characteristics
	 * @param array $data
	 */
	public function store(string $name, array $characteristics, $data, \DateTime $lastMod = null);
	/**
	 * @param string $name
	 * @param array $characteristics
	 * @return CacheItem or null if item does not exist
	 * @throws CorruptedCacheStoreException
	 */
	public function get(string $name, array $characteristics);
	/**
	 * @param string $name
	 * @param array $characteristics
	 */
	public function remove(string $name, array $characteristics);
	/**
	 * @param string $name
	 * @param array $characteristicNeedles
	 * @return CacheItem[]
	 */
	public function findAll(string $name, array $characteristicNeedles = null);
	/**
	 * @param string $name
	 * @param array $characteristicNeedles
	 */
	public function removeAll(string $name, array $characteristicNeedles = null);
	/**
	 * 
	 */
	public function clear();
}

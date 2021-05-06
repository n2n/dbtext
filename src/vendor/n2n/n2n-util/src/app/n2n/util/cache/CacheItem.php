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

class CacheItem {
	private $name;
	private $characteristics;
	public $data;
	/**
	 * @param string $name
	 * @param array $characteristics
	 * @param mixed $data
	 */
	public function __construct($name, array $characteristics, $data) {
		$this->name = $name;
		$this->characteristics = $characteristics;
		$this->data = $data;
	}
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}
	/**
	 * @return array
	 */
	public function getCharacteristics() {
		return $this->characteristics;
	}
	/**
	 * @param array $characteristics
	 */
	public function setCharacteristics(array $characteristics) {
		$this->characteristics = $characteristics;
	}
	/**
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}
	/**
	 * @param mixed $data
	 */
	public function setData($data) {
		$this->data = $data;
	}	
}

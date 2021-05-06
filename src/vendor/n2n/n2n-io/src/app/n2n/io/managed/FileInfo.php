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
namespace n2n\io\managed;

use n2n\util\type\attrs\DataMap;

class FileInfo implements \JsonSerializable {
	private $originalName;
	private $customInfos = [];
	
	/**
	 * @param string $originalName
	 */
	function __construct(string $originalName = null) {
		$this->originalName = $originalName;
	}
	
	/**
	 * @param string|null $originalName
	 */
	function setOriginalName(?string $originalName) {
		$this->originalName = $originalName;
	}
	
	/**
	 * @return string
	 */
	function getOriginalName() {
		return $this->originalName;
	}
	
	/**
	 * @param string $refTypeName
	 * @param array $data
	 * @return FileInfo
	 */
	function setCustomInfo(string $refTypeName, array $data) {
		$this->customInfos[$refTypeName] = $data;
		return $this;
	}
	
	/**
	 * @param string $refTypeName
	 */
	function removeCustomInfo(string $refTypeName) {
		unset($this->customInfos[$refTypeName]);
	}
	
	/**
	 * @param string $refTypeName
	 * @return array|null
	 */
	function getCustomInfo(string $refTypeName) {
		return $this->customInfos[$refTypeName] ?? null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'originalName' => $this->originalName,
			'customInfos' => $this->customInfos
		];
	}
	
	static function fromArray(array $data) {
		$dm = new DataMap($data);
		
		try {
			$fileInfo = new FileInfo($dm->optString('originalName'));
			$fileInfo->customInfos = $dm->reqArray('customInfos', 'array');
			return $fileInfo;
		} catch (\n2n\util\type\attrs\InvalidAttributeException $e) {
			throw new \InvalidArgumentException(null, 0, $e);
		}
	}
}

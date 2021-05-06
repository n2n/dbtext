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

use n2n\util\uri\Url;
use rocket\core\model\IncompleteNavPointException;

class SiNavPoint implements \JsonSerializable {
	private $url;
	private $siref;
	
	function __construct(Url $url = null, bool $siref = true) {
		$this->url = $url;
		$this->siref = $siref;
	}
	
	function isSiRef(): bool {
		return $this->siref;
	}
	
	/**
	 * @return boolean
	 */
	function isUrlComplete() {
		return $this->url !== null && (!$this->url->isRelative() || $this->url->getPath()->hasLeadingDelimiter());
	}
	
	/**
	 * @param Url $contextUrl
	 * @return SiNavPoint
	 */
	function complete(Url $contextUrl) {
		$this->url = $contextUrl->ext($this->url);
		return $this;
	}
	
	/**
	 * @return Url 
	 */
	function getUrl() {
		if ($this->isUrlComplete()) {
			return $this->url;
		}
	
		throw new IncompleteNavPointException('Incomplete url: ' . $this->url);
	}
	
	function jsonSerialize() {
		return [
			'siref' => $this->siref,
			'url' => (string) $this->getUrl()
		];
	}
	
	/**
	 * @param Url $urlExt
	 * @return \rocket\si\control\SiNavPoint
	 */
	static function href(Url $url = null) {
		return new SiNavPoint($url, false);
	}
	
	/**
	 * @param Url $urlExt
	 * @return \rocket\si\control\SiNavPoint
	 */
	static function siref(Url $url = null) {
		return new SiNavPoint($url, true);
	}
}
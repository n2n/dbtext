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
namespace n2n\l10n;

class Region {
	private $n2nLocaleId;
	
	/**
	 * 
	 * @param string $n2nLocaleId
	 */
	public function __construct($n2nLocaleId) {
		$this->n2nLocaleId = $n2nLocaleId;
	}
	/**
	 * 
	 * @param string $displayN2nLocale
	 * @return string
	 */
	public function getName($displayN2nLocale = null) {
		if (!L10nUtils::isL10nSupportAvailable()) return $this->getShort();
		
		if (isset($displayN2nLocale)) {
			return \Locale::getDisplayRegion($this->n2nLocaleId, $displayN2nLocale);
		}
		return \Locale::getDisplayRegion($this->n2nLocaleId);
	}
	/**
	 * 
	 * @return string
	 */
	public function getShort() {
		return self::parseId($this->n2nLocaleId);
	}
	/**
	 * 
	 * @param string $o
	 * @return boolean
	 */
	public function equals($o) {
		return $o instanceof Region && $o->getShort() == $this->getShort();
	}
	/**
	 * 
	 * @return string
	 */
	public function __toString(): string {
		return $this->getShort();
	}
	
	public static function parseId($n2nLocaleId) {
		if (L10nUtils::isL10nSupportAvailable()) {
			$regionId = \Locale::getRegion($n2nLocaleId);
			if (empty($regionId)) return null;
			return $regionId;
		}
		
		$n2nLocaleIdParts = explode('_', ($n2nLocaleId));
		if (sizeof($n2nLocaleIdParts) > 1) {
			return $n2nLocaleIdParts[1];
		}
		
		return null;
	}
}

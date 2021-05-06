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

class L10n {
	private static $peclIntlEnabled = true;
	private static $l10nConfig;
	private static $pseudoL10nConfig;
	
	public static function setPeclIntlEnabled(bool $peclIntlEnabled) {
		self::$peclIntlEnabled = $peclIntlEnabled;
	}
	
	public static function isPeclIntlEnabled(): bool {
		return self::peclIntlEnabled;
	}
	
	public static function setL10nConfig(L10nConfig $l10nConfig) {
		self::$l10nConfig = $l10nConfig;
	}
	
	public static function getL10nConfig() {
		if (self::$l10nConfig === null) {
			self::$l10nConfig = new L10nConfig(true, array());
		}
		
		return self::$l10nConfig;
	}
	
	public static function setPseudoL10nConfig(PseudoL10nConfig $pseudoL10nConfig) {
		self::$pseudoL10nConfig = $pseudoL10nConfig;	
	}
	
	public static function getPseudoL10nConfig() {
		if (self::$pseudoL10nConfig === null) {
			self::$pseudoL10nConfig = new PseudoL10nConfig(array(), array());
		}
		
		return self::$pseudoL10nConfig;
	}
}

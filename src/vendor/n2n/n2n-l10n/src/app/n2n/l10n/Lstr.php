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

use n2n\l10n\impl\StaticLstr;
use n2n\l10n\impl\TextCodeLstr;
use n2n\l10n\impl\TextCodeDtcLstr;

abstract class Lstr {
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	abstract function t(N2nLocale $n2nLocale): string;
	
	/**
	 * @return string
	 */
	abstract function __toString(): string;
	
	/**
	 * @param string|Lstr $arg
	 * @return Lstr
	 */
	static function create($arg): Lstr {
		if ($arg instanceof Lstr) return $arg;
		
		return new StaticLstr((string) $arg);
	}

	/**
	 * @param string $code
	 * @param string ...$moduleNamespaces
	 * @return \n2n\l10n\impl\TextCodeLstr
	 */
	static function createCode(string $code, string ...$moduleNamespaces) {
		return new TextCodeLstr($code, null, null, $moduleNamespaces);
	}
	
	static function createCodeDtc(string $code, DynamicTextCollection $dtc) {
		return new TextCodeDtcLstr($code, null, null, $dtc);
	}

	/**
	 * @param string $code
	 * @param array $args
	 * @param string ...$moduleNamespaces
	 * @return \n2n\l10n\impl\TextCodeLstr
	 */
	static function createCodeArg(string $code, array $args, string ...$moduleNamespaces) {
		return new TextCodeLstr($code, $args, null, $moduleNamespaces);
	}
	
	/**
	 * @param string $code
	 * @param array $args
	 * @param int $num
	 * @param string ...$moduleNamespaces
	 * @return \n2n\l10n\impl\TextCodeLstr
	 */
	static function createCodeArgNum(string $code, array $args, int $num, string ...$moduleNamespaces) {
		return new TextCodeLstr($code, $args, $num, $moduleNamespaces);
	}
}
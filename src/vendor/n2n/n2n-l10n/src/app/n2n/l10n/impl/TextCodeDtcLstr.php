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
namespace n2n\l10n\impl;

use n2n\l10n\Lstr;
use n2n\l10n\DynamicTextCollection;
use n2n\l10n\N2nLocale;

class TextCodeDtcLstr extends Lstr {
	private $code;
	private $args;
	private $num;
	private $dtc;
	
	/**
	 * @param string $code
	 * @param array|null $args
	 * @param int|null $num
	 * @param string[] $moduleNamespaces
	 */
	function __construct(string $code, ?array $args, ?int $num, DynamicTextCollection $dtc) {
		$this->code = $code;
		$this->args = $args;
		$this->num = $num;
		$this->dtc = $dtc;
	}
	
	function t(N2nLocale $n2nLocale): string {
		return $this->dtc->lt($n2nLocale, $this->code, $this->args, $this->num);
	}
	
	function __toString(): string {
		try {
			return $this->t(N2nLocale::getDefault());
		} catch (\Throwable $e) {
			return $e->getMessage();
		}
	}
}
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

use n2n\l10n\Message;
use n2n\l10n\DynamicTextCollection;
use n2n\l10n\N2nLocale;
use n2n\l10n\Lstr;

class LstrMessage extends Message {
	private $lstr;
	
	public function __construct(Lstr $lstr, int $severity = null) {
		parent::__construct($severity);
		
		$this->lstr = $lstr;
	}
	
	public function setLstr(Lstr $lstr) {
		$this->lstr = $lstr;
		return $this;
	}
	
	/**
	 * @return \n2n\l10n\Lstr
	 */
	public function getLstr() {
		return $this->lstr;
	}
	
	public function t(N2nLocale $n2nLocale, string $moduleNamespace = null): string {
		return $this->lstr->t($n2nLocale);
	}
		
	public function tByDtc(DynamicTextCollection $dtc, N2nLocale $n2nLocale, string $moduleNamespace = null): string {
		return $this->t($n2nLocale, $moduleNamespace);
	}
	
	public function __toString(): string {
		return $this->lstr->__toString();
	}
}

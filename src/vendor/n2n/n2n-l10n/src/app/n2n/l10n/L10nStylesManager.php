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

class L10nStylesManager {
	private $l10nStyles;
	
	public function __construct(array $l10nStyles) {
		$this->l10nStyles = $l10nStyles;
	}
	
	/**
	 * @return L10nStyle[]
	 */
	public function getL10nStyles() {
		return $this->l10nStyles;
	}
	/**
	 * @param N2nLocale $n2nLocale
	 * @return L10nStyle
	 */
	public function getStyle(N2nLocale $n2nLocale) {
		$n2nLocaleId = $n2nLocale->getId();
		if (isset($this->l10nStyles[$n2nLocaleId])) {
			return $this->l10nStyles[$n2nLocaleId];
		}
	
		$languageId = $n2nLocale->getLanguageId();
		if (isset($this->l10nStyles[$languageId])) {
			return $this->l10nStyles[$languageId];
		}
	
		return null;
	}
}

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

namespace rocket\impl\ei\component\prop\string\cke\model;

use n2n\l10n\N2nLocale;
use n2n\web\ui\view\View;

abstract class CkeLinkProviderAdapter implements CkeLinkProvider {
	
	public function buildUrl(string $key, View $view, N2nLocale $n2nLocale) {
		$linkOptions = $this->getLinkOptions($n2nLocale);
		if (isset($linkOptions[$key])) return $linkOptions[$key];
		
		return null;
	}
	
	/**
	 * @return bool
	 */
	public function isOpenInNewWindow(): bool {
		return false;
	}
}

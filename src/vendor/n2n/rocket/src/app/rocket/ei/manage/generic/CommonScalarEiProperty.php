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

namespace rocket\ei\manage\generic;

use n2n\l10n\Lstr;
use rocket\ei\component\prop\EiProp;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\EiPropPath;

class CommonScalarEiProperty implements ScalarEiProperty {
	private $eiProp;
	private $scalarValueBuilder;
	private $eiFieldValueBuilder;

	public function __construct(EiProp $eiProp, \Closure $scalarValueBuilder = null, 
			\Closure $eiFieldValueBuilder = null) {
		$this->eiProp = $eiProp;
		$this->scalarValueBuilder = $scalarValueBuilder;
		$this->eiFieldValueBuilder = $eiFieldValueBuilder;
	}

	public function getLabelLstr(): Lstr {
		return $this->eiProp->getLabelLstr();
	}
	
	public function getEiPropPath(): EiPropPath {
		return EiPropPath::from($this->eiProp);
	}

	public function buildScalarValue(EiEntry $eiEntry) {
		return $this->eiFieldValueToScalarValue($eiEntry->getValue($this->eiProp));
	}

	public function eiFieldValueToScalarValue($eiFieldValue) {
		if ($this->scalarValueBuilder === null) {
			return $eiFieldValue;
		}
		
		return $this->scalarValueBuilder->__invoke($eiFieldValue);
	}

	public function scalarValueToEiFieldValue($scalarValue) {
		if ($this->eiFieldValueBuilder === null) {
			return $scalarValue;
		}
		
		return $this->eiFieldValueBuilder->__invoke($scalarValue);
	}
}

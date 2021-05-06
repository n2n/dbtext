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
namespace rocket\impl\ei\component\prop\translation\model;

use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\model\ToManyEiField;

class TranslationEiField extends ToManyEiField {
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::copyEiField($eiObject)
	 */
	public function copyEiField(Eiu $copyEiu) {
		$copy = parent::copyEiField($copyEiu);
		
		if ($copy === null) return null;
		
		$value = $this->getValue();
		$valueCopy = $copy->getValue();

		foreach ($value as $key => $targetRelationEntry) {
			$valueCopy[$key] = $valueCopy[$key]->getEiObject()->getEiEntityObj()->getEntityObj()->setN2nLocale(
					$targetRelationEntry->getEiObject()->getEiEntityObj()->getEntityObj()->getN2nLocale());
		}
		
		return $copy;
	}
	
	public function copyValue(Eiu $copyEiu) {
		$valueCopy = parent::copyValue($copyEiu);
		
		if ($valueCopy === null) return null;
		
		$value = $this->getValue();
		
		foreach ($value as $key => $targetRelationEntry) {
			$valueCopy[$key] = $valueCopy[$key]->getEiObject()->getEiEntityObj()->getEntityObj()->setN2nLocale(
					$targetRelationEntry->getEiObject()->getEiEntityObj()->getEntityObj()->getN2nLocale());
		}
		
		return $valueCopy;
	}
}

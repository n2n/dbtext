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
namespace rocket\impl\ei\component\prop\meta;

use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\impl\ei\component\prop\adapter\DisplayableEiPropAdapter;
use rocket\si\content\impl\SiFields;
use rocket\ei\manage\idname\IdNameProp;
use rocket\ei\component\prop\IdNameEiProp;
use rocket\ei\util\factory\EifGuiField;
use rocket\si\content\impl\meta\SiCrumb;

class TypeEiProp extends DisplayableEiPropAdapter implements IdNameEiProp {
	
	protected function prepare() {
	}
	
	public function buildDisplayDefinition(Eiu $eiu): ?DisplayDefinition {
// 		$eiu->prop()->getLabel();
// 		$eiu->prop()->getHelpText();
		return $this->getDisplayConfig()->toDisplayDefinition($eiu->guiFrame()->getViewMode(),
				$eiu->prop()->getLabel());
	}

	public function createOutEifGuiField(Eiu $eiu): EifGuiField {
		$eiuMask = $eiu->context()->mask($eiu->entry()->getEiEntry()->getEiType());
		$iconType = $eiuMask->getIconType();
		$label = $eiuMask->getLabel();
		
		if (null === $iconType) {
			return SiFields::stringOut($label);
		}
		
		return $eiu->factory()->newGuiField(SiFields::crumbOut(SiCrumb::createIcon($iconType), 
				SiCrumb::createLabel($label)));
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			$eiMask = $this->getEiMask();
			$eiObject = $eiu->object()->getEiObject();
			if (!$eiMask->getEiType()->equals($eiObject->getEiEntityObj()->getEiType())) {
				$eiMask = $eiObject->getEiEntityObj()->getEiType()->getEiMask();
			}
			
			return $eiMask;
		})->toIdNameProp();
	}

}

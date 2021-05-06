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
namespace rocket\impl\ei\component\prop\string;

use n2n\reflection\property\AccessProxy;
use n2n\util\StringUtils;
use n2n\util\type\ArgUtils;
use n2n\util\type\TypeConstraint;
use rocket\ei\component\prop\FieldEiProp;
use rocket\ei\component\prop\IdNameEiProp;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\idname\IdNameProp;
use rocket\ei\util\Eiu;
use rocket\ei\util\factory\EifGuiField;
use rocket\impl\ei\component\prop\adapter\DisplayablePropertyEiPropAdapter;
use rocket\impl\ei\component\prop\adapter\config\ObjectPropertyConfigurable;
use rocket\impl\ei\component\prop\adapter\gui\GuiFieldFactory;
use rocket\si\content\impl\SiFields;

class StringDisplayEiProp extends DisplayablePropertyEiPropAdapter implements ObjectPropertyConfigurable, 
		FieldEiProp, GuiFieldFactory, IdNameEiProp {
	
	function prepare() {
		$this->getDisplayConfig()->setCompatibleViewModes(ViewMode::read());
	}
	
	function isEntityPropertyRequired(): bool {
		return false;
	}

	function setObjectPropertyAccessProxy(?AccessProxy $objectPropertyAccessProxy) {
		ArgUtils::assertTrue($objectPropertyAccessProxy !== null);
		$objectPropertyAccessProxy->setConstraint(TypeConstraint::createSimple('string', true));
		parent::setObjectPropertyAccessProxy($objectPropertyAccessProxy);
	}


	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			return StringUtils::reduce($eiu->object()->readNativValue($this), 30, '..');
		});
	}

	function createOutEifGuiField(Eiu $eiu): EifGuiField {
		return $eiu->factory()->newGuiField(SiFields::stringOut($eiu->field()->getValue()));
	}
}

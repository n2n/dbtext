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
namespace rocket\impl\ei\component\prop\adapter\idname;

use n2n\l10n\N2nLocale;
use rocket\ei\manage\idname\IdNameProp;
use rocket\ei\util\Eiu;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;

class ClosureIdNameProp implements IdNameProp {
	private $function;
	
	function __construct(\Closure $callback) {
		$this->function = new \ReflectionFunction($callback);
	}
	
	function buildIdentityString(Eiu $eiu, N2nLocale $n2nLocale): ?string {
		$mmi = new MagicMethodInvoker($eiu->getN2nContext());
		$mmi->setClassParamObject(Eiu::class, $eiu);
		$mmi->setClassParamObject(N2nLocale::class, $n2nLocale);
		$mmi->setReturnTypeConstraint(TypeConstraints::scalar(true));
		
		return $mmi->invoke(null, $this->function);
	}	
}
// <?php
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\impl\ei\component\modificator\l10n;

// use rocket\impl\ei\component\modificator\adapter\EiModificatorAdapter;
// use rocket\ei\manage\frame\EiFrame;
// use rocket\ei\manage\entry\EiEntry;
// use rocket\ei\manage\entry\OnWriteMappingListener;
// use rocket\impl\ei\component\prop\l10n\N2nLocaleEiProp;
// use n2n\l10n\N2nLocale;

// class N2nLocaleEiModificator extends EiModificatorAdapter {
// 	private $eiProp;
	
// 	public function __construct(N2nLocaleEiProp $eiProp) {
// 		$this->eiProp = $eiProp;
// 	}
	
// 	public function setupEiEntry(Eiu $eiu) {
// 		if ($this->eiProp->isMultiLingual()) return;
// 		if (!$eiEntry->getEiObject()->isNew()) return;
// 		$that = $this;
// 		$eiEntry->registerListener(new OnWriteMappingListener(function() 
// 				use ($eiFrame, $eiEntry, $that) {
// 			$eiEntry->setValue($that->eiProp->getId(), N2nLocale::getDefault());
// 		}));
// 	}
// }

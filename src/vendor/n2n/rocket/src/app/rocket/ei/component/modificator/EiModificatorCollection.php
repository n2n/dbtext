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
namespace rocket\ei\component\modificator;

use rocket\ei\component\EiComponentCollection;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use rocket\ei\EiModificatorPath;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\util\Eiu;

class EiModificatorCollection extends EiComponentCollection {
	/**
	 * @param EiType $eiType
	 */
	public function __construct(EiMask $eiMask) {
		parent::__construct('EiModificator', EiModificator::class);
		$this->setEiMask($eiMask);
	}

	public function getById(string $id): EiModificator {
		return $this->getEiComponentById($id);
	}
		
	/**
	 * @param EiModificator $eiModificator
	 * @param bool $prepend
	 * @return EiModificatorWrapper
	 */
	public function add(EiModificator $eiModificator, string $id = null, bool $prepend = false) {
		$eiModificatorPath = new EiModificatorPath($this->makeId($id, $eiModificator));
		$eiModificatorWrapper = new EiModificatorWrapper($eiModificatorPath, $eiModificator, $this);
		
		$this->addElement($eiModificatorPath, $eiModificator);
		
		return $eiModificatorWrapper;
	}
	
	/**
	 * @param IndependentEiModificator $independentEiModificator
	 * @param string $id
	 * @return \rocket\ei\component\modificator\EiModificatorWrapper
	 */
	public function addIndependent(string $id, IndependentEiModificator $independentEiModificator) {
		$eiModificatorWrapper = $this->add($independentEiModificator, $id);
		$this->addIndependentElement($eiModificatorWrapper->getEiModificatorPath(), $independentEiModificator);
		return $eiModificatorWrapper;
	}
	
	function setupEiGuiFrame(EiGuiFrame $eiGuiFrame) {
		if ($this->isEmpty()) {
			return;
		}
		
		$eiu = new Eiu($eiGuiFrame);
		foreach ($this as $eiModificator) {
			$eiModificator->setupEiGuiFrame($eiu);
		}
	}
	
}

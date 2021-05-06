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
namespace rocket\impl\ei\component\prop\adapter;

use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use rocket\ei\component\prop\EiProp;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\component\prop\indepenent\IndependentEiProp;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;

abstract class IndependentEiPropAdapter extends EiPropAdapter implements IndependentEiProp {
	protected $parentEiProp;
	protected $labelLstr;
	
	/**
	 * @var AdaptableEiPropConfigurator
	 */
	private $configurator;
	
	function __construct() {
	}
	
	/**
	 * @return AdaptableEiPropConfigurator 
	 */
	protected function getConfigurator() {
		if ($this->configurator !== null) {
			return $this->configurator;
		}
		
		$this->configurator = $this->createConfigurator();
		$this->prepare();
		return $this->configurator;
	}
	
	protected function createConfigurator(): AdaptableEiPropConfigurator {
		return new AdaptableEiPropConfigurator($this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\IndependentEiProp::createEiPropConfigurator()
	 */
	public function createEiPropConfigurator(): EiPropConfigurator {
		return $this->getConfigurator();
	}
	
	protected abstract function prepare();
	
	public function getLabelLstr(): Lstr {
		return $this->labelLstr ?? parent::getLabelLstr();
	}

	public function setLabelLstr(Lstr $labelLstr) {
		$this->labelLstr = $labelLstr;
	}
	
	public function equals($obj) {
		return $obj instanceof EiProp && parent::equals($obj);
	}
}

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
namespace rocket\impl\ei\component\command\adapter;

use rocket\ei\component\command\IndependentEiCommand;
use rocket\ei\component\EiConfigurator;
use rocket\impl\ei\component\config\AdaptableEiConfigurator;
use rocket\impl\ei\component\command\adapter\config\AdaptableEiCommandConfigurator;

abstract class IndependentEiCommandAdapter extends EiCommandAdapter implements IndependentEiCommand {
	
	/**
	 * @var AdaptableEiConfigurator
	 */
	private $configurator;
	
	function __construct() {
	}
	
	/**
	 * @return AdaptableEiCommandConfigurator
	 */
	protected function getConfigurator() {
		if ($this->configurator !== null) {
			return $this->configurator;
		}
		
		$this->configurator = $this->createConfigurator();
		$this->prepare();
		return $this->configurator;
	}
	
	protected function createConfigurator(): AdaptableEiCommandConfigurator {
		return new AdaptableEiCommandConfigurator($this);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\IndependentEiProp::createEiPropConfigurator()
	 */
	public function createEiConfigurator(): EiConfigurator {
		return $this->getConfigurator();
	}
	
	protected abstract function prepare();
	
	public function equals($obj) {
		return $obj instanceof IndependentEiCommand && parent::equals($obj);
	}
}

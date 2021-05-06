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
namespace rocket\impl\ei\component\command\adapter\config;

use rocket\impl\ei\component\config\AdaptableEiConfigurator;
use rocket\impl\ei\component\config\EiConfiguratorAdaption;

class AdaptableEiCommandConfigurator extends AdaptableEiConfigurator {
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\config\EiConfiguratorAdapter::getTypeName()
	 */
	function getTypeName(): string {
		return self::shortenTypeName(parent::getTypeName(), array('Ei', 'Command'));
	}
	
	/**
	 * @param EiConfiguratorAdaption $adaption
	 * @return \rocket\impl\ei\component\command\adapter\config\AdaptableEiCommandConfigurator
	 */
	function addAdaption(EiConfiguratorAdaption $adaption) {
		$this->registerAdaption($adaption);
		return $this;
	}
	
	/**
	 * @param EiConfiguratorAdaption $adaption
	 * @return \rocket\impl\ei\component\command\adapter\config\AdaptableEiCommandConfigurator
	 */
	function removeAdaption(EiConfiguratorAdaption $adaption) {
		$this->unregisterAdaption($adaption);
		return $this;
	}
	
	/**
	 * @param \Closure $setupCallback
	 * @return \rocket\impl\ei\component\command\adapter\config\AdaptableEiCommandConfigurator
	 */
	function addSetupCallback(\Closure $setupCallback) {
		$this->registerSetupCallback($setupCallback);
		return $this;
	}
	
	/**
	 * @param \Closure $setupCallback
	 * @return \rocket\impl\ei\component\command\adapter\config\AdaptableEiCommandConfigurator
	 */
	function removeSetupCallback(\Closure $setupCallback) {
		$this->unregisterSetupCallback($setupCallback);
		return $this;
	}
}

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
namespace rocket\impl\ei\component\config;

use n2n\core\container\N2nContext;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagCollection;
use n2n\web\dispatch\mag\MagDispatchable;
use rocket\ei\component\EiConfigurator;
use rocket\ei\component\EiSetup;
use rocket\ei\util\Eiu;

class AdaptableEiConfigurator extends EiConfiguratorAdapter implements EiConfigurator {
	
	/**
	 * @var EiConfiguratorAdaption[]
	 */
	private $adapations = [];
	
	/**
	 * @var \Closure[]
	 */
	private $setupCallbacks = [];
	
	public function setup(EiSetup $eiSetupProcess) {
		$eiu = $eiSetupProcess->eiu();
		
		foreach ($this->adapations as $adaption) {
			$adaption->setup($eiu, $this->dataSet);
		}
		
		foreach ($this->setupCallbacks as $setupCallback) {
			$setupCallback($eiu, $this->dataSet);
		}
	}
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$magCollection = new MagCollection();
		
		$eiu = new Eiu($n2nContext, $this->eiComponent);
		foreach ($this->adapations as $adaption) {
			$adaption->mag($eiu, $this->dataSet, $magCollection);
		}
		
		return new MagForm($magCollection);
	}
	
	function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		parent::saveMagDispatchable($magDispatchable, $n2nContext);
		
		$eiu = new Eiu($n2nContext, $this->eiComponent);
		$magCollection = $magDispatchable->getMagCollection();
		foreach ($this->adapations as $adaption) {
			$adaption->save($eiu, $magCollection, $this->dataSet);
		}
	}
	
	/**
	 * @param EiConfiguratorAdaption $adaption
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	protected function registerAdaption(EiConfiguratorAdaption $adaption) {
		$this->adapations[spl_object_hash($adaption)] = $adaption;
		return $this;
	}
	
	/**
	 * @param EiConfiguratorAdaption $adaption
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	protected function unregisterAdaption(EiConfiguratorAdaption $adaption) {
		unset($this->adapations[spl_object_hash($adaption)]);
		return $this;
	}
	
	protected function getAdaptions() {
		return $this->adapations;
	}
	
	/**
	 * @param \Closure $setupCallback
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	protected function registerSetupCallback(\Closure $setupCallback) {
		$this->setupCallbacks[spl_object_hash($setupCallback)] = $setupCallback;
		return $this;
	}
	
	/**
	 * @param \Closure $setupCallback
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	protected function unregisterSetupCallback(\Closure $setupCallback) {
		unset($this->setupCallbacks[spl_object_hash($setupCallback)]);
		return $this;
	}
	
	/**
	 * @return \Closure[]
	 */
	protected function getSetupCallbacks() {
		return $this->setupCallbacks;
	}
}

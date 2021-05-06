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
namespace rocket\spec\source;

use n2n\core\VarStore;
use n2n\config\source\impl\JsonFileConfigSource;
use rocket\core\model\Rocket;
use n2n\core\container\N2nContext;
use n2n\config\source\WritableConfigSource;

class N2nContextRocketConfigSource implements RocketConfigSource {
	const ROCKET_CONFIG_FOLDER = 'rocket';
	const LAYOUT_CONFIG_FILE = 'layout.json';
	const SCRIPT_CONFIG_FILE = 'specs.json';
	const COMPONENT_STORAGE_FILE = 'components.json';
	
	private $n2nContext;
	private $layoutConfigSource;
	private $scriptsConfigSource;
	private $elementsConfigSource;

	public function __construct(N2nContext $n2nContext) {
		$this->n2nContext = $n2nContext;
	}
	
	public function getLayoutConfigSource(): WritableConfigSource {
		if ($this->layoutConfigSource === null) {
			$this->layoutConfigSource = new JsonFileConfigSource($this->n2nContext->getVarStore()
					->requestFileFsPath(VarStore::CATEGORY_SRV, Rocket::NS,
							null, self::LAYOUT_CONFIG_FILE, true, true));
		}
		
		return $this->layoutConfigSource;
	}
	
	public function getSpecsConfigSource(): ModularConfigSource {
		if ($this->scriptsConfigSource === null) {
			$this->scriptsConfigSource = new VarStoreConfigSource($this->n2nContext->getVarStore(), 
					self::ROCKET_CONFIG_FOLDER, self::SCRIPT_CONFIG_FILE);
		}
		
		return $this->scriptsConfigSource;
	}

	public function getElementsConfigSource() {
		if ($this->elementsConfigSource === null) {
			$this->elementsConfigSource = new VarStoreConfigSource($this->n2nContext->getVarStore(),
					self::ROCKET_CONFIG_FOLDER, self::COMPONENT_STORAGE_FILE);
		}
		
		return $this->elementsConfigSource;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\spec\RocketConfigSource::getConfiguratedModules()
	 */
	public function getModuleNamespaces() {
		return $this->n2nContext->getModuleManager()->getModules();
	}
}

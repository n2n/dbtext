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
use n2n\config\source\WritableConfigSource;

class VarStoreConfigSource implements ModularConfigSource {
	const ROCKET_CONFIG_FOLDER = 'rocket';
	const LAYOUT_CONFIG_FILE = 'manage.json';
	const SCRIPT_CONFIG_FILE = 'specs.json';
	const COMPONENT_STORAGE_FILE = 'elements.json';
	
	private $varStore;
	private $folderName;
	private $fileName;
	
	private $configSources = array();

	public function __construct(VarStore $varStore, $folderName, $fileName) {
		$this->varStore = $varStore;
		$this->folderName = $folderName;
		$this->fileName = $fileName;
	}
	
	public function getOrCreateConfigSourceByModuleNamespace($module): WritableConfigSource {
		$namespace = (string) $module;
		
		if (isset($this->configSources[$namespace])) {
			return $this->configSources[$namespace];
		}
		
		return $this->configSources[$namespace] = new JsonFileConfigSource(
				$this->varStore->requestFileFsPath(VarStore::CATEGORY_ETC, $namespace, 
						$this->folderName, $this->fileName, true, true, true));
	}

	public function containsModuleNamespace($moduleNamespace): bool {
		$namespace = (string) $moduleNamespace;
		
		return isset($this->configSources[$namespace]) 
				|| $this->varStore->requestFileFsPath(VarStore::CATEGORY_ETC, $namespace, 
						$this->folderName, $this->fileName, false, false, false)->exists();
	}
	
	public function hashCode(): string {
		$hashCode = '';
		foreach ($this->configSources as $ns => $configSource) {
			$csHashCode = $configSource->hashCode();
			if ($csHashCode === null) return null;
			
			$hashCode .=  ':' . $ns . ':' . $csHashCode; 
		}
		return md5($hashCode);
	}
}

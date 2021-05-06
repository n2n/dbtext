<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\persistence\meta\structure\common;

use n2n\persistence\meta\structure\MetaEntity;

use n2n\persistence\meta\Database;
use n2n\reflection\ReflectionUtils;

abstract class MetaEntityAdapter implements MetaEntity {
	private $name;
	/**
	 * @var Database
	 */
	private $database;
	private $attrs;

	/**
	 * @var MetaEntityChangeListener []
	 */
	private $changeListeners;
	
	public function __construct(string $name) {
		$this->name = $name;
		
		if ($name == 'comptusch') {
			if (ReflectionUtils::atuschBreak(2)) {
				//throw new e;	
			}
		}
		$this->changeListeners = array();
		$this->attrs = array();
	}
	
	public function getName(): string {
		return $this->name;
	}
	
	public function setName(string $name) {
		$originalName = null;
		
		if ($this->name !== $name) {
			$originalName = $this->name;
		}
		
		$this->name = $name;
		
		if (null !== $originalName) {
			$this->triggerNameChangeListeners($originalName);
		}
	}
	/** 
	 * @return \n2n\persistence\meta\Database
	 */
	public function getDatabase(): Database {
		return $this->database;
	}
	
	public function setDatabase(Database $database) {
		$this->database = $database;
	}
	
	public function getAttrs(): array {
		return $this->attrs;
	}
	
	public function setAttrs(array $attrs) {
		$this->attrs = $attrs;
	} 
	
	public function registerChangeListener(MetaEntityChangeListener $changeListener) {
		$this->changeListeners[spl_object_hash($changeListener)] = $changeListener;
	}
	
	public function unregisterChangeListener(MetaEntityChangeListener $changeListener) {
		unset($this->changeListeners[spl_object_hash($changeListener)]);
	}
	
	protected function triggerChangeListeners() {
		foreach($this->changeListeners as $changeListener) {
			$changeListener->onMetaEntityChange($this);
		}
	}
	
	protected function triggerNameChangeListeners(string $originalName) {
		foreach($this->changeListeners as $changeListener) {
			$changeListener->onMetaEntityNameChange($originalName, $this);
		}
	}
	
	public function equals($obj): bool {
		return get_class($obj) === get_class($this) && $obj->getName() === $this->name;
	}
}
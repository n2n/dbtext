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
namespace n2n\persistence\orm\store\action;

use n2n\persistence\orm\model\EntityModel;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\property\EntityProperty;

abstract class EntityActionAdapter extends ActionAdapter implements EntityAction {
	protected $actionQueue;
	protected $entityModel;
	protected $id;
	protected $entity;
	protected $supplied = false;
	protected $disabled = false;
	protected $attrs = array();
	protected $onDisableClosures = array();
	
	public function __construct(ActionQueue $actionQueue, EntityModel $entityModel, $id, $entity) {
		ArgUtils::assertTrue(is_object($entity));
		$this->actionQueue = $actionQueue;
		$this->entityModel = $entityModel;
		$this->id = $id;
		$this->entity = $entity;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\EntityAction::getActionQueue()
	 */
	public function getActionQueue() {
		return $this->actionQueue;	
	}
	
	public function getEntityModel() {
		return $this->entityModel;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getEntityObj() {
		return $this->entity;
	}
	
	public function isSupplied(): bool {
		return $this->supplied;
	}
	
	public function setSupplied(bool $supplied) {
		$this->supplied = $supplied;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\PersistAction::executeOnDisable()
	 */
	public function executeOnDisable(\Closure $closure) {
		$this->onDisableClosures[] = $closure;
	}
	/**
	 * 
	 */
	public function disable() {
		IllegalStateException::assertTrue(!$this->supplied && !$this->executed);
		
		$this->disabled = true;
		while (null !== ($closure = array_shift($this->onDisableClosures))) {
			$closure($this);
		}
	}
	
	public function isDisabled(): bool {
		return $this->disabled;
	}
	
	public function execute() {
		if ($this->disabled) return;
		
		parent::execute();
	}

	public function setAttr(EntityProperty $entityProperty, $name, $value) {
		$this->attrs[$entityProperty->toPropertyString() . $name] = $value;
	}
	
	public function getAttr(EntityProperty $entityProperty, $name) {
		$attrName = $entityProperty->toPropertyString() . $name;
		if (isset($this->attrs[$attrName])) {
			return $this->attrs[$attrName];
		}
		
		return null;
	}
}

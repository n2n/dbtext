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

use n2n\util\ex\UnsupportedOperationException;
use n2n\persistence\orm\model\EntityModel;

class UninitializedPersistAction implements PersistAction {
	private $entityModel;
	private $id;	
	
	public function __construct(EntityModel $entityModel, $id) {
		$this->entityModel = $entityModel;
		$this->id = $id;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\EntityAction::getEntityModel()
	 */
	public function getEntityModel() {
		return $this->entityModel;
	}
	
	public function getId() {
		return $this->id;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\PersistAction::hasId()
	 */
	public function hasId() {
		return $this->id !== null;
	}
	
	public function isNew() {
		return false;
	}
	
	public function isInitialized() {
		return false;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\PersistAction::getMeta()
	 */
	public function getMeta() {
		throw new UnsupportedOperationException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\PersistAction::executeOnDisable()
	 */
	public function executeOnDisable(\Closure $closure) {
		throw new UnsupportedOperationException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\EntityAction::getActionQueue()
	 */
	public function getActionQueue() {
		throw new UnsupportedOperationException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\Action::execute()
	 */
	public function execute() {
		throw new UnsupportedOperationException();
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\Action::executeAtStart()
	 */
	public function executeAtStart(\Closure $closure) {
		throw new UnsupportedOperationException();
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\Action::executeAtEnd()
	 */
	public function executeAtEnd(\Closure $closure) {
		throw new UnsupportedOperationException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\Action::addDependent()
	 */
	public function addDependent(\n2n\persistence\orm\store\action\Action $actionJob) {
		throw new UnsupportedOperationException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\Action::getDependents()
	 */
	public function getDependents() {
		throw new UnsupportedOperationException();
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\Action::setDependents()
	 */
	public function setDependents(array $dependents) {
		throw new UnsupportedOperationException();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\EntityAction::setAttr()
	 */
	public function setAttr(\n2n\persistence\orm\property\EntityProperty $entityProperty, $name, $value) {
		throw new UnsupportedOperationException();
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\EntityAction::getAttr()
	 */
	public function getAttr(\n2n\persistence\orm\property\EntityProperty $entityProperty, $name) {
		throw new UnsupportedOperationException();		
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\EntityAction::isDisabled()
	 */
	public function isDisabled(): bool {
		throw new UnsupportedOperationException();
	}

	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\EntityAction::disable()
	 */
	public function disable() {
		throw new UnsupportedOperationException();
	}
	
	public function isSupplied(): bool {
		throw new UnsupportedOperationException();
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\EntityAction::disable()
	 */
	public function setSupplied(bool $supplied) {
		throw new UnsupportedOperationException();
	}	
}

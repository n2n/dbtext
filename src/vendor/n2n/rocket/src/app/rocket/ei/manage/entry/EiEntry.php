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
namespace rocket\ei\manage\entry;

use n2n\l10n\Message;
use rocket\ei\manage\EiObject;
use rocket\ei\EiPropPath;
use n2n\util\col\HashSet;
use rocket\ei\mask\EiMask;
use n2n\reflection\magic\MagicMethodInvoker;
use rocket\ei\util\Eiu;
use n2n\core\container\N2nContext;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\security\EiEntryAccess;

class EiEntry {
	/**
	 * @var EiObject
	 */
	private $eiObject;
	/**
	 * @var EiMask
	 */
	private $eiMask;
	/**
	 * @var EiFieldMap
	 */
	private $eiFieldMap;
	/**
	 * @var EiEntryAccess
	 */
	private $eiEntryAccess;
	/**
	 * @var EiEntryValidationResult
	 */
	private $validationResult;
// 	private $accessible = true;
	private $listeners = array();
	private $constraintSet;
	
	public function __construct(EiObject $eiObject, EiMask $eiMask) {
		$this->eiObject = $eiObject;
		$this->eiMask = $eiMask;
		$this->eiFieldMap = new EiFieldMap($this, new EiPropPath([]), $eiObject->getEiEntityObj()->getEntityObj());;
		$this->constraintSet = new HashSet(EiEntryConstraint::class);
	}
	
	/**
	 * @return string|null
	 */
	public function getPid() {
		$eiEntityObj = $this->eiObject->getEiEntityObj();
		if (!$eiEntityObj->isPersistent()) return null;
		
		return $this->getEiMask()->getEiType()->idToPid($eiEntityObj->getId());
	}
	
	/**
	 * @return mixed|null
	 */
	public function getId() {
		$eiEntityObj = $this->eiObject->getEiEntityObj();
		if (!$eiEntityObj->isPersistent()) return null;
		
		return $eiEntityObj->getId();
	}
	
	/**
	 * @return boolean
	 */
	public function isNew() {
		return $this->eiObject->isNew();
	}
	
	/**
	 * @return EiMask
	 */
	function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @return \rocket\ei\EiType
	 */
	function getEiType() {
		return $this->eiMask->getEiType();
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiFieldMap
	 */
	function getEiFieldMap() {
		return $this->eiFieldMap;
	}
	
	/**
	 * @return \rocket\ei\manage\security\EiEntryAccess
	 */
	function getEiEntryAccess() {
		if ($this->eiEntryAccess !== null) {
			return $this->eiEntryAccess;
		}
		
		throw new IllegalStateException($this . ' has no EiEntryAccess assigned.');
	}
	
	/**
	 * @param EiEntryAccess $eiEntryAccess
	 */
	function setEiEntryAccess(EiEntryAccess $eiEntryAccess) {
		$this->eiEntryAccess = $eiEntryAccess;
	}
	
// 	/**
// 	 * @param bool $accessible
// 	 */
// 	public function setAccessible(bool $accessible) {
// 		$this->accessible = $accessible;
// 	}
	
// 	/**
// 	 * @return bool
// 	 */
// 	public function isAccessible(): bool {
// 		return $this->accessible;
// 	}
	
// 	/**
// 	 * @param bool $ignoreAccessRestriction
// 	 * @throws InaccessibleEntryException
// 	 */
// 	private function ensureAccessible($ignoreAccessRestriction) {
// 		if ($this->accessible || $ignoreAccessRestriction) {
// 			return;
// 		}
		
// 		throw new InaccessibleEntryException();
// 	}
	
	/**
	 * @return \n2n\util\col\Set EiEntryConstraint
	 */
	public function getConstraintSet() {
		return $this->constraintSet;
	}
	
// 	public function getEiCommandAccessRestrictors()  {
// 		return $this->eiCommandAccessRestrictors;
// 	}
	
// 	public function isExecutableBy(EiCommandPath $eiCommandPath) {
// 		foreach ($this->eiCommandAccessRestrictors as $eiExecutionRestrictor) {
// 			if (!$eiExecutionRestrictor->isAccessibleBy($eiCommandPath)) {
// 				return false;
// 			}
// 		}
	
// 		return true;
// 	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiField
	 */
	function getEiField(EiPropPath $eiPropPath) {
		return $this->getEiFieldWrapper($eiPropPath)->getEiField();
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @throws UnknownEiFieldExcpetion
	 * @return \rocket\ei\manage\entry\EiFieldWrapper
	 */
	function getEiFieldWrapper(EiPropPath $eiPropPath) {
		$ids = $eiPropPath->toArray();
		$passedIds = [];
		$eiFieldWrapper = null;
		
		$eiFieldMap = $this->eiFieldMap;
		while (null !== ($passedIds[] = $id = array_shift($ids))) {
			try {
				$eiFieldWrapper = $eiFieldMap->getWrapper($id);
				if (empty($ids)) return $eiFieldWrapper;
			} catch (\rocket\ei\manage\entry\UnknownEiFieldExcpetion $e) {
				throw new UnknownEiFieldExcpetion('No EiField defined for EiPropPath: ' . (new EiPropPath($passedIds)));
			}
			
			$eiFieldMap = $eiFieldWrapper->getEiField()->getForkedEiFieldMap();
			if ($eiFieldMap !== null) continue;
			
			throw new UnknownEiFieldExcpetion('No EiField defined for EiPropPath: ' . (new EiPropPath(
					array_merge($passedIds, [reset($ids)]))));
		}
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return boolean
	 */
	function containsEiField(EiPropPath $eiPropPath) {
		try {
			$this->getEiFieldWrapper($eiPropPath);
			return true;
		} catch (\rocket\ei\manage\entry\UnknownEiFieldExcpetion $e) {
			return false;
		}
	}
	
	
	// 	public function read($entity, EiPropPath $eiPropPath) {
	
	// 	}
	
	// 	public function readAll($entity) {
	// 		$values = array();
	// 		foreach ($this->eiFields as $id => $eiField) {
	// 			if ($eiField->isReadable()) {
	// 				$values[$id] = $eiField->read($entity);
	// 			}
	// 		}
	// 		return $values;
	// 	}
	
	
	public function registerListener(EiEntryListener $listener, $relatedFieldId = null) {
		$objectHash = spl_object_hash($listener);
		$this->listeners[$objectHash] = $listener;
		if (!isset($this->listenerBindings[$relatedFieldId])) {
			$this->listenerBindings[$relatedFieldId][$objectHash] = $listener;
		}
	}
	
// 	public function getFieldRelatedListeners($fieldId) {
// 		if (isset($this->listenerBindings[$fieldId])) {
// 			return $this->listenerBindings[$fieldId];
// 		}
	
// 		return array();
// 	}
	
	public function unregisterListener(EiEntryListener $listener) {
		$objectHash = spl_object_hash($listener);
		unset($this->listeners[$objectHash]);
		foreach ($this->listenerBindings as $fieldId => $listeners) {
			unset($this->listenerBindings[$fieldId][$objectHash]);
		}
	}
	
// 	public function unregisterFieldRelatedListeners($fieldId) {
// 		unset($this->listenerBindings[$fieldId]);
// 	}
		
	public function write() {
		foreach ($this->listeners as $listener) {
			$listener->onWrite($this);
		}
	
		$this->eiFieldMap->write();
		
		foreach ($this->listeners as $listener) {
			$listener->written($this);
		}
	}
	
	private function flush() {
		foreach ($this->listeners as $listener) {
			$listener->flush($this);
		}
	}
	
	/**
	 * @return boolean
	 */
	function hasChanges() {
		foreach ($this->eiFieldMap->getWrappers() as $wrapper) {
			if ($wrapper->hasChanges()) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param mixed $value
	 * @return boolean
	 */
	public function acceptsValue(EiPropPath $eiPropPath, $value) {
		foreach ($this->constraintSet as $constraint) {
			if (!$constraint->acceptsValue($eiPropPath, $value)) return false;
		}
		return true;
	}
	
	/**
	 * @return \rocket\ei\manage\EiObject
	 */
	public function getEiObject(): EiObject {
		return $this->eiObject;
	}
	
	public function getValue(EiPropPath $eiPropPath) {
		return $this->getEiFieldWrapper($eiPropPath)->getValue();
	}
	
	public function setValue(EiPropPath $eiPropPath, $value, bool $regardSecurity = true) {
		$this->getEiFieldWrapper($eiPropPath)->setValue($value, $regardSecurity);
	}

	public function getOrgValue(EiPropPath $eiPropPath) {
		return $this->getMappingProfile()->getEiField($eiPropPath)->getOrgValue();
	}
	
	public function save(): bool {
		if (!$this->validate()) return false;
		$this->write();
		$this->flush();
		return true;
	}
	
	public function isValid() {
		if (!$this->eiFieldMap->isValid()) return false;
		
		if (null !== ($eiEntryConstraint = $this->getEiEntryAccess()->getEiEntryConstraint())) {
			if (!$eiEntryConstraint->check($this)) return false;
		}
		
		foreach ($this->constraintSet as $constraint) {
			if (!$constraint->check($this)) return false;
		}
		
		return true;
	}
	
	public function validate(EiEntryValidationResult $validationResult = null): bool {
		if ($validationResult === null) {
			$validationResult = $this->validationResult = new EiEntryValidationResult();	
		}
		
		foreach ($this->listeners as $listener) {
			$listener->onValidate($this);
		}
		
		$this->eiFieldMap->validate($validationResult);
				
		if (null !== ($eiEntryConstraint = $this->getEiEntryAccess()->getEiEntryConstraint())) {
			$eiEntryConstraint->validate($this, $validationResult);
		}
		
		foreach ($this->constraintSet as $constraint) {
			$constraint->validate($this, $validationResult);
		}
		
		foreach ($this->listeners as $listener) {
			$listener->validated($this);
		}
		
		return $validationResult->isValid();
	}
	
	/**
	 * @return bool
	 */
	public function hasValidationResult(): bool {
		return $this->validationResult !== null;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiEntryValidationResult
	 */
	public function getValidationResult() {
		if ($this->validationResult === null) {
			throw new IllegalStateException('EiEntry has no ValidationResult.');
		}
		
		return $this->validationResult;
	}
	
	/**
	 * @param EiEntry $targetMapping
	 */
	public function copy(EiEntry $targetMapping) {
		$targetMappingDefinition = $targetMapping->getMappingDefinition();
		$targetType = $targetMapping->getEiObject()->getType();
		foreach ($targetMappingDefinition->getIds() as $id) {
			if (!$this->mappingProfile->containsId($id)) continue;

			$targetMapping->setValue($id, $this->getValue($id));
		}
	}
	
	public function equals($obj) {
		return $obj instanceof EiEntry && $this->determineEiType()->equals($obj->determineEiType())
				&& $this->eiObject->equals($obj->getEiObject());
	}
	
	public function toEntryNavPoint() {
		return $this->eiObject->toEntryNavPoint($this->contextEiType);
	}
	
	public function __toString() {
		if ($this->eiObject->isDraft()) {
			return 'EiEntry (' . $this->eiObject->getDraft() . ')';
		}
		
		$eiEntityObj = $this->eiObject->getEiEntityObj();
		
		return 'EiEntry (' . $this->eiObject->getEiEntityObj()->getEiType()->getEntityModel()->getClass()->getShortName()
				. '#' . ($eiEntityObj->hasId() ? $eiEntityObj->getPid() : 'new') . ')';
	}
}

class OnWriteMappingListener implements EiEntryListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {
		$this->closure->__invoke($eiEntry);
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {}
	
	public function flush(EiEntry $eiEntry) {}

}

class WrittenMappingListener implements EiEntryListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {
		$this->closure->__invoke($eiEntry);
	}
	
	public function flush(EiEntry $eiEntry) {}
}

class OnValidateMappingListener implements EiEntryListener {
	private $closure;
	private $magicContext;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure, N2nContext $magicContext) {
		$this->closure = $closure;
		$this->magicContext = $magicContext;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) { 
		$mmi = new MagicMethodInvoker($this->magicContext);
		$mmi->setClassParamObject(Eiu::class, new Eiu($eiEntry, $this->magicContext));
		$mmi->invoke(null, new \ReflectionFunction($this->closure));
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) { }
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {}
	
	public function flush(EiEntry $eiEntry) {}
}

class ValidatedMappingListener implements EiEntryListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) { 
		$this->closure->__invoke($eiEntry);
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {}
	
	public function flush(EiEntry $eiEntry) {}
}

class FlushMappingListener implements EiEntryListener {
	private $closure;
	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure) {
		$this->closure = $closure;
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onValidate()
	 */
	public function onValidate(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::validated()
	 */
	public function validated(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::onWrite()
	 */
	public function onWrite(EiEntry $eiEntry) {}
	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\entry\EiEntryListener::written()
	 */
	public function written(EiEntry $eiEntry) {}
	
	public function flush(EiEntry $eiEntry) {
		$this->closure->__invoke($eiEntry);
	}
}

// class SimpleEiEntryConstraint implements EiEntryConstraint {
// 	private $closure;

// 	public function __construct(\Closure $closure) {
// 		$this->closure = $closure;
// 	}

// 	public function validate(EiEntry $eiEntry) {
// 		if (true === $this->closure->__invoke($eiEntry)) return;
		
// 		$eiObjectMapp
// 	}
// }


class MappingValidationResult {
	private $messages;
	
	public function hasFailed() {
		return 0 < sizeof($this->messages);
	}
	
	public function isValid() {
		return empty($this->messages);
	}
		
	public function addError($id, Message $message) {
		$this->messages[] = $message;
	}
	
	public function getMessages() {
		return $this->messages;
	}
}




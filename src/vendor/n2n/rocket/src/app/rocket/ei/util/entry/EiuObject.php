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

namespace rocket\ei\util\entry;

use rocket\ei\EiPropPath;
use rocket\ei\manage\entry\EiFieldOperationFailedException;
use rocket\ei\manage\EiObject;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\component\prop\EiProp;
use rocket\si\content\SiEntryQualifier;

class EiuObject {
	private $eiObject;
	private $eiuAnalyst;
	
	/**
	 * @param EiObject $eiObject
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiObject $eiObject, EiuAnalyst $eiuAnalyst) {
		$this->eiObject = $eiObject;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return boolean
	 */
	public function isNew() {
		return $this->eiObject->isNew();
	}
	
	/**
	 * @param bool $required
	 * @return NULL|string
	 */
	public function getPid(bool $required = true) {
		$eiEntityObj = $this->eiObject->getEiEntityObj();
		
		if (!$required && !$eiEntityObj->hasId()) {
			return null;
		}
		
		return $eiEntityObj->getPid();
	}
	
	/**
	 * @return \rocket\ei\manage\EiObject
	 */
	public function getEiObject() {
		return $this->eiObject;
	}
	
	/**
	 * @return object
	 */
	public function getEntityObj() {
		return $this->eiObject->getEiEntityObj()->getEntityObj();
	}
	
	/**
	 * @return \rocket\ei\EiType
	 */
	public function getEiType() {
		return $this->eiObject->getEiEntityObj()->getEiType();
	}
	
	/**
	 * @param EiProp $eiProp
	 * @return boolean
	 */
	public function isDraftProp(EiProp $eiProp) {
		return $this->eiObject->isDraft()
				&& $eiProp->getWrapper()->getEiPropCollection()->getEiMask()->getEiEngine()->getDraftDefinition()
						->containsEiPropPath(EiPropPath::from($eiProp));
	}
	
	/**
	 * @param EiProp $eiProp
	 * @return object
	 */
	public function getForkObject(EiProp $eiProp) {
		$eiPropPath = EiPropPath::from($eiProp);
		return $eiProp->getWrapper()->getEiPropCollection()->getEiMask()->getForkObject($eiPropPath->poped(), $this->eiObject);
	}
	
	/**
	 * @param EiProp $eiProp
	 * @throws EiFieldOperationFailedException
	 * @return NULL|mixed
	 */
	public function readNativValue(EiProp $eiProp) {
		if ($this->isDraftProp($eiProp)) {
			return $this->eiObject->getDraft()->getDraftValueMap()->getValue($eiPropPath);
		}
		
		$objectPropertyAccessProxy = $eiProp->getObjectPropertyAccessProxy();
		if ($objectPropertyAccessProxy !== null) {
			 return $objectPropertyAccessProxy->getValue($this->getForkObject($eiProp));
		}
		
		throw new EiFieldOperationFailedException('There is no ObjectPropertyAccessProxy configured for ' . $eiProp);
	}
	
	/**
	 * @param EiProp $eiProp
	 * @return boolean
	 */
	public function isNativeWritable(EiProp $eiProp) {
		if ($this->isDraftProp($eiProp)) {
			return true;
		}
		
		return $this->getObjectPropertyAccessProxy($eiProp)->isWritable();
	}
	
	/**
	 * @param EiProp $eiProp
	 * @throws EiFieldOperationFailedException
	 * @return \n2n\reflection\property\AccessProxy
	 */
	private function getObjectPropertyAccessProxy(EiProp $eiProp) {
		$objectPropertyAccessProxy = $eiProp->getObjectPropertyAccessProxy();
		if ($objectPropertyAccessProxy !== null) {
			return $objectPropertyAccessProxy;
		}
		
		throw new EiFieldOperationFailedException('There is no ObjectPropertyAccessProxy configured for ' . $eiProp);
	}
	
	/**
	 * @param EiProp $eiProp
	 * @param mixed $value
	 * @throws EiFieldOperationFailedException
	 */
	public function writeNativeValue(EiProp $eiProp, $value) {
		if ($this->isDraftProp($eiProp)) {
			$this->eiObject->getDraft()->getDraftValueMap()->setValue($eiPropPath);
			return;
		}
		
		$this->getObjectPropertyAccessProxy($eiProp)->setValue($this->getForkObject($eiProp), $value);
	}
	
	/**
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	public function newEntry() {
		$this->eiuAnalyst->getEiFrame(true);
		return new EiuEntry(null, $this, null, $this->eiuAnalyst);
	}
	
	/**
	 * @return string
	 */
	function createIdentityString() {
		return $this->eiuAnalyst->getEiuFrame(true)->engine()->createIdentityString($this->eiObject);
	}
	
	/**
	 * @param string $name
	 * @return SiEntryQualifier
	 */
	function createSiEntryQualifier(string $name = null) {
		$name = $name ?? $this->createIdentityString();
		
		$siMaskQualifier = $this->eiuAnalyst->getEiuFrame(true)->mask($this->eiObject)->createSiMaskQualifier();
		
		return $this->eiObject->createSiEntryIdentifier()->toQualifier($siMaskQualifier, $name);
	}
}
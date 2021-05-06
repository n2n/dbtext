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
namespace rocket\ei\util\spec;

use rocket\ei\EiType;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\util\entry\EiuObject;
use rocket\spec\UnknownTypeException;

class EiuType  {
	private $eiType;
	private $eiuMask;
	private $eiuAnalyst;
	private $supremeEiuType = null;
	private $allSubEiuTypes = null;
	
	/**
	 * @param EiType $eiType
	 * @param EiuMask $eiuMask
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(EiType $eiType, EiuAnalyst $eiuAnalyst) {
		$this->eiType = $eiType;
		$this->eiuAnalyst = $eiuAnalyst;
	}

	/**
	 * @return \rocket\ei\EiType
	 */
	function getEiType() {
		return $this->eiType;
	}
	
	/**
	 * @return string
	 */
	function getSiTypeId() {
		return $this->getId();
	}
	
	/**
	 * @return string
	 */
	function getId() {
		return $this->eiType->getId();
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuMask
	 */
	function mask() {
		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		return $this->eiuMask = new EiuMask($this->eiType->getEiMask(), null, $this->eiuAnalyst);
	}
	
	/**
	 * @param string $eiTypeExtensionId
	 * @param bool $required
	 * @throws UnknownTypeException
	 * @return void|\rocket\ei\util\spec\EiuMask
	 */
	function extensionMask(string $eiTypeExtensionId, bool $required = true) {
		try {
			return new EiuMask(
					$this->eiType->getEiTypeExtensionCollection()->getById($eiTypeExtensionId)->getEiMask(),
					null, $this->eiuAnalyst);
		} catch (UnknownTypeException $e) {
			if (!$required) return;
				
			throw $e;
		}
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuMask[]
	 */
	function extensionMasks() {
		$eiuMasks = [];
		foreach ($this->eiType->getEiTypeExtensionCollection() as $eiTypeExtension) {
			$eiuMasks[$eiTypeExtension->getId()] = new EiuMask($eiTypeExtension->getEiMask(), null, $this->eiuAnalyst);
		}
		return $eiuMasks;
	}
	
	/**
	 * @return string[] key is EiTypePath
	 */
	function getExtensionMaskOptions() {
		$n2nLocale = $this->eiuAnalyst->getN2nContext(true)->getN2nLocale();
		
		$options = [];
		foreach ($this->eiType->getEiTypeExtensionCollection() as $eiTypeExtension) {
			$options[$eiTypeExtension->getId()] = $eiTypeExtension->getEiMask()->getLabelLstr()->t($n2nLocale);
		}
		return $options;
	}
	
	/**
	 * @return boolean
	 */
	function isAbstract() {
		return $this->eiType->isAbstract();
	}
	
	/**
	 * @param object $entityObj
	 * @return \rocket\ei\util\entry\EiuObject
	 */
	function newObject(object $entityObj = null/*, bool $draft = false*/) {
		$eiObject = null;
		if ($entityObj === null) {
			$eiObject = $this->eiType->createNewEiObject(false /*$draft*/);
		} else {
			$eiObject = LiveEiObject::create($this->eiType, $entityObj);
		}
		
		
// 		if ($draft) {
// 			$loginContext = $this->eiuAnalyst->getN2nContext(true)->lookup(LoginContext::class);
// 			CastUtils::assertTrue($loginContext instanceof LoginContext);
			
// 			$eiObject->getDraft()->setUserId($loginContext->getCurrentUser()->getId());
// 		}
		
		return new EiuObject($eiObject, $this->eiuAnalyst);
	}
	
	
	/**
	 * @return EiuType 
	 */
	function supremeType() {
		if ($this->supremeEiuType !== null) {
			return $this->supremeEiuType;
		}
		
		if (!$this->eiType->hasSuperEiType()) {
			return $this->supremeEiuType = $this;
		}
		
		return $this->supremeEiuType = new EiuType($this->eiType->getSupremeEiType(), $this->eiuAnalyst);
	}
	
	/**
	 * @param string[]|null $allowedSubEiTypeIds
	 * @param bool $includeAbstracts
	 * @return \rocket\ei\util\spec\EiuType[]
	 */
	function possibleTypes(array $allowedSubEiTypeIds = null, bool $includeAbstracts = false) {
		$eiuTypes = [];
		
		if ($this->eiTypeMatches($this->eiType, $allowedSubEiTypeIds, $includeAbstracts)) {
			$eiuTypes[] = $this; 
		}
		
		foreach ($this->eiType->getAllSubEiTypes() as $subEiType) {
			if ($this->eiTypeMatches($subEiType, $allowedSubEiTypeIds, $includeAbstracts)) {
				$eiuTypes[] = new EiuType($subEiType, $this->eiuAnalyst);
			}
		}
		
		return $eiuTypes;
	}
	
	/**
	 * @param EiType $eiType
	 * @param string[]|null $allowedSubEiTypeIds
	 * @param bool $includeAbstractTypes
	 * @return boolean
	 */
	private function eiTypeMatches($eiType, $allowedSubEiTypeIds, $includeAbstractTypes) {
		return ($includeAbstractTypes || !$eiType->isAbstract())
				&& ($allowedSubEiTypeIds === null || in_array($eiType->getId(), $allowedSubEiTypeIds));
	}
	
	/**
	 * @return EiuType[]
	 */
	function allSubTypes() {
		if ($this->allSubEiuTypes !== null) {
			return $this->allSubEiuTypes;
		}
		
		$this->allSubEiuTypes = [];
		foreach ($this->eiType->getAllSubEiTypes() as $eiType) {
			$this->allSubEiuTypes[] = new EiuType($eiType, $this->eiuAnalyst);
		}
		return $this->allSubEiuTypes;
	}
	
	/**
	 * @param object $object
	 * @return boolean
	 */
	function matches($object) {
		$eiType = EiuAnalyst::determineEiType($object, true);
		return $this->eiType->isA($eiType);
	}
	
	/**
	 * @return \n2n\persistence\orm\model\EntityModel
	 */
	function getEntityModel() {
		return $this->eiType->getEntityModel();
	}
	
	/**
	 * @return \ReflectionClass
	 */
	function getClass() {
		return $this->eiType->getEntityModel()->getClass();
	}
}
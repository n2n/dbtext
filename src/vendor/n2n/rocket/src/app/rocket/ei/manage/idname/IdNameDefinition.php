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
namespace rocket\ei\manage\idname;

use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\ei\manage\EiObject;
use n2n\l10n\Lstr;
use n2n\core\container\N2nContext;
use rocket\ei\manage\DefPropPath;
use rocket\ei\mask\EiMask;

class IdNameDefinition {
	private $identityStringPattern;
	private $eiMask;
	private $labelLstr;
	private $idNameProps = array();
	private $idNamePropForks = array();
	private $eiPropPaths = array();
	
	function __construct(EiMask $eiMask, Lstr $labelLstr) {
		$this->eiMask = $eiMask;
		$this->labelLstr = $labelLstr;
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @return \n2n\l10n\Lstr
	 */
	function getLabelLstr() {
		return $this->labelLstr;
	}
	
	/**
	 * @param string|null $identityStringPattern
	 */
	public function setIdentityStringPattern(?string $identityStringPattern) {
		$this->identityStringPattern = $identityStringPattern;
		$this->usedDefPropPaths = null;
	}
	
	/**
	 * @return string|null
	 */
	public function getIdentityStringPattern() {
		return $this->identityStringPattern;
	}
	
	/**
	 * @return IdNameProp[]
	 */
	public function getIdNameProps() {
		return $this->idNameProps;
	}
	
	/**
	 * @return IdNamePropFork[]
	 */
	public function getIdNamePropForks() {
		return $this->idNamePropForks;
	}
	
	function putIdNameProp(EiPropPath $eiPropPath, IdNameProp $idNameProp) {
		$this->idNameProps[(string) $eiPropPath] = $idNameProp;
		$this->usedDefPropPaths = null;
	}
	
	function putIdNamePropFork(EiPropPath $eiPropPath, IdNamePropFork $idNamePropFork) {
		$this->idNamePropForks[(string) $eiPropPath] = $idNamePropFork;
		$this->usedDefPropPaths = null;
	}
	
	
	private function createDefaultIdentityStringPattern() {
		
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	private function createDefaultIdentityString(EiObject $eiObject, N2nContext $n2nContext, N2nLocale $n2nLocale) {
		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
		$idPatternPart = null;
		$namePatternPart = null;
		
		foreach ($this->getAllIdNameProps() as $eiPropPathStr => $idNameProp) {
			if ($eiPropPathStr == $eiType->getEntityModel()->getIdDef()->getPropertyName()) {
				$idPatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
			} else {
				$namePatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
			}
			
			if ($namePatternPart !== null) break;
		}
		
		if ($idPatternPart === null) {
			$idPatternPart = $eiObject->getEiEntityObj()->hasId() 
					? $eiType->idToPid($eiObject->getEiEntityObj()->getId()) : 'new';
		}
		
		if ($namePatternPart === null) {
			$namePatternPart = $this->labelLstr->t($n2nLocale);
		}
		
		return $this->createIdentityStringFromPattern($namePatternPart . ' #' . $idPatternPart, 
				$n2nContext, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(EiObject $eiObject, N2nContext $n2nContext, N2nLocale $n2nLocale) {
		if ($this->identityStringPattern === null) {
			return $this->createDefaultIdentityString($eiObject, $n2nContext, $n2nLocale);
		}
		
		return $this->createIdentityStringFromPattern($this->identityStringPattern, $n2nContext, $eiObject, $n2nLocale);
	}
	
	/**
	 * @param string $identityStringPattern
	 * @param N2nContext $n2nContext
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityStringFromPattern(string $identityStringPattern, N2nContext $n2nContext, 
			EiObject $eiObject, N2nLocale $n2nLocale): string {
		$builder = new SummarizedStringBuilder($identityStringPattern, $n2nContext, $n2nLocale);
		$builder->replaceFields(array(), $this, $eiObject);
		return $builder->__toString();
	}
	
	private $usedDefPropPaths = null;
	
	public function getUsedDefPropPaths() {
		if ($this->usedDefPropPaths !== null) {
			return $this->usedDefPropPaths;
		}
		
		if ($this->identityStringPattern !== null) {
			return $this->usedDefPropPaths = SummarizedStringBuilder::detectUsedDefPropPaths($this->identityStringPattern, $this);
		}
			
		foreach ($this->getAllIdNameProps() as $eiPropPathStr => $idNameProp)  {
			$this->usedDefPropPaths = [$eiPropPathStr => DefPropPath::create($eiPropPathStr)];
			break;
		}
		
		$this->usedDefPropPaths = [];
	}
	
	/**
	 * @return \rocket\ei\manage\idname\IdNameProp[]
	 */
	public function getAllIdNameProps() {
		return $this->combineIdNameProps($this, array());
	}
	
	private function combineIdNameProps(IdNameDefinition $idNameDefinition, array $baseIds) {
		$idNameProps = array();
		
		foreach ($idNameDefinition->getIdNameProps() as $id => $idNameProp) {
			$ids = $baseIds;
			$ids[] = EiPropPath::create($id);
			$idNameProps[(string) new DefPropPath($ids)] = $idNameProp;
		}
		
		foreach ($idNameDefinition->getIdNamePropForks() as $id => $idNamePropFork) {
			$forkedIdNameDefinition = $idNamePropFork->getForkedIdNameDefinition();
			
			if ($forkedIdNameDefinition === null) continue;
			
			$ids = $baseIds;
			$ids[] = EiPropPath::create($id);
			$idNameProps = array_merge($idNameProps, 
					$this->combineIdNameProps($forkedIdNameDefinition, $ids));
		}
		
		return $idNameProps;
	}
	
	
}
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
namespace rocket\ei\component\command;

use rocket\ei\component\EiComponentCollection;
use rocket\ei\component\UnknownEiComponentException;
use n2n\util\type\ArgUtils;
use rocket\ei\mask\EiMask;
use rocket\ei\EiCommandPath;
use rocket\ei\util\Eiu;
use rocket\ei\manage\EiObject;
use rocket\si\control\SiNavPoint;

class EiCommandCollection extends EiComponentCollection {
	
	/**
	 * @param EiMask $eiMask
	 */
	public function __construct(EiMask $eiMask) {
		parent::__construct('EiCommand', EiCommand::class);
		$this->setEiMask($eiMask);
	}

	/**
	 * @param string $id
	 * @return EiCommand
	 */
	public function getByPath(EiCommandPath $eiCommandPath) {
		return $this->getElementByIdPath($eiCommandPath);
	}
	
	/**
	 * @param string $id
	 * @return EiCommand
	 */
	public function getById(string $id) {
		return $this->getElementByIdPath(new EiCommandPath($id));
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @param bool $prepend
	 * @return EiCommandWrapper
	 */
	public function add(EiCommand $eiCommand, string $id = null, bool $prepend = false) {
		$eiCommandPath = new EiCommandPath($this->makeId($id, $eiCommand));
		$eiCommandWrapper = new EiCommandWrapper($eiCommandPath, $eiCommand, $this);
		
		$this->addElement($eiCommandPath, $eiCommand);
		
		return $eiCommandWrapper;
	}
	
	/**
	 * @param IndependentEiCommand $independentEiCommand
	 * @param string $id
	 * @return \rocket\ei\component\command\EiCommandWrapper
	 */
	public function addIndependent(string $id, IndependentEiCommand $independentEiCommand) {
		$eiCommandWrapper = $this->add($independentEiCommand, $id);
		$this->addIndependentElement($eiCommandWrapper->getEiCommandPath(), $independentEiCommand);
		return $eiCommandWrapper;
	}
	
	/**
	 * @return boolean
	 */
	public function hasGenericOverview() {
		return null !== $this->determineGenericOverview(false);
	}
	
	/**
	 * @param bool $required
	 * @throws UnknownEiComponentException
	 * @return \rocket\ei\component\command\GenericResult|NULL
	 */
	public function determineGenericOverview(bool $required) {
		foreach ($this as $eiCommand) {
			if (!($eiCommand instanceof GenericOverviewEiCommand)) {
				continue;
			}
			
			$navPoint = $eiCommand->buildOverviewNavPoint(new Eiu($this->eiMask, $eiCommand));
			if ($navPoint == null) {
				continue;
			}
			
// 			ArgUtils::assertTrueReturn($navPoint->getUrl()->isRelative(), $eiCommand, 'buildOverviewNavPoint', 
// 					'Returned Url must be relative.');
			
			return new GenericResult($eiCommand, $navPoint);
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiMask . ' provides no compatible' 
				. GenericOverviewEiCommand::class . '.');
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function hasGenericDetail(EiObject $eiObject) {
		return null !== $this->determineGenericDetail($eiObject, false);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $required
	 * @return GenericResult
	 */
	public function determineGenericDetail(EiObject $eiObject, bool $required = false) {
		foreach ($this->eiMask->getEiCommandCollection() as $eiCommand) {
			if (!($eiCommand instanceof GenericDetailEiCommand)) {
				continue;
			}
			
			$navPoint = $eiCommand->buildDetailNavPoint(new Eiu($this->eiMask, $eiObject, $eiCommand));
			if ($navPoint == null) {
				continue;
			}
			
// 			ArgUtils::assertTrueReturn($navPoint->getUrl()->isRelative(), $eiCommand,
// 					'getDetailUrlExt', 'Returned Url must be relative.');
			
			return new GenericResult($eiCommand, $navPoint);
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiMask->getEiEngineModel() . ' provides no ' 
				. GenericDetailEiCommand::class . ' for ' . $eiObject);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function hasGenericEdit(EiObject $eiObject) {
		return null !== $this->determineGenericEdit($eiObject, false);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $required
	 * @return GenericResult
	 */
	public function determineGenericEdit(EiObject $eiObject, bool $required = false) {
		foreach ($this->eiMask->getEiCommandCollection() as $eiCommand) {
			if (!($eiCommand instanceof GenericEditEiCommand)) {
				continue;
			}
			
			$navPoint = $eiCommand->buildEditNavPoint(new Eiu($this->eiMask, $eiObject, $eiCommand));
			if ($navPoint == null) {
				continue;
			}
			
// 			ArgUtils::assertTrueReturn($urlExt->isRelative(), $eiCommand,
// 					'getEditUrlExt', 'Returned Url must be relative.');
			
			return new GenericResult($eiCommand, $navPoint);
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiMask->getEiEngineModel() . ' provides no '
				. GenericEditEiCommand::class . ' for ' . $eiObject);
	}
	
		/**
	 * @return boolean
	 */
	public function hasGenericAdd() {
		return null !== $this->determineGenericAdd(false);
	}
	
	/**
	 * @param bool $required
	 * @throws UnknownEiComponentException
	 * @return \rocket\ei\component\command\GenericResult|NULL
	 */
	public function determineGenericAdd(bool $required) {
		foreach ($this as $eiCommand) {
			if (!($eiCommand instanceof GenericAddEiCommand)) {
				continue;
			}
			
			$navPoint = $eiCommand->buildAddNavPoint(new Eiu($this->eiMask, $eiCommand));
			if ($navPoint == null) {
				continue;
			}
			
// 			ArgUtils::assertTrueReturn($navPoint->getUrl()->isRelative(), $eiCommand,
// 					'buildAddNavPoint', 'Returned Url must be relative.');
			
			return new GenericResult($eiCommand, $navPoint);
		}
		
		if (!$required) return null;
		
		throw new UnknownEiComponentException($this->eiMask . ' provides no compatible' 
				. GenericAddEiCommand::class . '.');
	}
}

class GenericResult {
	private $eiCommand;
	private $eiCommandPath;
	private $navPoint;
	
	function __construct(EiCommand $eiCommand, SiNavPoint $navPoint) {
		$this->eiCommand = $eiCommand;
		$this->eiCommandPath = EiCommandPath::from($eiCommand);
		$this->navPoint = $navPoint;
	}
	
	function getEiCommand() {
		return $this->eiCommand;	
	}
	
	/**
	 * @return \rocket\ei\EiCommandPath
	 */
	function getEiCommandPath() {
		return $this->eiCommandPath;
	}
	
	/**
	 * @return SiNavPoint
	 */
	function getNavPoint() {
		return $this->navPoint;
	}
	
}

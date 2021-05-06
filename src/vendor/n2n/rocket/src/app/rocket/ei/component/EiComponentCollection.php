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
namespace rocket\ei\component;

use n2n\util\ex\IllegalStateException;
use rocket\ei\IdPath;
use rocket\ei\mask\EiMask;
use n2n\util\type\TypeUtils;

abstract class EiComponentCollection implements \IteratorAggregate, \Countable {
	private $elementName;
	private $genericType;
	
	protected $eiMask;
	private $idPaths = array();
	private $elements = array();
	private $rootElements = array();
	private $forkedElements = array();
	private $independentElements = array();
	private $inheritedCollection;
	private $disabledInheritIds = array();
	
	public function __construct($elementName, $genericType) {
		$this->elementName = $elementName;
		$this->genericType = $genericType;
	}
	
	protected function setEiMask(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\mask\EiMask
	 */
	public function getEiMask() {
		if ($this->eiMask !== null) {
			return $this->eiMask;
		}
		
		throw new IllegalStateException('No EiMask assigend to EiComponentCollection: ' . $this->elementName);
	}
	
	/**
	 * @param EiComponentCollection $inheritedCollection
	 */
	public function setInheritedCollection(EiComponentCollection $inheritedCollection = null) {
		$this->ensureNoEiEngine();
		
		$this->inheritedCollection = $inheritedCollection;
	}
	
	/**
	 * @return EiComponentCollection
	 */
	public function getInheritedCollection() {
		return $this->inheritedCollection;
	}
	
	private function ensureNoEiEngine() {
		if (!$this->eiMask->hasEiEngine()) return;
		
		throw new IllegalStateException('Can not add EiComponent because EiEngine for EiMask ' . $this->eiMask 
				. ' is already initialized.');
	}
	
	/**
	 * @param string $id
	 * @param EiComponent $eiComponent
	 * @throws InvalidEiComponentConfigurationException
	 * @return string
	 */
	protected function makeId(?string $id, EiComponent $eiComponent) {
		if (0 == mb_strlen($id)) {
			$id = $this->createUniqueId($eiComponent->getIdBase() 
					?? TypeUtils::buildTypeAcronym(get_class($eiComponent)));
		} else if (IdPath::constainsSpecialIdChars($id)) {
			throw new InvalidEiComponentConfigurationException($this->elementName . ' contains invalid id: '
					. $id);
		}
		
		return $id;
	}
	
	/**
	 * @param string $idPath
	 * @param EiComponent $eiComponent
	 */
	protected function addElement(IdPath $idPath, EiComponent $element, bool $prepend = false) {
		$idPathStr = (string) $idPath;
		
		$this->idPaths[$idPathStr] = $idPath; 
		if (!$prepend) {
			$this->elements[$idPathStr] = $element;
		} else {
			$this->elements = array($idPathStr => $element) + $this->elements;
		}
		
		if (!$idPath->hasMultipleIds()) {
			$this->rootElements[$idPathStr] = $element;
		}
		
		$ids = $idPath->toArray();
		$lastId = array_pop($ids);
		$forkIdPathStr = IdPath::implodeIds($ids);
		
		if (!isset($this->forkedElements[$forkIdPathStr])) {
			$this->forkedElements[$forkIdPathStr] = array();
		}
		
		$this->forkedElements[$forkIdPathStr][$lastId] = $element;
		
		$this->triggerChanged();
	}
	
	protected function addIndependentElement(IdPath $idPath, EiComponent $independentElement) {
		$this->independentElements[(string) $idPath] = $independentElement;
	}
	
	/**
	 * @param string $id
	 * @return mixed
	 * @throws UnknownEiComponentException
	 */
	protected function getElementByIdPath(IdPath $idPath) {
		$idPathStr = (string) $idPath;
		
		if (isset($this->elements[$idPathStr])) {
			return $this->elements[$idPathStr];
		}
		
		if ($this->inheritedCollection !== null) {
			return $this->inheritedCollection->getElementByIdPath($idPath);
		}
		
		throw new UnknownEiComponentException('No ' . $this->elementName . ' with id \'' . $idPathStr
				. '\' found in ' . $this->eiMask . '.');
	}
	
	/**
	 * @param IdPath $forkIdPath
	 * @return array
	 */
	protected function getElementsByForkIdPath(IdPath $forkIdPath) {
		$elements = null;
		if ($forkIdPath->isEmpty()) {
			$elements = $this->rootElements;
		} else {
			$forkIdPathStr = (string) $forkIdPath;
			$elements = $this->forkedElements[$forkIdPathStr] ?? array();
		}
		
		if ($this->inheritedCollection !== null) {
			return array_merge($elements, $this->inheritedCollection->getElementsByForkIdPath($forkIdPath));
		}
		
		return $elements;
	}
	
	/**
	 * @param bool $checkInherited
	 * @return boolean
	 */
	public function isEmpty(bool $checkInherited = true): bool {
		if (!$checkInherited) {
			return empty($this->elements);
		}
		
		return empty($this->elements) && ($this->inheritedCollection === null 
				|| $this->inheritedCollection->isEmpty());
	}
	
	/**
	 * @param string $idBase
	 * @return string
	 */
	protected function createUniqueId(string $idBase) {
		$idBase = IdPath::stripSpecialIdChars($idBase);
		if (0 < mb_strlen($idBase) && !$this->containsId($idBase, true, true)) {
			return $idBase;			
		}
		
		for ($ext = 1; true; $ext++) {
			$id = $idBase . $ext;
			if (!$this->containsId($id, true, true)) {
				return $id;
			}
		}
	}
	
	/**
	 * @param string $id
	 * @return boolean
	 */
	public function containsId($id, $checkInherited = true): bool {
		if (isset($this->elements[$id])) return true;
		
		if ($this->inheritedCollection !== null && $checkInherited && 
				$this->inheritedCollection->containsId($id, true, false)) {
			return true;
		}
		
		return false;
	}
	
	/* (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() {
		return new \ArrayIterator($this->toArray());
	}

	/**
	 * @param string $independentOnly
	 * @return \rocket\ei\component\EiComponent[]
	 */
	public function toArray(bool $includeInherited = true): array {
		if ($this->inheritedCollection === null || !$includeInherited) return $this->elements;

		$superElements = $this->filterEnableds($this->inheritedCollection->toArray(true));

		return $superElements + $this->elements;
	}
	
	/**
	 * @todo remove probably
	 * @param array $elements
	 */
	private function filterEnableds(array $elements) {
		return $elements;
	}
	
	function count() {
		$num = count($this->elements);
		if ($this->inheritedCollection !== null) {
			$num += $this->inheritedCollection->count();
		}
		return $num;
	}
	
	/**
	 * @var EiComponentCollectionListener[]
	 */
	private $listeners = [];
	
	function registerListener(EiComponentCollectionListener $listener) {
		$this->listeners[spl_object_hash($listener)] = $listener;
	}
	
	function unregisterListener(EiComponentCollectionListener $listener) {
		unset($this->listeners[spl_object_hash($listener)]);
	}
	
	private function triggerChanged() {
		foreach ($this->listeners as $listener) {
			$listener->eiComponenetCollectionChanged($this);
		}
	}
	
}

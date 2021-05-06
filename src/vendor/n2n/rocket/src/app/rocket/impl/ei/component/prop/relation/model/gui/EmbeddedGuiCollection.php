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
namespace rocket\impl\ei\component\prop\relation\model\gui;

use rocket\ei\util\gui\EiuEntryGui;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\EiPropPath;
use rocket\ei\util\frame\EiuFrame;
use rocket\si\content\impl\relation\SiEmbeddedEntry;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\type\CastUtils;
use rocket\si\input\SiEntryInput;
use rocket\ei\util\spec\EiuType;

class EmbeddedGuiCollection {
	/**
	 * @param EiuFrame
	 */
	private $eiuFrame;
	/**
	 * @var bool
	 */
	private $readOnly;
	/**
	 * @var bool
	 */
	private $summaryRequired;
	/**
	 * @var int
	 */
	private $min;
	/** 
	 * @var EiuEntryGui[]
	 */
	private $eiuEntryGuis = [];
	/**
	 * 
	 * @var EiuType[]
	 */
	private $allowedEiuTypes = [];

	/**
	 * @param bool $readOnly
	 * @param bool $summaryRequired
	 * @param int $min
	 * @param EiuFrame $eiuFrame
	 * @param array $allowedEiuTypes
	 */
	function __construct(bool $readOnly, bool $summaryRequired, int $min, ?EiuFrame $eiuFrame, ?array $allowedEiuTypes) {
		$this->readOnly = $readOnly;
		$this->summaryRequired = $summaryRequired;
		$this->min = $min;
		$this->eiuFrame = $eiuFrame;
		$this->allowedEiuTypes = $allowedEiuTypes;
	
	}

	/**
	 * 
	 */
	function clear() {
		$this->eiuEntryGuis = [];
	}

	/**
	 * @param EiuEntry $eiuEntry
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	function add(EiuEntry $eiuEntry) {
		return $this->eiuEntryGuis[] = $eiuEntry->newGui(true, $this->readOnly)->entryGui();
	}
	
	function fillUp() {
		$num = $this->min - count($this->eiuEntryGuis);
		
		if ($num <= 0) {
			return;
		}
		
		IllegalStateException::assertTrue($this->eiuFrame !== null);
		$eiuGuiModel = $this->eiuFrame->contextEngine()->newForgeMultiGuiModel(true, $this->readOnly, $this->allowedEiuTypes);
		for ($i = 0; $i < $num; $i++) {
			$this->eiuEntryGuis[] = $eiuGuiModel->newEntryGui();
		}
	}
	
	function addNew() {
		IllegalStateException::assertTrue($this->eiuFrame !== null);
		return $this->eiuEntryGuis[] = $this->eiuFrame->newForgeMultiEntryGui(true, $this->readOnly);
	}
	
	function sort(EiPropPath $orderEiPropPath) {
		uasort($this->eiuEntryGuis, function($a, $b) use ($orderEiPropPath) {
			$aValue = $a->entry()->getScalarValue($orderEiPropPath);
			$bValue = $b->entry()->getScalarValue($orderEiPropPath);
			
			if ($aValue == $bValue) {
				return 0;
			}
			
			return ($aValue < $bValue) ? -1 : 1;
		});
	}
	
	/**
	 * @return int
	 */
	function count() {
		return count($this->eiuEntryGuis);
	}
	
// 	/**
// 	 * @return NULL|string[]
// 	 */      
// 	function buildAllowedSiTypeIds() {
// 		if ($this->allowedEiuMasks === null) {
// 			return null;
// 		}
		
// 		$allowedSiTypeIds = [];
// 		foreach ($this->allowedEiuMasks as $eiuMask) {
// 			$allowedSiTypeIds[] = $eiuMask->type()->getSiTypeId();
// 		}
// 		return $allowedSiTypeIds;
// 	}
	
	function createSiEmbeddedEntries() {
		return array_values(array_map(
				function ($eiuEntryGui) { return $this->createSiEmbeddeEntry($eiuEntryGui); },
				$this->eiuEntryGuis));
	}
	
	/**
	 * @param EiuEntryGui $eiuEntryGui
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
	 */
	private function createSiEmbeddeEntry($eiuEntryGui) {
		return new SiEmbeddedEntry(
				$eiuEntryGui->gui()->createBulkyEntrySiGui(false, false),
				($this->summaryRequired ?
						$eiuEntryGui->gui()->copy(false, true)->createCompactEntrySiGui(false):
						null));
	}
	
	/**
	 * @param string $id
	 * @return \rocket\ei\util\gui\EiuEntryGui|NULL
	 */
	function find(string $id) {
		foreach ($this->eiuEntryGuis as $eiuEntryGui) {
			if ($eiuEntryGui->entry()->hasId() && $id == $eiuEntryGui->entry()->getPid()) {
				return $eiuEntryGui;
			}
		}
		
		return null;
	}
	
	/**
	 * @param SiEntryInput $siEntryInputs
	 */
	function handleSiEntryInputs(array $siEntryInputs) {
		$newEiuEntryGuis = [];
		foreach ($siEntryInputs as $siEntryInput) {
			CastUtils::assertTrue($siEntryInput instanceof SiEntryInput);
			
			$eiuEntryGui = null;
			$id = $siEntryInput->getIdentifier()->getId();
			
			if ($id !== null && null !== ($eiuEntryGui = $this->find($id))) {
				$eiuEntryGui->handleSiEntryInput($siEntryInput);
				$newEiuEntryGuis[] = $eiuEntryGui;
				continue;
			}
			
			$newEiuEntryGuis[] = $this->addNew()->handleSiEntryInput($siEntryInput);
		}
		
		$this->eiuEntryGuis = $newEiuEntryGuis;
	}
	
	/**
	 * @param EiPropPath $orderEiPropPath
	 * @return EiEntry[]
	 */
	function save(?EiPropPath $orderEiPropPath) {
		$values = [];
		$i = 0;
		foreach ($this->eiuEntryGuis as $eiuEntryGui) {
			if (!$eiuEntryGui->isTypeSelected()) {
				continue;
			}
			
			$eiuEntryGui->save();
			$values[] = $eiuEntry = $eiuEntryGui->entry();
			
			if (null === $orderEiPropPath) {
				continue;
			}
			
			$i += 10;
			$eiuEntry->setScalarValue($orderEiPropPath, $i);
		}
		return $values;
	}
}
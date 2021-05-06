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

use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\util\Eiu;
use rocket\ei\util\frame\EiuFrame;
use rocket\si\content\SiField;
use rocket\si\content\impl\relation\EmbeddedEntriesInSiField;
use rocket\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;
use rocket\si\input\SiEntryInput;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\content\impl\relation\EmbeddedEntryInputHandler;
use rocket\ei\manage\gui\GuiFieldMap;
use n2n\util\ex\IllegalStateException;
use rocket\si\content\impl\relation\SiEmbeddedEntry;

class EmbeddedToManyGuiField implements GuiField, EmbeddedEntryInputHandler {
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var EiuFrame
	 */
	private $targetEiuFrame;
	/**
	 * @var EmbeddedEntriesInSiField
	 */
	private $siField;
	/**
	 * @var EmbeddedGuiCollection
	 */
	private $embeddedGuiCollection;
	/**
	 * @var bool
	 */
	private $readOnly;
	
	function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationModel $relationModel, bool $readOnly) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->relationModel = $relationModel;
		$this->embeddedGuiCollection = new EmbeddedGuiCollection($readOnly, $relationModel->isReduced(), 
				$relationModel->getMin(), $targetEiuFrame, null);
		$this->readOnly = $readOnly;
		
		if ($readOnly) {
			$this->siField = SiFields::embeddedEntriesOut($this->targetEiuFrame->createSiFrame(), $this->readValues())
					->setReduced($this->relationModel->isReduced());
			return;
		}
		
		$this->siField = SiFields::embeddedEntriesIn($this->targetEiuFrame->createSiFrame(),
						$this, $this->readValues(), (int) $relationModel->getMin(), $relationModel->getMax())
				->setReduced($this->relationModel->isReduced())
				->setNonNewRemovable($this->relationModel->isRemovable())
				->setSortable(($relationModel->getMax() === null || $relationModel->getMax() > 1) 
						&& $relationModel->getTargetOrderEiPropPath() !== null)
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
				
	}
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry[]
	 */
	private function readValues() {
		$this->embeddedGuiCollection->clear();
		
		foreach ($this->eiu->field()->getValue() as $eiuEntry) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$this->embeddedGuiCollection->add($eiuEntry);
		}
	
		if (null !== ($targetOrderEiPropPath = $this->relationModel->getTargetOrderEiPropPath())) {
			$this->embeddedGuiCollection->sort($targetOrderEiPropPath);
		}
		
		if (!$this->readOnly) {
			$this->embeddedGuiCollection->fillUp();
		}
		
		return $this->embeddedGuiCollection->createSiEmbeddedEntries(); 
	}
	
	
	
// 	/**
// 	 * @param EiuEntryGuiTypeDef $eiuEntryGuiMulti
// 	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
// 	 */
// 	private function createSiEmbeddeEntryFromMulti($eiuEntryGuiMulti) {
// 		return new SiEmbeddedEntry(
// 				$eiuEntryGuiMulti->createBulkyEntrySiGui(false, false),
// 				($this->relationModel->isReduced() ?
// 						$eiuEntryGuiMulti->entry()->newGui(false, false)->createCompactEntrySiGui(false, false):
// 						null));
// 	}
	
	
	
	/**
	 * @param SiEntryInput $siEntryInputs
	 * @throws CorruptedSiInputDataException
	 * @return SiEmbeddedEntry[]
	 */
	function handleInput(array $siEntryInputs): array {
		IllegalStateException::assertTrue(!$this->readOnly);
		
		$this->embeddedGuiCollection->handleSiEntryInputs($siEntryInputs);
		$this->embeddedGuiCollection->fillUp();
		
		$targetOrderEiPropPath = $this->relationModel->getTargetOrderEiPropPath();
		$targetEiuEntries = $this->embeddedGuiCollection->save($targetOrderEiPropPath);
		$this->eiu->field()->setValue($targetEiuEntries);
		
		return $this->embeddedGuiCollection->createSiEmbeddedEntries();
	}
	
	function save() {
// 		IllegalStateException::assertTrue(!$this->readOnly);
		
// 		$targetOrderEiPropPath = $this->relationModel->getTargetOrderEiPropPath();
		
// 		$eiuEntries = $this->embeddedGuiCollection->save($targetOrderEiPropPath);
		
// 		$this->eiu->field()->setValue($eiuEntries);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}
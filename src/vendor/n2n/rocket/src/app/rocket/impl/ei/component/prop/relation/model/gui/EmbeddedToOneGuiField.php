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
use rocket\si\input\SiEntryInput;
use rocket\si\input\CorruptedSiInputDataException;
use rocket\si\content\impl\relation\EmbeddedEntryInputHandler;
use rocket\ei\manage\gui\GuiFieldMap;
use n2n\util\ex\IllegalStateException;
use n2n\util\col\ArrayUtils;

class EmbeddedToOneGuiField implements GuiField, EmbeddedEntryInputHandler {
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
	private $emebeddedGuiCollection;
	/**
	 * @var bool
	 */
	private $readOnly;
	
	function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationModel $relationModel, bool $readOnly) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->relationModel = $relationModel;
		$this->emebeddedGuiCollection = new EmbeddedGuiCollection($readOnly, $relationModel->isReduced(), 
				$this->relationModel->getMin(), $targetEiuFrame, null);
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
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
	}
	
	/**
	 * @return \rocket\si\content\impl\relation\SiEmbeddedEntry
	 */
	private function readValues() {
		$this->emebeddedGuiCollection->clear();
		
		if (null !== ($eiuEntry = $this->eiu->field()->getValue())) {
			$this->emebeddedGuiCollection->add($eiuEntry);
		}
		
		if (!$this->readOnly) {
			$this->emebeddedGuiCollection->fillUp();
		}
		
		return $this->emebeddedGuiCollection->createSiEmbeddedEntries();
	}
	
	/**
	 * @param SiEntryInput[] $siEntryInputs
	 * @throws CorruptedSiInputDataException
	 */
	function handleInput(array $siEntryInputs): array {
		IllegalStateException::assertTrue(!$this->readOnly);
		
		if (count($siEntryInputs) > 1) {
			throw new CorruptedSiInputDataException('Too many SiEntryInputs for EmbeddedToOneGuiField.');
		}
		
		$this->emebeddedGuiCollection->handleSiEntryInputs($siEntryInputs);
		$this->emebeddedGuiCollection->fillUp();
		return $this->emebeddedGuiCollection->createSiEmbeddedEntries();
	}
	
	function save() {
		IllegalStateException::assertTrue(!$this->readOnly);
		
		$value = ArrayUtils::first($this->emebeddedGuiCollection->save($this->relationModel->getTargetOrderEiPropPath()));
		
		$this->eiu->field()->setValue($value);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}
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

use rocket\ei\manage\gui\GuiFieldMap;
use rocket\ei\manage\gui\field\GuiField;
use rocket\si\content\SiField;
use rocket\ei\util\Eiu;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;
use rocket\si\content\impl\relation\QualifierSelectInSiField;

class ToOneGuiField implements GuiField {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var Eiu
	 */
	private $targetEiu;
	/**
	 * @var QualifierSelectInSiField
	 */
	private $siField;
	
	function __construct(Eiu $eiu, RelationModel $relationModel) {
		$this->eiu = $eiu;
		
		$this->targetEiu = $eiu->frame()->forkSelect($eiu->prop()->getPath(), $eiu->entry());
		$this->targetEiu->frame()->exec($relationModel->getTargetReadEiCommandPath());
		
		$values = [];
		if (null !== ($eiuEntry = $eiu->field()->getValue())) {
			CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
			$values[] = $eiuEntry->createSiEntryQualifier();
		}
		
		$this->siField = SiFields::qualifierSelectIn($this->targetEiu->frame()->createSiFrame(),
						$values, ($relationModel->isMandatory() ? 1 : 0), 1, 
						$this->readPickableQualifiers($relationModel->getMaxPicksNum()))
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
	}
	
	private function readPickableQualifiers(int $maxNum) {
		if ($maxNum <= 0) {
			return null;
		}
		
		$num = $this->targetEiu->frame()->count();
		if ($num > $maxNum) {
			return null;
		}
		
		$siEntryQualifiers = [];
		foreach ($this->targetEiu->frame()->lookupObjects() as $eiuObject) {
			$siEntryQualifiers[] = $eiuObject->createSiEntryQualifier();
		}
		return $siEntryQualifiers;
	}
	
	function save() {
		$siQualifiers = $this->siField->getValues();
		
		if (empty($siQualifiers)) {
			$this->eiu->field()->setValue(null);
			return;
		}
		
		$id = $this->targetEiu->frame()->siQualifierToId(current($siQualifiers));
		$value = $this->targetEiu->frame()->lookupEntry($id);
		$this->eiu->field()->setValue($value);
	}

	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
	
}

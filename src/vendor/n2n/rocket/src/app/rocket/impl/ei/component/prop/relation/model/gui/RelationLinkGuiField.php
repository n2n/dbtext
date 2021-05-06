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

use rocket\ei\util\Eiu;
use rocket\si\content\SiField;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ei\manage\gui\GuiFieldMap;
use rocket\ei\manage\gui\field\GuiField;
use n2n\util\ex\UnsupportedOperationException;
use rocket\si\content\impl\SiFields;
use n2n\util\type\CastUtils;
use rocket\ei\util\entry\EiuEntry;

class RelationLinkGuiField implements GuiField {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	/**
	 * @var SiField
	 */
	private $siField;
	
	function __construct(Eiu $eiu, RelationModel $relationModel) {
		$this->eiu = $eiu;
		$this->relationModel = $relationModel;
		
		if ($relationModel->isTargetMany()) {
			$this->siField = $this->createToManySiField();
		} else {
			$this->siField = $this->createToOneSiField();
		}
	}
	
	private function createToManySiField() {
		$targetEiuFrame = $this->eiu->frame()->forkDiscover($this->eiu->prop(), $this->eiu->entry())->frame();
		$targetEiuFrame->exec($this->relationModel->getTargetReadEiCommandPath());
		
		$num = $targetEiuFrame->count();
		$label = null;
		if ($num == 1) {
			$label = $num . ' ' . $targetEiuFrame->engine()->mask()->getLabel();
		} else {
			$label = $num . ' ' . $targetEiuFrame->engine()->mask()->getPluralLabel();
		}
		
		if (null !== ($overviewNavPoint = $targetEiuFrame->getOverviewNavPoint(false))) {
			return SiFields::linkOut($overviewNavPoint, $label, false);
		}
		
		return SiFields::stringOut($label);
	}
	
	private function createToOneSiField() {
		$value = $this->eiu->field()->getValue();
		if ($value === null) {
			return SiFields::stringOut(null);
		}
		
		CastUtils::assertTrue($value instanceof EiuEntry);
		$label = $value->createIdentityString();
		
		$targetEiuFrame = $this->eiu->frame()->forkDiscover($this->eiu->prop(), $this->eiu->entry())->frame();
		$targetEiuFrame->exec($this->relationModel->getTargetReadEiCommandPath());
		
		if (null !== ($detailNavPoint = $targetEiuFrame->getDetailNavPoint($value, false))) {
			return SiFields::linkOut($detailNavPoint, $label);
		}
		
		return SiFields::stringOut($label);
	}
	
	function getSiField(): SiField {
		return $this->siField;
	}
	
	function getContextSiFields(): array {
		return [];
	}
	
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
	
	function save() {
		throw new UnsupportedOperationException();
	}


}
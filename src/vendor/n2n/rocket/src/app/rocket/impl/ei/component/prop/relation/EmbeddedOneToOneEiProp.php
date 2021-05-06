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
namespace rocket\impl\ei\component\prop\relation;

use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use n2n\persistence\orm\property\EntityProperty;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\ei\manage\gui\ViewMode;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\si\meta\SiStructureType;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use rocket\ei\component\prop\FieldEiProp;
use rocket\impl\ei\component\prop\relation\model\ToOneEiField;
use rocket\ei\util\Eiu;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\model\gui\EmbeddedToOneGuiField;
use n2n\util\type\CastUtils;
use rocket\si\content\impl\meta\SiCrumb;
use rocket\si\content\impl\SiFields;
use rocket\ei\util\entry\EiuEntry;

class EmbeddedOneToOneEiProp extends RelationEiPropAdapter implements FieldEiProp {
	
	/**
	 * 
	 */
	public function __construct() {
		parent::__construct();
		
		$this->setup(
				(new DisplayConfig(ViewMode::all()))->setSiStructureType(SiStructureType::SIMPLE_GROUP)
						->setDefaultDisplayedViewModes(ViewMode::bulky()),
				new RelationModel($this, false, false, RelationModel::MODE_EMBEDDED, new EditConfig()));
	}

	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);
	
		parent::setEntityProperty($entityProperty);
	}
	
	function buildEiField(Eiu $eiu): ?EiField {
		$targetEiuFrame = $eiu->frame()->forkSelect($this, $eiu->object())
				->frame()->exec($this->getRelationModel()->getTargetReadEiCommandPath());
		
		$field = new ToOneEiField($eiu, $targetEiuFrame, $this, $this->getRelationModel());
		$field->setMandatory($this->getEditConfig()->isMandatory());
		return $field;
	}
	
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
// 		if ($readOnly || $this->getEditConfig()->isReadOnly()) {
// 			return new RelationLinkGuiField($eiu, $this->getRelationModel());
// 		}
		
// 		$targetEiuFrame = $eiu->frame()->forkDiscover($this, $eiu->object())->frame()
// 				->exec($this->getRelationModel()->getTargetEditEiCommandPath());
		
		$readOnly = $readOnly || $this->getEditConfig()->isReadOnly();
		
		if ($readOnly && $eiu->gui()->isCompact()) {
			return $this->createCompactGuiField($eiu);
		}
		
		$targetEiuFrame = null;
		if ($readOnly){
			$targetEiuFrame = $eiu->frame()->forkDiscover($this, $eiu->object())->frame()
					->exec($this->getRelationModel()->getTargetReadEiCommandPath());
		} else {
			$targetEiuFrame = $eiu->frame()->forkDiscover($this, $eiu->object())->frame()
					->exec($this->getRelationModel()->getTargetReadEiCommandPath());
		}

		return new EmbeddedToOneGuiField($eiu, $targetEiuFrame, $this->getRelationModel(), $readOnly);
	}
	
	
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\si\content\SiField
	 */
	private function createCompactGuiField(Eiu $eiu) {
		$eiuEntry = $eiu->field()->getValue();
		
		if ($eiuEntry === null) {
			return $eiu->factory()->newGuiField(SiFields::crumbOut(SiCrumb::createLabel('0')
					->setSeverity(SiCrumb::SEVERITY_UNIMPORTANT)))->toGuiField();
		}
		
		CastUtils::assertTrue($eiuEntry instanceof EiuEntry);
		
		return $eiu->factory()->newGuiField(SiFields::crumbOut(
				SiCrumb::createIcon($eiuEntry->mask()->getIconType())->setSeverity(SiCrumb::SEVERITY_IMPORTANT),
				SiCrumb::createLabel($eiuEntry->object()->createIdentityString())))->toGuiField();
	}
}
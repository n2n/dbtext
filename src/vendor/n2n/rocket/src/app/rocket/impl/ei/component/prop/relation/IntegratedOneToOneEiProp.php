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

use rocket\ei\manage\gui\GuiPropFork;
use rocket\ei\manage\gui\field\GuiFieldFork;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\gui\EiEntryGuiAssembler;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\relation\model\ToOneEiField;
use rocket\ei\manage\LiveEiObject;
use n2n\util\type\CastUtils;
use n2n\impl\web\dispatch\mag\model\ObjectMagAdapter;
use n2n\web\dispatch\Dispatchable;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\Raw;
use n2n\web\ui\UiComponent;
use n2n\persistence\orm\property\EntityProperty;
use n2n\util\type\ArgUtils;
use n2n\impl\persistence\orm\property\ToOneEntityProperty;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\ei\util\Eiu;
use n2n\web\dispatch\mag\UiOutfitter;
use rocket\ei\manage\gui\GuiProp;
use n2n\web\dispatch\mag\Mag;
use rocket\ei\manage\gui\field\GuiFieldForkEditable;
use rocket\ei\util\gui\EiuEntryGuiAssembler;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\gui\EiFieldAbstraction;
use rocket\impl\ei\component\prop\adapter\entry\EiFieldWrapperCollection;
use rocket\ei\manage\gui\field\GuiField;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\ei\manage\EiObject;

class IntegratedOneToOneEiProp extends RelationEiPropAdapter /*implements GuiPropFork*/ {
	
	public function __construct() {
		parent::__construct();
		
		$this->setup(null, new RelationModel($this, false, false, RelationModel::MODE_INTEGRATED, null));
	}
	
	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ToOneEntityProperty
				&& $entityProperty->getType() === RelationEntityProperty::TYPE_ONE_TO_ONE);
	
		parent::setEntityProperty($entityProperty);
	}
	
	private $forkedGuiDefinition;
	
	public function buildGuiPropFork(Eiu $eiu): ?GuiPropFork {
		$this->forkedGuiDefinition = $eiu->context()->engine($this->eiPropRelation->getTargetEiMask())
				->getGuiDefinition();
		
		return $this;
	}
	
	public function getForkedGuiDefinition(): GuiDefinition {
		return $this->forkedGuiDefinition;
	}
	
	public function buildEiField(Eiu $eiu): ?EiField {
		$readOnly = $this->eiPropRelation->isReadOnly($eiu->entry()->getEiEntry(), $eiu->frame()->getEiFrame());
		
		return new ToOneEiField($eiu, $this, $this, ($readOnly ? null : $this));
	}
	
	/**
	 * @param Eiu $eiu
	 * @return GuiFieldFork
	 */
	public function createGuiFieldFork(Eiu $eiu): GuiFieldFork {
		$eiFrame = $eiu->frame()->getEiFrame();
		$eiEntry = $eiu->entry()->getEiEntry();
		
		$targetEiFrame = null;
		if ($eiu->entryGui()->isReadOnly()) {
			$targetEiFrame = $this->eiPropRelation->createTargetReadPseudoEiFrame($eiFrame, $eiEntry);
		} else {
			$targetEiFrame = $this->eiPropRelation->createTargetEditPseudoEiFrame($eiFrame, $eiEntry);
		}
		
		$targetEiuFrame = (new Eiu($targetEiFrame))->frame();
		
		$eiuField = $eiu->field();
		$targetRelationEntry = $eiuField->getValue();
		CastUtils::assertTrue($targetRelationEntry instanceof RelationEntry || $targetRelationEntry === null);
		
		if ($targetRelationEntry === null) {
			$targetRelationEntry = RelationEntry::fromM($targetEiuFrame->newEntry()->getEiEntry());
		} else if (!$targetRelationEntry->hasEiEntry()) {
			$targetRelationEntry = RelationEntry::fromM(
					$targetEiuFrame->entry($targetRelationEntry->getEiObject())->getEiEntry());
		}
				
		$targetEiuEntryGuiAssembler = $targetEiuFrame->entry($targetRelationEntry->getEiEntry())
				->newEntryGuiAssembler($eiu->guiFrame()->getViewMode());
				
		return new OneToOneGuiFieldFork($eiuField->getEiField(), $targetRelationEntry, $targetEiuEntryGuiAssembler);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiPropFork::determineForkedEiObject()
	 */
	public function determineForkedEiObject(Eiu $eiu): ?EiObject {
		$targetObject = $eiu->object()->readNativValue($this);
		if ($targetObject === null) {
			return null;
		}
		return LiveEiObject::create($this->eiPropRelation->getTargetEiType(), $targetObject);
	}
	
	public function determineEiFieldAbstraction(Eiu $eiu, DefPropPath $defPropPath): EiFieldAbstraction {
		$eiEntry = $eiu->entry()->getEiEntry();
		
		$targetRelationEntry = $eiEntry->getValue(EiPropPath::from($this->eiPropRelation->getRelationEiProp()));
		if ($targetRelationEntry === null || !$targetRelationEntry->hasEiEntry()) {
			return new EiFieldWrapperCollection([]);
		}
	
		return $this->getForkedGuiDefinition()->determineEiFieldAbstraction($eiu->getN2nContext(), 
				$targetRelationEntry->getEiEntry(), $defPropPath);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\GuiEiProp::getGuiProp()
	 */
	public function buildGuiProp(Eiu $eiu): ?GuiProp {
		return null;	
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\DraftableEiProp::getDraftProperty()
	 */
	public function getDraftProperty() {
		throw new NotYetImplementedException();
	}

}

class OneToOneGuiFieldFork implements GuiFieldFork {
	private $toOneEiField;
	private $targetRelationEntry;
	private $targetEiuEntryGuiAssembler;
	
	public function __construct(ToOneEiField $toOneEiField, RelationEntry $targetRelationEntry, 
			EiuEntryGuiAssembler $targetEiuEntryGuiAssembler) {
		$this->toOneEiField = $toOneEiField;
		$this->targetRelationEntry = $targetRelationEntry;
		$this->targetEiuEntryGuiAssembler = $targetEiuEntryGuiAssembler;
	}
	
	public function assembleGuiField(DefPropPath $defPropPath): GuiField {
		return $this->targetEiuEntryGuiAssembler->assembleGuiField($defPropPath);
	}
	
	public function isReadOnly(): bool {
		return null === $this->targetEiuEntryGuiAssembler->getEiuEntryGui()->getDispatchable();
	}
	
	public function getEditable(): ?GuiFieldForkEditable {
		if ($this->isReadOnly()) return null;
		
		return new OneToOneGuiFieldForkEditable($this->toOneEiField, $this->targetEiuEntryGuiAssembler,
				$this->targetRelationEntry);
	}
}

class OneToOneGuiFieldForkEditable implements GuiFieldForkEditable {
	private $toOneEiField;
	private $targetEiuEntryGuiAssembler;
	private $targetRelationEntry;
	
	/**
	 * @param ToOneEiField $toOneEiField
	 * @param EiEntryGuiAssembler $targetEiEntryGuiAssembler
	 */
	public function __construct(ToOneEiField $toOneEiField, EiuEntryGuiAssembler $targetEiuEntryGuiAssembler,
			RelationEntry $targetRelationEntry) {
		$this->toOneEiField = $toOneEiField;
		$this->targetEiuEntryGuiAssembler = $targetEiuEntryGuiAssembler;
		$this->targetRelationEntry = $targetRelationEntry;
	}
	
	public function isForkMandatory(): bool {
		return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\field\GuiFieldForkEditable::getForkMag()
	 */
	public function getForkMag(): Mag {
		$dispatchable = $this->targetEiuEntryGuiAssembler->getEiuEntryGui()->getDispatchable();
		
		if ($dispatchable !== null) {
			return new OneToOneForkMag($dispatchable);
		}
		
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\field\GuiFieldForkEditable::getAdditionalForkMagPropertyPaths()
	 */
	public function getInheritForkMagAssemblies(): array {
		return $this->targetEiuEntryGuiAssembler->getEiuEntryGui()->getAllForkMagAssemblies();
	}
	
	/**
	 * 
	 */
	public function save() {
// 		$this->targetEiEntryGuiAssembler->save();
		$this->toOneEiField->setValue($this->targetRelationEntry);
	}
}

class OneToOneForkMag extends ObjectMagAdapter {
	private $dispatchable;

	/**
	 * @param Dispatchable $dispatchable
	 */
	public function __construct(Dispatchable $dispatchable) {
		parent::__construct('', $dispatchable);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createUiField()
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
		return new Raw();
	}
}

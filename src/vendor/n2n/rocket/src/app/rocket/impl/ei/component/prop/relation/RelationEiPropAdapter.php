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

use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\prop\ForkEiProp;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\manage\frame\EiForkLink;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\PropertyEiPropAdapter;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\relation\conf\RelationConfig;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\relation\model\Relation;
use rocket\ei\manage\gui\GuiFieldAssembler;
use rocket\ei\manage\idname\IdNameProp;
use rocket\ei\component\prop\IdNameEiProp;

abstract class RelationEiPropAdapter extends PropertyEiPropAdapter implements RelationEiProp, GuiEiProp, GuiFieldAssembler, ForkEiProp, IdNameEiProp {
			
	/**
	 * @var DisplayConfig
	 */
	private $displayConfig;
	
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	
	/**
	 * @var Relation
	 */
	private $relation;
			
		
	function isPrivileged(): bool {
		return true;
	}
	
	/**
	 * @param RelationModel $relationModel
	 */
	protected function setup(?DisplayConfig $displayConfig, RelationModel $relationModel) {
		$this->displayConfig = $displayConfig;
		$this->relationModel = $relationModel;
	}
	
	protected function prepare() {
		$relationModel = $this->getRelationModel();
		$configurator = $this->getConfigurator();
		
		if (null !== $this->displayConfig) {
			$configurator->addAdaption($this->displayConfig);
		}
		
		if (null !== ($editConfig = $relationModel->getEditConfig())) {
			$configurator->addAdaption($editConfig);
		}
		
		$configurator->addAdaption(new RelationConfig($relationModel));
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig|NULL
	 */
	protected function getEditConfig() {
		return $this->getRelationModel()->getEditConfig();
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\relation\conf\RelationModel
	 */
	protected function getRelationModel() {
		IllegalStateException::assertTrue($this->relationModel !== null, get_class($this));
		return $this->relationModel;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\relation\model\Relation
	 */
	protected function getRelation() {
		if ($this->relation !== null) {
			return $this->relation;
		}
		
// 		IllegalStateException::assertTrue($this->displayConfig !== null && $this->editConfig !== null);
		return $this->relation = new Relation($this, $this->getRelationModel()); 
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\relation\RelationEiProp::getRelationEntityProperty()
	 */
	function getRelationEntityProperty(): RelationEntityProperty {
		return $this->requireEntityProperty();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\GuiEiProp::buildGuiProp()
	 */
	function buildGuiProp(Eiu $eiu): ?GuiProp {
		return $eiu->factory()->newGuiProp(function (Eiu $eiu) {
			return $this->displayConfig->buildGuiPropSetup($eiu, $this);
		})->toGuiProp();
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		return null;
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		if ($this->getRelationModel()->isTargetMany()) {
			return null;
		}
		
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			$targetEntityObj = $eiu->object()->readNativValue($eiu->prop()->getEiProp());
			
			if ($targetEntityObj === null) {
				return null;
			}
			
			$targetEiuEngine = $this->getRelationModel()->getTargetEiuEngine();
			return $targetEiuEngine->createIdentityString($targetEntityObj);
		})->toIdNameProp();
	}
	
	function createForkedEiFrame(Eiu $eiu, EiForkLink $eiForkLink): EiFrame {
		return $this->getRelation()->createForkEiFrame($eiu, $eiForkLink);
	}
}

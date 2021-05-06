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
namespace rocket\impl\ei\component\prop\relation\model\relation;

use rocket\ei\manage\EiObject;
use rocket\ei\manage\frame\EiFrame;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use rocket\impl\ei\component\prop\relation\conf\RelationConfig;
use rocket\ei\util\Eiu;
use n2n\util\type\TypeUtils;

class EmbeddedEiPropRelation extends EiPropRelation {
	private $embeddedPseudoCommand;
	private $embeddedEditPseudoCommand;
	private $orphansAllowed = false;
	
	public function getOrphansAllowed() {
		return $this->orphansAllowed;
	}
	
	public function setOrphansAllowed(bool $orphansAllowed) {
		$this->orphansAllowed = $orphansAllowed;
	}

	public function init(Eiu $eiu, EiType $targetEiType, EiMask $targetEiMask, array $targetEiTypeExtensions) {
		parent::init($eiu, $targetEiType, $targetEiMask, $targetEiTypeExtensions);

		if (!$this->isPersistCascaded()) {
			$entityProperty = $this->getRelationEiProp()->getEntityProperty();
			throw new InvalidEiComponentConfigurationException(
					'EiProp requires an EntityProperty which cascades persist: ' 
							. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(),
									$entityProperty->getName()));
		}
		
		if ($this->isDraftable() && !$this->isJoinTableRelation($this)) {
			throw new InvalidEiComponentConfigurationException(
					'Only EiProps of properties with join table relations can be drafted.');
		}
		
		$this->setupEmbeddedEditEiCommand();
		
		// reason to remove: orphans should never remain in db on embeddedeiprops
		$entityProperty = $this->getRelationEntityProperty();
		if (!$entityProperty->getRelation()->isOrphanRemoval()) {
			if (!$this->getOrphansAllowed()) {
				throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
						. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
						. ' which removes orphans or an EiProp configuration with ' 
						. RelationConfig::ATTR_ORPHANS_ALLOWED_KEY . '=true.');
			}
			
			if (!$this->getRelationEntityProperty()->isMaster() && !$this->isSourceMany()
					&& !$this->getTargetMasterAccessProxy()->getConstraint()->allowsNull()) {
				throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
						. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
						. ' which removes orphans or target ' . $this->getTargetMasterAccessProxy()
						. ' must accept null.');
			}
		}
		
		
// 		if (!$this->getRelationEntityProperty()->isMaster()) {
// 			$entityProperty = $this->getRelationEntityProperty();
// 			if (!$entityProperty->getRelation()->isOrphanRemoval()
// 					&& (!$this->isSourceMany() && !$this->getTargetMasterAccessProxy()->getConstraint()->allowsNull())) {
								
// 				throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
// 						. TypeUtils::prettyPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
// 						. ' which removes orphans or target ' . $this->getTargetMasterAccessProxy()
// 						. ' must accept null.');
// 			}
// 		}
		
			
// 		$this->embeddedPseudoCommand = new EmbeddedPseudoCommand($this->getTarget());
// 		$this->getTarget()->getEiEngine()->getEiCommandCollection()->add($this->embeddedPseudoCommand);
		
// 		$this->embeddedEditPseudoCommand = new EmbeddedEditPseudoCommand($this->getRelationEiProp()->getEiEngine()->getEiMask()->getEiType()->getEiMask()->getLabel() 
// 						. ' > ' . $this->relationEiProp->getLabel() . ' Embedded Edit', 
// 				$this->getRelationEiProp()->getId(), $this->getTarget()->getId());
		
// 		$this->getTarget()->getEiEngine()->getEiCommandCollection()->add($this->embeddedEditPseudoCommand);
	}
	
	public function isReadOnlyRequired(EiEntry $mapping, EiFrame $eiFrame) {
		if (parent::isReadOnlyRequired($mapping, $eiFrame) || $this->hasRecursiveConflict($eiFrame)) return true;

		$esConstraint = $eiFrame->getManageState()->getSecurityManager()
				->getConstraintBy($this->getTarget());
		
		return $esConstraint !== null
				&& !$esConstraint->isEiCommandAvailable($this->embeddedEditPseudoCommand);	
	}
	
// 	public function completeMagCollection(MagCollection $magCollection) {
// 		$dtc = new DynamicTextCollection('rocket');
// 		$magCollection->addMag(DraftablePropertyEiPropAdapter::ATTR_DRAFTABLE_KEY,
// 				new BoolMag($dtc->translate('ei_impl_draftable_label'), self::OPTION_DRAFTABLE_DEFAULT));
// 		$magCollection->addMag(TranslatableEiPropAdapter::OPTION_TRANSLATION_ENABLED_KEY,
// 				new BoolMag($dtc->translate('ei_impl_translatable_label'), self::OPTION_TRANSLATION_ENABLED_DEFAULT));
		
// 		parent::completeMagCollection($magCollection);
// 		return $magCollection;
// 	}
	
	const OPTION_DRAFTABLE_DEFAULT = false;
	const OPTION_TRANSLATION_ENABLED_DEFAULT = false;
	
	public function isDraftable() {
		return false;
		return $this->relationEiProp->getDataSet()->get(DraftablePropertyEiPropAdapter::ATTR_DRAFTABLE_KEY, 
				self::OPTION_DRAFTABLE_DEFAULT);
	}
	
// 	public function isTranslationEnabled() {
// 		return $this->relationEiProp->getDataSet()->get(TranslatableEiPropAdapter::OPTION_TRANSLATION_ENABLED_KEY,
// 				self::OPTION_TRANSLATION_ENABLED_DEFAULT);
// 	}
	
	protected function configureTargetEiFrame(EiFrame $targetEiFrame, EiFrame $eiFrame, 
			EiObject $eiObject = null, $editCommandRequired = null) {
		parent::configureTargetEiFrame($targetEiFrame, $eiFrame, $eiObject);
		
		$targetEiFrame->setOverviewDisabled(true);
		
// 		if ($targetEiFrame->isPseudo()) {
// 			if ($editCommandRequired) {
// 				$targetEiFrame->setExecutedEiCommand($this->embeddedEditPseudoCommand);
// 			} else {
// 				$targetEiFrame->setExecutedEiCommand($this->embeddedPseudoCommand);
// 			}
// 			return;
// 		}

		if ($eiObject !== null && null !== $targetEiFrame->getOverviewUrlExt() 
				&& null !== $targetEiFrame->getDetailPathExt()) {
			$pathExt = $eiFrame->getControllerContext()->toPathExt()->ext(
					$eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getEntryDetailPathExt($eiObject->toEntryNavPoint()));
			$targetEiFrame->setOverviewPathExt($pathExt);
			$targetEiFrame->setDetailPathExt($pathExt);
		}
		
		$targetEiFrame->setDetailBreadcrumbLabelOverride($this->relationEiProp->getLabelLstr()
				->t($targetEiFrame->getN2nContext()->getN2nLocale()));
		$targetEiFrame->setDetailDisabled(true);
	}
}

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

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\EiObject;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use rocket\impl\ei\component\prop\relation\command\RelationJhtmlController;
use n2n\util\uri\Url;
use n2n\web\http\HttpContext;
use rocket\ei\util\Eiu;

class SelectEiPropRelation extends EiPropRelation {
	private $embeddedAddEnabled = false;
	private $hiddenIfTargetEmpty = false;
	
	protected $embeddedPseudoEiCommand;
	protected $embeddedEditPseudoEiCommand;
	
	public function init(Eiu $eiu, EiType $targetEiType, EiMask $targetEiMask, array $targetEiTypeExtensions) {
		parent::init($eiu, $targetEiType, $targetEiMask, $targetEiTypeExtensions);

		if ($this->isEmbeddedAddEnabled() && !$this->isPersistCascaded()) {
			throw new InvalidEiComponentConfigurationException(
					'Enabled embedded add option requires EntityProperty which cascades persist.');
		}
		
// 		if ($this->isEmbeddedAddEnabled()) {
			$this->setupEmbeddedEditEiCommand();
// 		}
		
// 		if ($this->isEmbeddedAddEnabled()) {
// 			$this->embeddedEditPseudoEiCommand = new EmbeddedEditPseudoCommand(
// 					$this->getRelationEiProp()->getEiEngine()->getEiMask()->getEiType()->getEiMask()->getLabel() . ' > ' 
// 							. $this->relationEiProp->getLabel() . ' Embedded Add', 
// 					$this->getRelationEiProp()->getId(), $this->getTarget()->getId());
// 			$this->getTarget()->getEiEngine()->getEiCommandCollection()->add($this->embeddedEditPseudoEiCommand);
// 		}
		
// 		$this->embeddedPseudoEiCommand = new EmbeddedPseudoCommand($this->getTarget());
// 		$this->target->getEiEngine()->getEiCommandCollection()->add($this->embeddedPseudoEiCommand);

	}
	
	/**
	 * @return bool
	 */
	public function isEmbeddedAddEnabled(): bool {
		return $this->embeddedAddEnabled;
	}
	
	/**
	 * @param bool $embeddedAddEnabled
	 */
	public function setEmbeddedAddEnabled(bool $embeddedAddEnabled) {
		$this->embeddedAddEnabled = $embeddedAddEnabled;
	}
	
	/**
	 * @return bool
	 */
	public function isHiddenIfTargetEmpty() {
		return $this->hiddenIfTargetEmpty;
	}
	
	/**
	 * @param bool $hiddenIfTargetEmpty
	 */
	public function setHiddenIfTargetEmpty(bool $hiddenIfTargetEmpty) {
		$this->hiddenIfTargetEmpty = $hiddenIfTargetEmpty;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @return boolean
	 */
	public function isEmbeddedAddActivated(EiFrame $eiFrame) {
		return $this->isEmbeddedAddEnabled() /*&& !$this->hasRecursiveConflict($eiFrame)
				&& $eiFrame->isEiCommandAvailable($this->embeddedEditPseudoEiCommand)*/;
	}
	
	protected function configureTargetEiFrame(EiFrame $targetEiFrame, EiFrame $eiFrame, 
			EiObject $eiObject = null, $editCommandRequired = null) {
		parent::configureTargetEiFrame($targetEiFrame, $eiFrame, $eiObject, $editCommandRequired);
		
// 		if (!$this->isTargetMany()) {
// 			$targetEiFrame->setOverviewDisabled(true);
// 			$targetEiFrame->setDetailBreadcrumbLabel($this->buildDetailLabel($eiFrame, $eiObject));
// 			return;
// 		}
		
// 		$targetEiFrame->setOverviewBreadcrumbLabel($this->buildDetailLabel($eiFrame, $eiObject));
		
		
	}
	
	protected function buildDetailLabel(EiFrame $eiFrame) {
		$label = $this->relationEiProp->getLabel();
		
		do {
			if ($eiFrame->isDetailDisabled() 
					&& null !== ($detaiLabel = $eiFrame->getDetailBreadcrumbLabel())) {
				$label = $detaiLabel . ' > ' . $label; 
			}
		} while (null !== ($eiFrame = $eiFrame->getParent()));
		
		return $label;
	}
	

	public function buildTargetOverviewToolsUrl(EiFrame $eiFrame, HttpContext $httpContext): Url {
		$contextUrl = $httpContext->getControllerContextPath($eiFrame->getControllerContext())
				->ext($this->relationEiCommand->getWrapper()->getEiCommandPath(), 'rel', $this->relationAjahEiCommand->getWrapper()->getEiCommandPath())->toUrl();
		return RelationJhtmlController::buildSelectToolsUrl($contextUrl);
	}
}

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
namespace rocket\ei\component;

use rocket\ei\manage\ManageState;
use n2n\web\http\controller\ControllerContext;
use rocket\ei\EiEngine;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\critmod\filter\FilterCriteriaConstraint;
use rocket\ei\util\Eiu;
use rocket\ei\manage\frame\Boundry;
use rocket\ei\EiCommandPath;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;
use rocket\ei\component\prop\ForkEiProp;
use rocket\ei\EiException;
use rocket\ei\EiPropPath;
use n2n\util\type\TypeUtils;
use rocket\ei\manage\frame\EiForkLink;

class EiFrameFactory {
	private $eiEngine;
	
	public function __construct(EiEngine $eiEngine) {
		$this->eiEngine = $eiEngine;		
	}
	
	/**
	 * @param ControllerContext $controllerContext
	 * @param ManageState $manageState
	 * @param EiFrame $parentEiFrame
	 * @param EiCommandPath $eiCommandPath
	 * @throws InaccessibleEiCommandPathException
	 * @throws UnknownEiComponentException
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	public function create(ManageState $manageState) {
		$eiFrame = new EiFrame($this->eiEngine, $manageState);
		
		$eiMask = $this->eiEngine->getEiMask();
		
		if (null !== ($filterSettingGroup = $eiMask->getFilterSettingGroup())) {
			$filterDefinition = $eiFrame->getFilterDefinition(); // $this->eiEngine->createFramedFilterDefinition($eiFrame);
			if ($filterDefinition !== null) {
				$eiFrame->getBoundry()->addCriteriaConstraint(Boundry::TYPE_HARD_FILTER,
						new FilterCriteriaConstraint($filterDefinition->createComparatorConstraint($filterSettingGroup)));
			}
		}
		
		if (null !== ($sortSettingGroup = $eiMask->getSortSettingGroup())) {
			$sortDefinition = $eiFrame->getSortDefinition(); //$this->eiEngine->createFramedSortDefinition($eiFrame);
			if ($sortDefinition !== null) {
				$eiFrame->getBoundry()->addCriteriaConstraint(Boundry::TYPE_HARD_SORT, 
						$sortDefinition->createCriteriaConstraint($sortSettingGroup));
			}
		}
		
		return $eiFrame;
	}
	
	/**
	 * @param ManageState $manageState
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	public function createRoot(ManageState $manageState) {
		$eiFrame = $this->create($manageState);
		
		$this->setupEiFrame($eiFrame);
		
		return $eiFrame;
	}
	
	private function setupEiFrame($eiFrame) {
		$eiu = new Eiu($eiFrame);
		foreach ($eiFrame->getContextEiEngine()->getEiMask()->getEiModificatorCollection()->toArray() as $eiModificator) {
			$eiModificator->setupEiFrame($eiu);
		}
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiForkLink $eiForkLink
	 * @throws EiException
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	public function createForked(EiPropPath $eiPropPath, EiForkLink $eiForkLink) {
		$eiProp = $this->eiEngine->getEiMask()->getEiPropCollection()->getByPath($eiPropPath);
		if (!($eiProp instanceof ForkEiProp)) {
			throw new EiException('EiProp ' . $eiProp . ' does not implement ' . ForkEiProp::class);
		}
		
		$parentEiFrame = $eiForkLink->getParent();
		$eiu = new Eiu($parentEiFrame, $eiForkLink->getParentEiObject(), $eiPropPath);
		$forkedEiFrame = $eiProp->createForkedEiFrame($eiu, $eiForkLink);
		
		if ($forkedEiFrame->hasEiExecution()) {
			throw new EiException(TypeUtils::prettyMethName(get_class($eiProp), 'createForkedEiFrame')
					. ' must return an EiFrame which is not yet executed.');
		}
		
		$forkedEiFrame->setEiForkLink($eiForkLink);
		
		$forkedEiFrame->setBaseUrl($parentEiFrame->getForkUrl(null, $eiPropPath,
				$eiForkLink->getMode(), $eiForkLink->getParentEiObject()));
		
		$this->setupEiFrame($forkedEiFrame);
		
		return $forkedEiFrame;
	}
	
	
}

// class ForkBaseLinkProvider implements EiFrameListener {
// 	private $parentEiFrame;
// 	private $forkedEiFrame;
// 	private $eiPropPath;
// 	private $eiForkLink;
	
// 	function __construct(EiFrame $parentEiFrame, EiFrame $forkedEiFrame, EiPropPath $eiPropPath, 
// 			EiForkLink $eiForkLink) {
// 		$this->parentEiFrame = $parentEiFrame;
// 		$this->forkedEiFrame = $forkedEiFrame;
// 		$this->eiPropPath = $eiPropPath;
// 		$this->eiForkLink = $eiForkLink;
// 	}
	
// 	function onNewEiEntry(EiEntry $eiEntry) {
// 	}
	
// 	function whenExecuted(EiExecution $eiExecution) {
// 		if ($this->forkedEiFrame->hasBaseUrl()) {
// 			return;
// 		}
		
// 		$eiCommandPath = EiCommandPath::from($eiExecution->getEiCommand());
		
// 		$this->forkedEiFrame->setBaseUrl($this->parentEiFrame->getForkUrl($eiCommandPath, $this->eiPropPath, 
// 				$this->eiForkLink->getMode(), $this->eiForkLink->getParentEiObject()));
// 	}
// }

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
namespace rocket\ei;

use n2n\core\container\N2nContext;
use rocket\ei\component\CritmodFactory;
use rocket\ei\component\SecurityFactory;
use rocket\ei\manage\draft\stmt\DraftMetaInfo;
use rocket\ei\component\GuiFactory;
use rocket\ei\component\EiEntryFactory;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\manage\critmod\sort\SortDefinition;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\component\DraftDefinitionFactory;
use rocket\ei\manage\draft\DraftDefinition;
use rocket\ei\mask\EiMask;
use rocket\ei\component\prop\GenericEiProp;
use rocket\ei\component\prop\ScalarEiProp;
use rocket\ei\manage\generic\ScalarEiDefinition;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\manage\generic\GenericEiDefinition;
use rocket\ei\manage\critmod\quick\QuickSearchDefinition;
use rocket\ei\manage\ManageState;
use rocket\ei\component\EiFrameFactory;
use rocket\ei\manage\gui\EiEntryGui;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\frame\EiForkLink;
use rocket\ei\component\IdNameFactory;
use rocket\ei\manage\gui\GuiDefinition;
use rocket\ei\manage\idname\IdNameDefinition;

class EiEngine {
	/**
	 * @var EiMask
	 */
	private $eiMask;
	
	private $scalarEiDefinition;
	private $genericEiDefinition;

	/**
	 * @param EiMask $eiMask
	 */
	function __construct(EiMask $eiMask) {
		$this->eiMask = $eiMask;
	}
	
	/**
	 * @return \rocket\ei\mask\EiMask
	 */
	function getEiMask() {
		return $this->eiMask;
	}
	
	/**
	 * @return EiMask
	 */
	private function getSupremeEiMask() {
		$eiType = $this->eiMask->getEiType();
		if (!$eiType->hasSuperEiType()) {
			return $this->eiMask;
		}
		
		return $eiType->getSupremeEiType()->getEiMask();
	}
	
	function getSupremeEiEngine() {
		return $this->getSupremeEiMask()->getEiEngine();
	}
	
	private $eiFrameFactory;
	
	/**
	 * @return \rocket\ei\component\EiFrameFactory
	 */
	private function getEiFrameFactory() {
		if ($this->eiFrameFactory === null) {
			$this->eiFrameFactory = new EiFrameFactory($this);
		}
		
		return $this->eiFrameFactory;
	}
	
	/**
	 * @param ManageState $manageState
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	function createEiFrame(ManageState $manageState) {
		return $this->getEiFrameFactory()->create($manageState);
	}
	
	/**
	 * @param ManageState $manageState
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	function createRootEiFrame(ManageState $manageState) {
		return $this->getEiFrameFactory()->createRoot($manageState);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiForkLink $eiForkLink
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	function createForkedEiFrame(EiPropPath $eiPropPath, EiForkLink $eiForkLink) {
		return $this->getEiFrameFactory()->createForked($eiPropPath, $eiForkLink);
	}
	
	/**
	 * @return GuiDefinition
	 */
	function createGuiDefinition(N2nContext $n2nContext) {
		return (new GuiFactory($this->eiMask))->createGuiDefinition($n2nContext);
	}

	/**
	 * @param N2nContext $n2nContext
	 * @return IdNameDefinition
	 */
	function createIdNameDefinition(N2nContext $n2nContext) {
		$idNameFactory = new IdNameFactory($this->eiMask);
		return $idNameFactory->createIdNameDefinition($n2nContext);
	}
	
	
	
	
	
	
	
	
	
	
	
	private $critmodFactory;
	
	private function getCritmodFactory() {
		if ($this->critmodFactory === null) {
			$this->critmodFactory = new CritmodFactory($this->eiMask->getEiPropCollection(), 
					$this->eiMask->getEiModificatorCollection());
		}
		
		return $this->critmodFactory;
	}
	
	function createFramedFilterDefinition(EiFrame $eiFrame): FilterDefinition {
		return $this->getCritmodFactory()->createFramedFilterDefinition($eiFrame);
	}
	
	function createFilterDefinition(N2nContext $n2nContext): FilterDefinition {
		return $this->getCritmodFactory()->createFilterDefinition($n2nContext);
	}
	
	function createFramedSortDefinition(EiFrame $eiFrame): SortDefinition {
		return $this->getCritmodFactory()->createFramedSortDefinition($eiFrame);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\mask\EiMask::createSortDefinition($n2nContext)
	 */
	function createSortDefinition(N2nContext $n2nContext): SortDefinition {
		return $this->getCritmodFactory()->createSortDefinition($n2nContext);
	}
	
	function createFramedQuickSearchDefinition(EiFrame $eiFrame): QuickSearchDefinition {
		return $this->getCritmodFactory()->createFramedQuickSearchDefinition($eiFrame);
	}
	
	private $securityFactory;
	
	private function getSecurityFactory() {
		if ($this->securityFactory === null) {
			$this->securityFactory = new SecurityFactory($this->eiMask->getEiPropCollection(),
					$this->eiMask->getEiCommandCollection(), $this->eiMask->getEiModificatorCollection());
		}
		
		return $this->securityFactory;
	}
	
	function createSecurityFilterDefinition(N2nContext $n2nContext) {
		return $this->getSecurityFactory()->createSecurityFilterDefinition($n2nContext);
	}
	
	function createPrivilegeDefinition(N2nContext $n2nContext) {
		$securityFactory = new SecurityFactory($this->eiMask->getEiPropCollection(), 
				$this->eiMask->getEiCommandCollection(), $this->eiMask->getEiModificatorCollection());
		return $securityFactory->createPrivilegedDefinition($n2nContext);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiObject $eiObject
	 * @param EiEntry $copyFrom
	 * @return EiEntry
	 */
	function createFramedEiEntry(EiFrame $eiFrame, EiObject $eiObject, ?EiEntry $copyFrom, array $eiEntryConstraints) {
		$mappingFactory = new EiEntryFactory($this->eiMask, $this->eiMask->getEiPropCollection(), 
				$this->eiMask->getEiModificatorCollection());
		return $mappingFactory->createEiEntry($eiFrame, $eiObject, $copyFrom, $eiEntryConstraints);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $eiEntry
	 * @param EiPropPath $forkEiPropPath
	 * @param object $object
	 * @param EiEntry $copyFrom
	 * @return \rocket\ei\manage\entry\EiFieldMap
	 */
	function createFramedEiFieldMap(EiFrame $eiFrame, EiEntry $eiEntry, EiPropPath $forkEiPropPath, object $object, 
			?EiEntry $copyFrom) {
		$mappingFactory = new EiEntryFactory($this->eiMask, $this->eiMask->getEiPropCollection(),
				$this->eiMask->getEiModificatorCollection());
		
		return $mappingFactory->createEiFieldMap($eiFrame, $eiEntry, $forkEiPropPath, $object, $copyFrom);
	}
	
	/**
	 * @param EiFrame $eiFrame
	 * @param EiEntry $from
	 * @param EiEntry $to
	 * @param array $eiPropPaths
	 */
	function copyValues(EiFrame $eiFrame, EiEntry $from, EiEntry $to, array $eiPropPaths = null) {
		ArgUtils::valArray($eiPropPaths, EiPropPath::class, true, 'eiPropPaths');
		$mappingFactory = new EiEntryFactory($this->eiMask, $this->eiMask->getEiPropCollection(), 
				$this->eiMask->getEiModificatorCollection());
		$mappingFactory->copyValues($eiFrame, $from, $to, $eiPropPaths);
	}
	
// 	function createFramedEiGuiFrame(EiFrame $eiFrame, int $viewMode) {
// 		$guiFactory = new GuiFactory($this->eiMask);
// 		return $guiFactory->createEiGuiFrame($eiFrame, $viewMode);
// 	}
	
// 	function createEiGuiFrame(int $viewMode, DisplayStructure $displayStructure) {
// 		$eiMask = $this->eiMask;
// 		if ($this->eiType === null) {
// 			$eiMask = $this->eiType->getEiTypeExtensionCollection()->getOrCreateDefault();
// 		}
		
// 		$guiFactory = new GuiFactory($this->eiMask);
// 		return $guiFactory->createEiEntryGui($eiMask, $eiuEntry, $viewMode, $eiPropPaths);
// 	}
	
	function getDraftDefinition(): DraftDefinition {
		if ($this->draftDefinition !== null) {
			return $this->draftDefinition;
		}
		
		$eiThing = $this->eiMask ?? $this->eiType;
		do {
			$id = $eiThing->getId();
		} while (($id === null || $eiThing->getEiEngine()->getEiMask()->getEiPropCollection()->isEmpty(true))
				&& null !== ($eiThing = $eiThing->getMaskedEiEngineModel()));
		return $this->draftDefinition = (new DraftDefinitionFactory($this->eiMask->getEntityModel(), 
						$this->eiPropCollection, $this->eiModificatorCollection))
				->create(DraftMetaInfo::buildTableName($eiThing));
	}

	/**
	 * @return \rocket\ei\manage\generic\GenericEiDefinition
	 */
	function getGenericEiDefinition() {
		if ($this->genericEiDefinition !== null) {
			return $this->genericEiDefinition;
		}
		
		$this->genericEiDefinition = new GenericEiDefinition();
		$genericEiPropertyMap = $this->genericEiDefinition->getMap();
		foreach ($this->eiMask->getEiPropCollection() as $eiProp) {
			if ($eiProp instanceof GenericEiProp 
					&& $genericEiProperty = $eiProp->getGenericEiProperty()) {
				ArgUtils::valTypeReturn($genericEiProperty, GenericEiProperty::class, $eiProp, 
						'getGenericEiProperty', true);
				$genericEiPropertyMap->offsetSet(EiPropPath::from($eiProp), $genericEiProperty);		
			}
		}
		return $this->genericEiDefinition;
	}
	
	/**
	 * @return \rocket\ei\manage\generic\ScalarEiDefinition
	 */
	function getScalarEiDefinition() {
		if ($this->scalarEiDefinition !== null) {
			return $this->scalarEiDefinition;
		}
		
		$this->scalarEiDefinition = new ScalarEiDefinition();
		$scalarEiProperties = $this->scalarEiDefinition->getMap();
		foreach ($this->eiMask->getEiPropCollection() as $eiProp) {
			if ($eiProp instanceof ScalarEiProp
					&& null !== ($scalarEiProperty = $eiProp->getScalarEiProperty())) {
				ArgUtils::valTypeReturn($scalarEiProperty, ScalarEiProperty::class, $eiProp, 'getScalarEiProperty', true);
				$scalarEiProperties->offsetSet(EiPropPath::from($eiProp), $scalarEiProperty);
			}
		}
		return $this->scalarEiDefinition;
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 * @param HtmlView $view
	 * @return \rocket\ei\manage\gui\control\GuiControl[]
	 */
	function createEiEntryGuiControls(EiEntryGui $eiEntryGui, HtmlView $view) {
		return (new GuiFactory($this->eiMask))->createEntryGuiControls($eiEntryGui, $view);
	}
	
}
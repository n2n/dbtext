<?php
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\ei\component;

// use n2n\util\type\ArgUtils;
// use rocket\ei\manage\gui\GuiDefinition;
// use rocket\ei\manage\gui\EiEntryGuiAssembler;
// use rocket\ei\manage\gui\EiEntryGui;
// use rocket\ei\component\prop\GuiEiProp;
// use rocket\ei\EiPropPath;
// use rocket\ei\manage\gui\GuiPropFork;
// use rocket\ei\manage\gui\GuiProp;
// use rocket\ei\util\entry\EiuEntry;
// use rocket\ei\mask\EiMask;
// use rocket\ei\manage\DefPropPath;
// use rocket\ei\manage\gui\EiGuiFrame;
// use n2n\impl\web\ui\view\html\HtmlView;
// use rocket\ei\manage\entry\EiEntry;
// use rocket\ei\util\Eiu;
// use rocket\ei\mask\model\ControlOrder;
// use rocket\ei\manage\EiObject;
// use n2n\l10n\N2nLocale;
// use n2n\core\container\N2nContext;
// use rocket\ei\component\prop\GuiEiPropFork;
// use rocket\ei\manage\idname\SummarizedStringBuilder;

// class ControlFactory {
// 	private $eiMask;
	
// 	public function __construct(EiMask $eiMask) {
// 		$this->eiMask = $eiMask;
// 	}
	
// 	/**
// 	 * @param N2nContext $n2nContext
// 	 * @param GuiDefinition|null $guiDefinition
// 	 * @return \rocket\ei\manage\gui\GuiDefinition
// 	 */
// 	public function createEntryGuiControlDefinition(N2nContext $n2nContext, &$guiDefinition = null) {
// 		$eiu = new Eiu($n2nContext, $this->eiMask);
		
// 		$guiDefinition = new GuiDefinition($this->eiMask->getLabelLstr());
// 		$guiDefinition->setIdentityStringPattern($this->eiMask->getIdentityStringPattern());
		
// 		foreach ($this->eiMask->getEiPropCollection() as $eiPropPathStr => $eiProp) {
// 			$eiPropPath = EiPropPath::create($eiPropPathStr);
			
// 			if (($eiProp instanceof GuiEiProp) && null !== ($guiProp = $eiProp->buildGuiProp($eiu))){
// 				ArgUtils::valTypeReturn($guiProp, GuiProp::class, $eiProp, 'buildGuiProp');
				
// 				$guiDefinition->putGuiProp($eiPropPath, $guiProp, EiPropPath::from($eiProp));
// 			}
			
// 			if (($eiProp instanceof GuiEiPropFork) && null !== ($guiPropFork = $eiProp->buildGuiPropFork($eiu))){
// 				ArgUtils::valTypeReturn($guiPropFork, GuiPropFork::class, $eiProp, 'buildGuiPropFork');
				
// 				$guiDefinition->putGuiPropFork($eiPropPath, $guiPropFork);
// 			}
// 		}
		
// 		foreach ($this->eiMask->getEiModificatorCollection() as $eiModificator) {
// 			$eiModificator->setupGuiDefinition($eiu);
// 		}
		
// 		return $guiDefinition;
// 	}
	
// 	/**
// 	 * @param EiObject $eiObject
// 	 * @param N2nLocale $n2nLocale
// 	 * @return string
// 	 */
// 	private function createDefaultIdentityString(EiObject $eiObject, N2nLocale $n2nLocale, GuiDefinition $guiDefinition) {
// 		$eiType = $eiObject->getEiEntityObj()->getEiType();
		
// 		$idPatternPart = null;
// 		$namePatternPart = null;
		
// 		foreach ($guiDefinition->getStringRepresentableGuiProps() as $eiPropPathStr => $guiProp) {
// 			if ($eiPropPathStr == $this->eiMask->getEiType()->getEntityModel()->getIdDef()->getPropertyName()) {
// 				$idPatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
// 			} else {
// 				$namePatternPart = SummarizedStringBuilder::createPlaceholder($eiPropPathStr);
// 			}
			
// 			if ($namePatternPart !== null) break;
// 		}
		
// 		if ($idPatternPart === null) {
// 			$idPatternPart = $eiObject->getEiEntityObj()->hasId() ?
// 			$this->eiMask->getEiType()->idToPid($eiObject->getEiEntityObj()->getId()) : 'new';
// 		}
		
// 		if ($namePatternPart === null) {
// 			$namePatternPart = $this->eiMask->getLabelLstr()->t($n2nLocale);
// 		}
		
// 		return $guiDefinition->createIdentityString($namePatternPart . ' #' . $idPatternPart, $eiObject, $n2nLocale);
// 	}
	
// 	/**
// 	 * @param EiObject $eiObject
// 	 * @param N2nLocale $n2nLocale
// 	 * @return string
// 	 */
// 	public function createIdentityString(EiObject $eiObject, N2nLocale $n2nLocale, GuiDefinition $guiDefinition) {
// 		$identityStringPattern = $this->eiMaskDef->getIdentityStringPattern();
		
// 		if ($manageState === null || $identityStringPattern === null) {
// 			return $this->createDefaultIdentityString($eiObject, $n2nLocale, $guiDefinition);
// 		}
		
// 		return $guiDefinition->createIdentityString($identityStringPattern, $eiObject, $n2nLocale);
// 	}
	
// // 	/**
// // 	 * @param EiGuiFrame $eiGuiFrame
// // 	 * @param HtmlView $view
// // 	 * @return Control[]
// // 	 */
// // 	public function createOverallControls(EiGuiFrame $eiGuiFrame, HtmlView $view) {
// // 		$eiu = new Eiu($eiGuiFrame);
		
// // 		$controls = array();
		
// // 		foreach ($this->eiMask->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// // 			if (!($eiCommand instanceof OverallControlComponent) || !$eiu->frame()->isExecutableBy($eiCommand)) {
// // 				continue;
// // 			}
			
// // 			$overallControls = $eiCommand->createOverallControls($eiu, $view);
// // 			ArgUtils::valArrayReturn($overallControls, $eiCommand, 'createOverallControls', Control::class);
// // 			foreach ($overallControls as $controlId => $control) {
// // 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
// // 			}
// // 		}
		
// // 		return $this->eiMask->getDisplayScheme()->getOverallControlOrder()->sort($controls);
// // 	}
	
// 	/**
// 	 * @param EiEntryGui $eiEntryGui
// 	 * @param HtmlView $view
// 	 * @return Control[]
// 	 */
// 	public function createEntryGuiControls(EiEntryGui $eiEntryGui, HtmlView $view) {
// 		$eiu = new Eiu($eiEntryGui);
		
// 		$controls = array();
		
// 		foreach ($this->eiMask->getEiCommandCollection() as $eiCommandId => $eiCommand) {
// 			if (!($eiCommand instanceof EntryGuiControlComponent)
// 					|| !$eiu->entry()->access()->isExecutableBy($eiCommand)) {
// 				continue;
// 			}
			
// 			$entryControls = $eiCommand->createEntryGuiControls($eiu, $view);
// 			ArgUtils::valArrayReturn($entryControls, $eiCommand, 'createEntryGuiControls', Control::class);
// 			foreach ($entryControls as $controlId => $control) {
// 				$controls[ControlOrder::buildControlId($eiCommandId, $controlId)] = $control;
// 			}
// 		}
		
// 		return $this->eiMask->getDisplayScheme()->getEntryGuiControlOrder()->sort($controls);
// 	}
	
// 	/**
// 	 * @param EiMask $eiMask
// 	 * @param EiuEntry $eiuEntry
// 	 * @param int $viewMode
// 	 * @param array $eiPropPaths
// 	 * @return EiEntryGui
// 	 */
// 	public static function createEiEntryGui(EiGuiFrame $eiGuiFrame, EiEntry $eiEntry, array $defPropPaths, int $treeLevel = null) {
// 		ArgUtils::valArrayLike($defPropPaths, DefPropPath::class);
		
// 		$eiEntryGui = new EiEntryGui($eiGuiFrame, $eiEntry, $treeLevel);
		
// 		$guiFieldAssembler = new EiEntryGuiAssembler($eiEntryGui);
		
// 		foreach ($defPropPaths as $defPropPath) {
// 			$guiFieldAssembler->assembleGuiField($defPropPath);
// 		}
		
// 		$guiFieldAssembler->finalize();
		
// 		return $eiEntryGui;
// 	}
// }


// // class ModEiGuiListener implements EiGuiListener {
// // 	private $eiModificatorCollection;

// // 	public function __construct(EiModificatorCollection $eiModificatorCollection) {
// // 		$this->eiModificatorCollection = $eiModificatorCollection;
// // 	}

// // 	public function onInitialized(EiGuiFrame $eiGuiFrame) {
// // 		foreach ($this->eiModificatorCollection as $eiModificator) {
// // 			$eiModificator->onEiGuiFrameInitialized($eiGuiFrame);
// // 		}
// // 	}

// // 	public function onNewEiEntryGui(EiEntryGui $eiEntryGui) {
// // 		foreach ($this->eiModificatorCollection as $eiModificator) {
// // 			$eiModificator->onNewEiEntryGui($eiEntryGui);
// // 		}
// // 	}

// // 	public function onNewView(HtmlView $view) {
// // 		foreach ($this->eiModificatorCollection as $eiModificator) {
// // 			$eiModificator->onNewView($view);
// // 		}
// // 	}

// // }
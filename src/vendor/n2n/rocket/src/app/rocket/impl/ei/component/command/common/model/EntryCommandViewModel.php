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
// namespace rocket\impl\ei\component\command\common\model;

// use n2n\util\uri\Url;
// use n2n\web\http\HttpContext;
// use rocket\ei\util\frame\EiuFrame;
// use rocket\ei\util\entry\EiuEntry;

// class EntryCommandViewModel {
// 	private $title;
// 	private $eiuFrame;
// 	private $cancelUrl;
// 	private $eiuEntry;
	
// 	public function __construct(EiuFrame $eiuFrame, Url $cancelUrl = null, EiuEntry $eiuEntry = null) {
// 		$this->eiuFrame = $eiuFrame;
// 		$this->cancelUrl = $cancelUrl;
// 		$this->eiuEntry = $eiuEntry;
// 	}
	
// 	public function getTitle() {
// 		if ($this->title !== null) return $this->title;
			
// 		return $this->title = $this->eiuEntry->createIdentityString();
// 	}
	
// 	public function setTitle($title) {
// 		$this->title = $title;
// 	}
	
// 	/**
// 	 * @return \rocket\ei\util\frame\EiuFrame
// 	 */
// 	public function getEiuFrame() {
// 		return $this->eiuFrame;
// 	}
	
// 	public function getEiuEntry() {
// 		return $this->eiuEntry;
// 	}
	
// 	private $latestDraft = null;
// 	private $historicizedDrafts = array();
	
// 	public function initializeDrafts() {
// 		return;
// 		$eiuEntry = $this->getEiuEntry();
// 		if ($eiuEntry->hasId() && $this->getEiuFrame()->isDraftingEnabled()) {
// 			$this->historicizedDrafts = $eiuEntry->lookupDrafts(0, 30);
// 		}
		
// 		if ($eiuEntry->isDraft() && $eiuEntry->isNew()) {
// 			$this->latestDraft = $eiuEntry->getDraft();
// 		}
	
// 		if (empty($this->historicizedDrafts) || $this->latestDraft !== null) return;
		
// 		$latestDraft = reset($this->historicizedDrafts);
// 		if (!$latestDraft->isPublished()) {
// 			$this->latestDraft = $latestDraft;
// 			array_shift($this->historicizedDrafts);
// 		}
// 	}
	
// 	public function hasDraftHistory() {
// 		return $this->latestDraft !== null || !empty($this->historicizedDrafts);
// 	}
	
// 	public function getSelectedDraft() {
// 		if ($this->getEiObject()->isDraft()) {
// 			return $this->getEiObject()->getDraft();
// 		}
		
// 		return null;
// 	}
	
// 	public function getLatestDraft() {
// 		return $this->latestDraft;
// 	}
	
// 	public function getHistoricizedDrafts() {
// 		return $this->historicizedDrafts;
// 	}
	
// 	public function isPreviewAvailable() {
// 		return $this->getEiuEntry()->isPreviewSupported();
// 	}
	
// // 	public function getEiuEntryGui() {
// // 		return $this->eiuEntryGui;
// // 	}
	
	
// // 	public function getInfoPathExt() {
// // 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint()->copy(false, false, true));
// // 	}
	
// // 	public function getPreviewPathExt() {
// // 		$previewType = $this->eiFrame->getPreviewType();
// // 		if (is_null($previewType)) $previewType = PreviewController::PREVIEW_TYPE_DEFAULT;

// // 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint(null, null, $previewType));
// // 	}
	
// // 	public function getCurrentPreviewType() {
// // 		return $this->currentPreviewType;
// // 	}
	
// // 	public function setCurrentPreviewType($currentPreviewType) {
// // 		$this->currentPreviewType = $currentPreviewType;
// // 	}
	
// 	public function getPreviewTypOptions() {
// 		return $this->eiMask->getPreviewTypeOptions($this->eiFrame, 
// 				$this->entryGuiModel->getEiEntryGui()->getViewMode());
// 	}
	
// 	public function getEiEntityObjUrl(HttpContext $httpContext) {
// 		return $this->eiFrame->getDetailUrl($httpContext, $this->entryGuiModel->getEiEntry()->toEntryNavPoint());
// 	}
	
// 	public function setCancelUrl(Url $cancelUrl) {
// 		$this->cancelUrl = $cancelUrl;
// 	}
	
// 	public function determineCancelUrl(HttpContext $httpContext) {
// 		if ($this->cancelUrl !== null) {
// 			return $this->cancelUrl;
// 		}
		
// 		$eiObject = null;
		
// 		if ($eiObject === null || $eiObject->isNew()) {
// 			return $this->eiuFrame->getEiFrame()->getOverviewUrl($httpContext);
// 		}
		
// 		return $this->eiFrame->getDetailUrl($httpContext, $this->entryGuiModel->getEiEntry()->toEntryNavPoint());	
// 	}
// }
// // class EntryViewInfo {
// // 	private $eiFrame;
// // 	private $commandEntryModel;
// // 	private $entryModel;
// // 	private $eiObject;
// // 	private $context;
// // 	private $exact;
// // 	private $previewController;
// // 	private $title;
	
// // 	public function __construct(CommandEntryModel $commandEntryModel = null, EntryModel $entryModel, PreviewController $previewController = null, $title = null) {
// // 		$this->eiFrame = $entryModel->getEiFrame();
// // 		$this->commandEntryModel = $commandEntryModel;
// // 		$this->entryModel = $entryModel;
// // 		$this->eiObject = $this->entryModel->getEiObject();
		
// // 		$this->context = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
// // 		$this->exact = $this->entryModel->getEiType();
		
// // 		$this->previewController = $previewController;
		
// // 		if (isset($title)) {
// // 			$this->title = $title;
// // 		} else {
// // 			$this->title = $this->exact->createIdentityString($this->eiObject->getEntityObj(),
// // 					$this->eiFrame->getN2nContext()->getN2nLocale());
// // 		}
// // 	}
	
// // 	public function getTitle() {
// // 		return $this->title;
// // 	}
	
// // 	public function getEiFrame()  {
// // 		return $this->eiFrame;
// // 	}
	
// // 	public function getEiObject() {
// // 		return $this->eiObject;
// // 	}
	
// // 	public function isInEditMode() {
// // 		return $this->entryModel instanceof EditEntryModel;
// // 	}
	
// // 	public function isNew() {
// // 		return $this->entryModel instanceof EditEntryModel && $this->entryModel->isNew();
// // 	}
	

	
// // 	public function getLangNavPoints() {
// // 		$currentTranslationN2nLocale = $this->eiObject->getTranslationN2nLocale();
		
// // 		$navPoints = array();
		
// // 		$this->ensureCommandEntryModel();
		
// // 		$mainTranslationN2nLocale = $this->commandEntryModel->getMainTranslationN2nLocale();
// // 		$navPoints[] = array(
// // 				'pathExt' => PathUtils::createPathExtFromEntryNavPoint(null, 
// // 						$this->eiFrame->toEntryNavPoint()->copy(false, true, false)),
// // 				'label' => $mainTranslationN2nLocale->getName($this->eiFrame->getN2nContext()->getN2nLocale()),
// // 				'active' => null === $currentTranslationN2nLocale);

// // 		foreach ($this->commandEntryModel->getTranslationN2nLocales() as $translationN2nLocale) {
// // 			$navPoints[] = array(
// // 					'pathExt' => PathUtils::createPathExtFromEntryNavPoint(null, 
// // 							$this->eiFrame->toEntryNavPoint(null, $translationN2nLocale)),
// // 					'label' => $translationN2nLocale->getName($this->eiFrame->getN2nContext()->getN2nLocale()),
// // 					'active'=> $translationN2nLocale->equals($currentTranslationN2nLocale));
// // 		}
		
// // 		return $navPoints;
// // 	}
	
// // 	public function getEiEntityObjPathExt() {
// // 		$previewType = $this->eiFrame->getPreviewType();
// // 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint()->copy(true));
// // 	}
	
// // 	private function ensureCommandEntryModel() {
// // 		if (!isset($this->commandEntryModel)) {
// // 			throw IllegalStateException::createDefault();
// // 		}
// // 	}
	
// // 	public function buildPathToDraft(Draft $draft) {
// // 		return PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint($draft->getId()));
// // 	}
	
// // 	public function getCurrentDraft() {
// // 		$this->ensureCommandEntryModel();
// // 		return $this->commandEntryModel->getCurrentDraft();
// // 	}
	
// // 	public function getHistoricizedDrafts() {
// // 		$this->ensureCommandEntryModel();
		
// // 		return $this->commandEntryModel->getHistoricizedDrafts();
// // 	}
	
// // 	public function isInPeview() {
// // 		return isset($this->previewController);
// // 	}
	
// // 	public function hasPreviewTypeNav() {
// // 		return isset($this->previewController) && sizeof((array) $this->previewController->getPreviewTypeOptions());
// // 	}
	
// // 	public function getPreviewTypeNavInfos() {
// // 		if (is_null($this->previewController)) return array();
		
// // 		$currentPreviewType = $this->eiFrame->getPreviewType();
// // 		$navPoints = array();
// // 		foreach ((array) $this->previewController->getPreviewTypeOptions() as $previewType => $label) {
// // 			$navPoints[(string) $previewType] = array('label' => $label,
// // 					'pathExt' => PathUtils::createPathExtFromEntryNavPoint(null, $this->eiFrame->toEntryNavPoint(null, null, $previewType)),
// // 					'active' => ($previewType == $currentPreviewType));
// // 		}
// // 		return $navPoints;
// // 	}
	
// // // 	public function get
// // }

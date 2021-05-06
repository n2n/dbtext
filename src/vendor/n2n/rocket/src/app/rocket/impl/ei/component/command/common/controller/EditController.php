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
namespace rocket\impl\ei\component\command\common\controller;

use n2n\l10n\DynamicTextCollection;
use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamQuery;
use rocket\ei\util\EiuCtrl;
use rocket\core\model\Rocket;
use rocket\si\control\SiButton;
use rocket\ei\util\Eiu;
use rocket\si\control\SiIconType;
use n2n\util\ex\IllegalStateException;

class EditController extends ControllerAdapter {
	const CONTROL_SAVE_KEY = 'save';
	const CONTROL_SAVE_AND_BACK_KEY = 'saveAndBack';
	const CONTROL_CANCEL_KEY = 'canel';
	
	private $dtc;
	private $eiuCtrl;
	
	public function prepare(DynamicTextCollection $dtc) {
		$this->dtc = $dtc;
		$this->eiuCtrl = EiuCtrl::from($this->cu());
	}
	
	public function index($pid) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		
		$this->eiuCtrl->pushOverviewBreadcrumb()
				->pushDetailBreadcrumb($eiuEntry)
				->pushCurrentAsSirefBreadcrumb($this->dtc->t('common_add_label'));
		
// 		$this->eiuCtrl->pushCurrentAsSirefBreadcrumb($this->dtc->t('common_add_label'), true, $eiuEntry);
		
		$this->eiuCtrl->forwardBulkyEntryZone($eiuEntry, false, true, false, $this->createControls());
	}
	
	private function createControls() {
		$eiuControlFactory = $this->eiuCtrl->eiu()->factory()->controls();
		$dtc = $this->eiuCtrl->eiu()->dtc(Rocket::NS);
		
		return [
			$eiuControlFactory->newCallback(self::CONTROL_SAVE_KEY,
					SiButton::primary($dtc->t('common_save_label'), SiIconType::ICON_SAVE),
					function (Eiu $eiu, array $inputEius) {
						return $this->handleInput($eiu, $inputEius);
					})
					->setInputHandled(true),
			$eiuControlFactory->newCallback(self::CONTROL_SAVE_AND_BACK_KEY,
					SiButton::primary($dtc->t('common_save_and_back_label'), SiIconType::ICON_SAVE),
					function (Eiu $eiu, array $inputEius) {
						return $this->handleInput($eiu, $inputEius)->redirectBack();
					})
					->setInputHandled(true),
			$eiuControlFactory->newCallback(self::CONTROL_CANCEL_KEY,
					SiButton::primary($dtc->t('common_cancel_label'), SiIconType::ICON_ARROW_LEFT),
					function (Eiu $eiu) {
						return $eiu->factory()->newControlResponse()->redirectBack();
					})
		];
	}
	
	/**
	 * @param Eiu $eiu
	 * @param Eiu[] $inputEius
	 */
	private function handleInput($eiu, $inputEius) {
		// 		$inputEiuEntries = [];
		foreach ($inputEius as $inputEiu) {
			$inputEiuEntry = $inputEiu->entry();
			// input eius are already validated.
			IllegalStateException::assertTrue($inputEiuEntry->save());
			// 			$inputEiuEntries[] = $inputEiuEntry;
		}
		
		return $eiu->factory()->newControlResponse()
		// 				->highlight(...$inputEiuEntries)
		;
	}
	
	public function doPreview($pid, $previewType, ParamQuery $refPath) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
		$previewController = $this->eiuCtrl->lookupPreviewController($previewType, $eiuEntry);
		
		$previewTypeOptions = $eiuEntry->getPreviewTypeOptions();
		
		$this->eiuCtrl->applyCommonBreadcrumbs($eiuEntry);
		
		$view = $this->createView('..\view\editPreview.html', array(
				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext())
						->ext('livepreviewsrc', $pid, $previewType),
				'currentPreviewType' => $previewType,
				'previewTypeOptions' => $previewTypeOptions,
				'entryCommandViewModel' => new EntryCommandViewModel($this->eiuCtrl->frame(), $redirectUrl, $eiuEntry)));
		$this->eiuCtrl->forwardView($view);
	}
	
	public function doPreviewSrc($pid, $previewType, array $delegateCmds = array()) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		$previewController = $this->eiuCtrl->lookupPreviewController($previewType, $eiuEntry);
		
		$this->delegate($previewController);
	}
	
// 	public function doLatestDraft($pid, ParamQuery $refPath) {
// 		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
// 		$eiObject = $this->eiuCtrl->lookupEiObject($pid);
// 		$drafts = $this->eiuCtrl->frame()->toEiuEntry($eiObject)->lookupDrafts(0, 1);
// 		$draft = ArrayUtils::first($drafts);
// 		if ($draft === null || $draft->isPublished()) {
// 			$this->redirectToController(array('newdraft', $pid), array('refPath' => $refPath));
// 			return;
// 		}
		
// 		$this->redirectToController(array('newdraft', $pid), array('refPath' => $refPath));
// 	}
		
// 	public function doNewDraft($pid, ParamQuery $refPath) {
// 		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
// 		$eiEntry = $this->eiuCtrl->lookupEiEntry($pid);
// 		$entryEiuFrame = $this->eiuCtrl->toEiuEntry($eiEntry);
		
// 		$eiUtils = $this->eiuCtrl->frame();
// 		$draftEiObject = $eiUtils->createEiObjectFromDraft(
// 				$eiUtils->createNewDraftFromEiEntityObj($eiEntry->getEiObject()->getEiEntityObj()));
// 		$draftEiEntry = $this->eiuCtrl->frame()->createEiEntryCopy($eiEntry, $draftEiObject);
		
// 		$editModel = new EditModel($this->eiuCtrl->frame(), true, true);
// 		$editModel->initialize($draftEiEntry);
		
// 		if ($this->dispatch($editModel, 'save')) {
// 			$this->redirect($redirectUrl);
// 			return;
// 		}
		
// 		$this->eiuCtrl->applyCommonBreadcrumbs($eiEntry->getEiObject(),
// 				$this->dtc->translate('ei_impl_edit_new_draft_breadcrumb'));
		
// 		$this->forward('..\view\edit.html', array('editModel' => $editModel,
// 				'entryCommandViewModel' => $this->createEntryCommandViewModel($editModel->getEntryModel()
// 						->getEntryGuiModel(), $redirectUrl)));
// 	}
	
// 	public function doDraft($draftId, ParamQuery $refPath) {
// 		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
// 		$eiEntry = $this->eiuCtrl->lookupEiEntryByDraftId($draftId);
// 		$entryEiuFrame = $this->eiuCtrl->toEiuEntry($eiEntry);
// 		if ($entryEiuFrame->getDraft()->isPublished()) {
// 			$eiObject = $entryEiuFrame->getEiuFrame()->createNewEiObject(true, $entryEiuFrame->getEiType());
// 			$eiEntry = $this->eiuCtrl->frame()->createEiEntryCopy($eiEntry, $eiObject);
// 		}
		
// 		$editModel = new EditModel($this->eiuCtrl->frame(), true, true);
// 		$editModel->initialize($eiEntry);
	
// 		if ($this->dispatch($editModel, 'save')) {
// 			$this->redirect($redirectUrl);
// 			return;
// 		}

// 		$this->eiuCtrl->applyCommonBreadcrumbs($eiEntry->getEiObject(), 
// 				$this->dtc->translate('ei_impl_edit_draft_breadcrumb'));
	
// 		$this->forward('..\view\edit.html', array('editModel' => $editModel,
// 				'entryCommandViewModel' => $this->createEntryCommandViewModel($editModel->getEntryModel()
// 						->getEntryGuiModel(), $redirectUrl)));
// 	}
	
// 	public function doPublish($draftId, ParamQuery $refPath) {
// 		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
		
// 		$draftEiEntry = $this->eiuCtrl->lookupEiEntryByDraftId($draftId);
		
// 		$eiUtils = $this->eiuCtrl->frame();
// 		$eiObject = $eiUtils->createEiObjectFromEiEntityObj($draftEiEntry->getEiObject()->getEiEntityObj());
// 		$eiEntry = $eiUtils->createEiEntryCopy($draftEiEntry, $eiObject);
		
// 		if ($eiEntry->save()) {
// 			$eiUtils->persist($eiObject);
// 			$draft = $draftEiEntry->getEiObject()->getDraft();
// 			$draft->setType(Draft::TYPE_PUBLISHED);
// 			$eiUtils->persist($draft);
			
// 			$this->redirect($this->eiuCtrl->buildRedirectUrl($eiEntry->getEiObject()));
// 			return;
// 		}
		
// 		$editModel = new EditModel($this->eiuCtrl->frame(), true, true);
// 		$editModel->initialize($eiEntry);
		
// 		if ($this->dispatch($editModel, 'save')) {
// 			$this->redirect($redirectUrl);
// 			return;
// 		}
		
// 		$this->eiuCtrl->applyCommonBreadcrumbs($draftEiEntry->getEiObject(),
// 				$this->dtc->translate('ei_impl_publish_entry_breadcrumb'));
		
// 		$this->forward('..\view\edit.html', array('editModel' => $editModel,
// 				'entryCommandViewModel' => $this->createEntryCommandViewModel($editModel->getEntryModel()
// 						->getEntryGuiModel(), $redirectUrl)));
// 	}
	
// 	public function doPreview($pid, $previewType = null, ParamGet $refPath = null) {
// 		$redirectUrl = $this->buildRedirectUrl($refPath);
		
// 		$eiEntry = $this->controllingUtils->lookupEiEntry($pid, true, $draftId);
// 		$entryManager = $this->utils->createEntryManager($eiEntry);
// 		$eiuEntryForm = $this->utils->createEiuEntryForm($eiEntry);
		
// 		$this->utils->lookupEditablePreivewEi		
// 		$previewController = $this->utils->createPreviewController($editModel->getEiuEntryForm(), $this->getRequest(), 
// 				$this->getResponse(), $previewType, $editModel);
// 		$currentPreviewType = $previewController->getPreviewType();
				
// 		$this->applyBreadcrumbs($eiFrame);
		
// 		$this->forward('ei\component\command\impl\common\view\editPreview.html', array('commandEditEntryModel' => $editModel,
// 				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext(),
// 						array('previewsrc', $currentPreviewType, $id, $httpN2nLocaleId)),
// 				'entryViewInfo' => new EntryViewInfo($editModel, $editModel->getEiuEntryForm(), $previewController)));
// 	}
	
// 	public function doPreviewSrc(array $contextCmds, 
// 			array $cmds, $previewType, $id, $httpN2nLocaleId = null) {
// 		$editModel = $this->utils->createEditModel($id, $httpN2nLocaleId, true, $this->editCommand);

// 		$previewController = $this->utils->createPreviewController($editModel->getEiuEntryForm(), $this->getRequest(), 
// 				$this->getResponse(), $previewType, $editModel);

// 		if (null != ($redirectUrl = $this->dispatchEditModel($editModel, false, true))) {
// 			$previewController->getPreviewModel()->setRedirectUrl($redirectUrl);
// 		}
		
// 		$previewController->execute(array(), array_merge($contextCmds, $cmds), $this->getN2nContext());
// 	}
	
// 	public function doDraftPreview($previewType, $id, $draftId, $httpN2nLocaleId = null) {
// 		$eiFrame = $this->utils->getEiFrame();
// 		$editModel = $this->utils->createDraftEditModel($id, $draftId, $httpN2nLocaleId, $this->editCommand);
// 		$previewController = $this->utils->createPreviewController($editModel->getEiuEntryForm(), $this->getRequest(), 
// 				$this->getResponse(), $previewType);
// 		$currentPreviewType = $previewController->getPreviewType();
		
// 		$this->applyBreadcrumbs($editModel);
		
// 		$this->forward('ei\component\command\impl\common\view\editPreview.html', array('commandEditEntryModel' => $editModel, 
// 				'iframeSrc' => $this->getHttpContext()->getControllerContextPath($this->getControllerContext(), 
// 						array('draftpreviewsrc', $currentPreviewType, $id, $draftId, $httpN2nLocaleId)),
// 				'entryViewInfo' => new EntryViewInfo($editModel, $editModel->getEiuEntryForm(), $previewController)));
// 	}
	
// 	public function doDraftPreviewSrc(array $contextCmds, array $cmds, $previewType, $id, $draftId, $httpN2nLocaleId = null) {
// 		$editModel = $this->utils->createDraftEditModel($id, $draftId, $httpN2nLocaleId, $this->editCommand);
// 		$previewController = $this->utils->createPreviewController($editModel->getEiuEntryForm(), 
// 				$this->getRequest(), $this->getResponse(), $previewType, $editModel);
	
// 		if (null != ($redirectUrl = $this->dispatchEditModel($editModel, true, true, $this->editCommand))) {
// 			$previewController->getPreviewModel()->setRedirectUrl($redirectUrl);
// 		}
		
// 		$previewController->execute(array(), array_merge($contextCmds, $cmds), $this->getN2nContext());
// 	}
	
// 	private function applyBreadcrumbs(EiObject $eiObject) {
// 		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
// 		$httpContext = $this->getHttpContext();
				
// 		if (!$eiFrame->isOverviewDisabled()) {
// 			$this->rocketState->addBreadcrumb(
// 					$eiFrame->createOverviewBreadcrumb($this->getHttpContext()));
// 		}
		
// 		$this->rocketState->addBreadcrumb($eiFrame->createDetailBreadcrumb($httpContext, $eiObject));
		
// 		if ($eiObject->isDraft()) {	
// 			$breadcrumbPath = $eiFrame->getDetailUrl($httpContext, $eiObject->toEntryNavPoint($eiFrame->getContextEiEngine()->getEiMask()->getEiType())
// 							->copy(false, true));
// 			$dtf = DateTimeFormat::createDateTimeInstance($this->getRequest()->getN2nLocale(),
// 					DateTimeFormat::STYLE_MEDIUM, DateTimeFormat::STYLE_SHORT);
// 			$breadcrumbLabel = $this->dtc->translate('ei_impl_detail_draft_breadcrumb', 
// 					array('last_mod' => $dtf->format($eiObject->getDraft()->getLastMod())));
// 			$this->rocketState->addBreadcrumb(new Breadcrumb($breadcrumbPath, $breadcrumbLabel));
// 		}
		
// 	}	
	
// 	private function dispatchEditModel(EditModel $editModel) {
// 		$eiFrame = $this->utils->getEiFrame();
// 		$entryNavPoint = null;
		
// 		$dispReturn = $this->dispatch($editModel, 'save');
// 		$eiObject = $editModel->getEntryModel()->getEiEntry()->getEiObject();
// 		if ($dispReturn instanceof Draft) {
// 			$entryNavPoint = $eiObject->toEntryNavPoint($eiFrame->getContextEiEngine()->getEiMask()->getEiType());
// 		} else if ($dispReturn) {
// 			$entryNavPoint = $eiObject->toEntryNavPoint($eiFrame->getContextEiEngine()->getEiMask()->getEiType())->copy(true);
// 		} else {
// 			return null;
// 		}
		
// 		return $eiFrame->getDetailUrl($this->getRequest(), $entryNavPoint);
// 	}
}

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

use rocket\core\model\RocketState;
use n2n\l10n\DynamicTextCollection;
use n2n\web\http\controller\ControllerAdapter;
use rocket\impl\ei\component\command\common\model\AddModel;
use rocket\impl\ei\component\command\common\model\EntryCommandViewModel;
use n2n\web\http\controller\ParamGet;
use rocket\ei\util\EiuCtrl;
use rocket\si\control\SiIconType;
use rocket\si\control\SiButton;
use rocket\ei\util\Eiu;
use rocket\core\model\Rocket;
use n2n\util\ex\IllegalStateException;

class AddController extends ControllerAdapter {
	const CONTROL_SAVE_KEY = 'save';
	const CONTROL_CANCEL_KEY = 'canel';
	
	private $dtc;
	private $eiuCtrl;
	
	private $parentEiuObject;
	private $beforeEiuObject;
	private $afterEiuObject;
	
	public function prepare(DynamicTextCollection $dtc, RocketState $rocketState) {
		$this->dtc = $dtc;
		$this->eiuCtrl = EiuCtrl::from($this->cu());
	}
		
	public function index($copyPid = null, ParamGet $refPath = null) {	
		$this->live($copyPid);
	}
	
	public function doChild($parentPid, $copyPid = null, ParamGet $refPath = null) {
		$this->parentEiuObject = $this->eiuCtrl->lookupObject($parentPid);	
		$this->live($copyPid);	
	}
	
	public function doBefore($beforePid, $copyPid = null, ParamGet $refPath = null) {
		$this->beforeEiuObject = $this->eiuCtrl->lookupObject($beforePid);	
		$this->live($copyPid);
	}
	
	public function doAfter($afterPid, $copyPid = null, ParamGet $refPath = null) {
		$this->afterEiuObject = $this->eiuCtrl->lookupObject($afterPid);	
		$this->live($copyPid);
	}
	
	private function live($copyPid = null) {

		$this->eiuCtrl->pushOverviewBreadcrumb()
				->pushCurrentAsSirefBreadcrumb($this->dtc->t('common_add_label'));
		
		$this->eiuCtrl->forwardNewBulkyEntryZone(true, true, true, $this->createControls());
	}
	
	private function createControls() {
		$eiuControlFactory = $this->eiuCtrl->eiu()->factory()->controls();
		$dtc = $this->eiuCtrl->eiu()->dtc(Rocket::NS);
		
		return [
				$eiuControlFactory->newCallback(self::CONTROL_SAVE_KEY,
								SiButton::primary($dtc->t('common_save_label'), SiIconType::ICON_SAVE),
								function (Eiu $eiu, array $inputEius) {
									$this->handleInput($eiu, $inputEius);
									return $eiu->factory()->newControlResponse()
											->redirectBack()
											->highlight(...array_map(function ($eiu) { return $eiu->entry(); }, $inputEius));
								})
						->setInputHandled(true),
				$eiuControlFactory->newCallback(self::CONTROL_CANCEL_KEY,
						SiButton::primary($dtc->t('common_cancel_label'), SiIconType::ICON_ARROW_LEFT),
						function (Eiu $eiu) {
							return $eiu->factory()->newControlResponse()->redirectBack();
						})
		];
	}
	
	private function handleInput(Eiu $eiu, array $inputEius) {
		foreach ($inputEius as $inputEiu) {
			$result = false;
			
			if ($this->parentEiuObject !== null) {
				$result = $inputEiu->entry()->insertAsChild($this->parentEiuObject);
			} else if ($this->beforeEiuObject !== null) {
				$result = $inputEiu->entry()->insertBefore($this->beforeEiuObject);
			} else if ($this->afterEiuObject !== null) {
				$result = $inputEiu->entry()->insertAfter($this->afterEiuObject);
			} else {
				$result = $inputEiu->entry()->save();
			}
			
			IllegalStateException::assertTrue($result);
		}		
	}
	
	public function doDraft(ParamGet $refPath = null) {
		$redirectUrl = $this->eiuCtrl->parseRefUrl($refPath);
			
		$eiuEntryForm = $this->eiuCtrl->frame()->newEntryForm(true);
		
		$eiFrame = $this->eiuCtrl->frame()->getEiFrame();
		$addModel = new AddModel($eiFrame, $eiuEntryForm);
		
		if (is_object($eiObject = $this->dispatch($addModel, 'create'))) {
			$this->redirect($this->eiuCtrl->buildRefRedirectUrl($redirectUrl, $eiObject));
			return;
		}
		
		$viewModel = new EntryCommandViewModel($this->eiuCtrl->frame(), null, $redirectUrl);
		$viewModel->setTitle($this->dtc->translate('ei_impl_add_draft_title', 
				array('type' => $this->eiuCtrl->frame()->getGenericLabel())));
		$this->forward('..\view\add.html', array('addModel' => $addModel, 'entryViewInfo' => $viewModel));
	}
	
	private function getBreadcrumbLabel() {
		$eiFrameUtils = $this->eiuCtrl->frame();
		
		if (null === $eiFrameUtils->getNestedSetStrategy()) {
			return $this->dtc->translate('ei_impl_add_breadcrumb');
		} else if ($this->parentEiuObject !== null) {
			return $this->dtc->translate('ei_impl_add_child_branch_breadcrumb',
					array('parent_branch' => $eiFrameUtils->createIdentityString($this->parentEiuObject)));
		} else if ($this->beforeEiuObject !== null) {
			return$this->dtc->translate('ei_impl_add_before_branch_breadcrumb',
					array('branch' => $eiFrameUtils->createIdentityString($this->beforeEiuObject)));
		} else if ($this->afterEiuObject !== null) {
			return $this->dtc->translate('ei_impl_add_after_branch_breadcrumb',
					array('branch' => $eiFrameUtils->createIdentityString($this->afterEiuObject)));
		} else {
			return $this->dtc->translate('ei_impl_add_root_branch_breadcrumb');
		}
	}
}

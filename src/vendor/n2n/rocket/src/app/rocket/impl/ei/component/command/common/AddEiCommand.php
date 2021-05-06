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
namespace rocket\impl\ei\component\command\common;

use n2n\l10n\DynamicTextCollection;
use n2n\l10n\N2nLocale;
use rocket\si\control\SiButton;
use rocket\si\control\SiIconType;
use rocket\impl\ei\component\command\adapter\IndependentEiCommandAdapter;
use rocket\ei\component\command\PrivilegedEiCommand;
use n2n\core\container\N2nContext;
use rocket\core\model\Rocket;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use rocket\impl\ei\component\command\common\controller\AddController;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\ex\IllegalStateException;
use n2n\impl\web\dispatch\mag\model\MagForm;
use rocket\ei\component\EiSetup;
use n2n\util\type\CastUtils;
use rocket\impl\ei\component\config\EiConfiguratorAdapter;
use rocket\ei\component\EiConfigurator;

class AddEiCommand extends IndependentEiCommandAdapter implements PrivilegedEiCommand {
	const ID_BASE = 'add';
	const CONTROL_ADD_KEY = 'add';
	const CONTROL_DUPLICATE_KEY = 'duplicate';
	const CONTROL_ADD_ROOT_BRANCH_KEY = 'addRootBranch';
	const CONTROL_INSERT_BRANCH_KEY = 'insertBranch';
	const CONTROL_INSERT_BEFORE_KEY = 'insertBefore';
	const CONTROL_INSERT_AFTER_KEY = 'insertAfter';
	const CONTROL_INSERT_CHILD_KEY = 'insertChild';
	const CONTROL_SAVE_KEY = 'save';

	const PRIVILEGE_LIVE_ENTRY_KEY = 'eiEntityObj';
	const PRIVILEGE_DRAFT_KEY = 'draft';

	private $dublicatingAllowed = true;
	
	protected function prepare() {
	}

	public function getIdBase(): ?string {
		return self::ID_BASE;
	}

	public function isDublicatingAllowed() {
		return $this->dublicatingAllowed;
	}

	public function setDublicatingAllowed(bool $dublicatingAllowed) {
		$this->dublicatingAllowed = $dublicatingAllowed;
	}

	public function getPrivilegeLabel(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return $dtc->translate('common_new_entry_label');
	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\component\command\PrivilegedEiCommand::createEiCommandPrivilege()
// 	 */
// 	public function createEiCommandPrivilege(Eiu $eiu): EiCommandPrivilege {
// 		$dtc = $eiu->dtc(Rocket::NS);
// 		$eiuCommandPrivilege = $eiu->factory()->newCommandPrivilege($dtc->t('common_new_entry_label'));
		
// 		$eiuCommandPrivilege->newSub(self::PRIVILEGE_LIVE_ENTRY_KEY, $dtc->t('ei_impl_add_live_entry_label'));
// 		$eiuCommandPrivilege->newSub(self::PRIVILEGE_DRAFT_KEY, $dtc->t('ei_impl_add_draft_label'));
		
// 		return $eiuCommandPrivilege;
		
// // 		$pi = new CommonEiCommandPrivilege(Rocket::createLstr('common_new_entry_label', Rocket::NS));
// // 		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_LIVE_ENTRY_KEY,
// // 				new CommonEiCommandPrivilege(Rocket::createLstr('ei_impl_add_live_entry_label', Rocket::NS)));
// // 		$pi->putSubEiCommandPrivilege(self::PRIVILEGE_DRAFT_KEY,
// // 				new CommonEiCommandPrivilege(Rocket::createLstr('ei_impl_add_draft_label', Rocket::NS)));
// // 		return $pi;
// 	}

	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(AddController::class);
	}

// 	public function getOverallControlOptions(N2nLocale $n2nLocale): array {
// 		$dtc = new DynamicTextCollection('rocket', $n2nLocale);

// 		$options = array();
		
// 		if (null === $this->eiMask->getEiType()->getNestedSetStrategy()) {
// 			$options[self::CONTROL_ADD_KEY] = $dtc->t('common_new_entry_label');
// 		} else {
// 			$options[self::CONTROL_ADD_ROOT_BRANCH_KEY] = $dtc->t('ei_impl_add_root_branch_label');
// 		}

// 		$options[self::CONTROL_ADD_DRAFT_KEY] = $dtc->translate('common_add_draft_label');
		
// 		return $options;
// 	}
	
	public function createGeneralGuiControls(Eiu $eiu): array {
		if ($eiu->frame()->isExecutedBy($this)) {
			return [];
		}
		
		return  [$this->createAddControl($eiu)];
	}
	
	/**
	 * @param Eiu $eiu
	 * @return \rocket\ei\util\control\EiuRefGuiControl
	 */
	private function createAddControl(Eiu $eiu) {
		$eiuControlFactory = $eiu->factory()->controls();
		$dtc = $eiu->dtc(Rocket::NS);
		
		$nestedSet = null !== $this->getWrapper()->getEiCommandCollection()->getEiMask()->getEiType()->getNestedSetStrategy();
		
		$key = $nestedSet ? self::CONTROL_ADD_ROOT_BRANCH_KEY : self::CONTROL_ADD_KEY;
		$siButton = SiButton::success($dtc->t($nestedSet ? 'ei_impl_add_root_branch_label' : 'common_new_entry_label'), SiIconType::ICON_PLUS_CIRCLE)
				->setImportant(true);
		return $eiuControlFactory->newCmdRef($key, $siButton);
	}
	
// 	/**
// 	 * @param Eiu $eiu
// 	 * @return \rocket\ei\util\control\EiuCallbackGuiControl
// 	 */
// 	private function createSaveControl(Eiu $eiu) {
// 		$eiuControlFactory = $eiu->factory()->controls();
// 		$dtc = $eiu->dtc(Rocket::NS);
		
// 		$siButton = SiButton::primary($dtc->t('common_save_label'), SiIconType::ICON_SAVE);
// 		$callback = function (Eiu $eiu, $inputEius) {
// 			$eiuResponse = $eiu->factory()->newControlResponse()->redirectBack();
			
// 			foreach ($inputEius as $inputEiu) {
// 				$inputEiu->entry()->save();
// 				$eiuResponse->highlight($inputEiu->entry());
// 			}
			
// 			return $eiuResponse; 
// 		};
		
// 		return $eiuControlFactory->newCallback(self::CONTROL_SAVE_KEY, $siButton, $callback)->setInputHandled(true);
// 	}

	public function getEntryGuiControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		
		return array(self::CONTROL_INSERT_BRANCH_KEY => $dtc->t('ei_impl_insert_branch_label'));
	}

	public function createEntryGuiControls(Eiu $eiu): array {
		$eiuEntry = $eiu->entry();
		
		if ($eiuEntry->isNew() || $eiu->frame()->isExecutedBy($this)) {
			return array();
		}
		
		$eiuControlFactory = $eiu->factory()->controls();
		$dtc = $eiu->dtc(Rocket::NS);
		
		if ($eiu->frame()->getNestedSetStrategy() === null) {
			if (!$this->dublicatingAllowed) return array();
			
			$name = $dtc->t('ei_impl_duplicate_label');
			$tooltip = $dtc->t('ei_impl_duplicate_tooltip', array('entry' => $eiuEntry->createIdentityString()));
			$siButton = SiButton::success($name,SiIconType::ICON_R_COPY)->setTooltip($tooltip);
			
			return array($eiuControlFactory
					->newCmdRef(self::CONTROL_DUPLICATE_KEY , $siButton, [$eiuEntry->getPid()]));
		}


		$groupControl = $eiuControlFactory->newGroup(self::CONTROL_INSERT_BRANCH_KEY,
				SiButton::success($dtc->t('ei_impl_insert_branch_label'), SiIconType::ICON_PLUS)
						->setTooltip($dtc->t('ei_impl_add_branch_tooltip'))
						->setImportant(false));
		
		$groupControl->add(
				$eiuControlFactory->newCmdRef(self::CONTROL_INSERT_BEFORE_KEY,
						SiButton::success($dtc->t('ei_impl_add_before_branch_label'), SiIconType::ICON_ANGLE_UP)
								->setTooltip($dtc->t('ei_impl_add_before_branch_tooltip'))
								->setImportant(TRUE),
						['before', $eiuEntry->getPid()]),
				$eiuControlFactory->newCmdRef(self::CONTROL_INSERT_AFTER_KEY,
						SiButton::success($dtc->t('ei_impl_add_after_branch_label'), SiIconType::ICON_ANGLE_DOWN)
								->setTooltip($dtc->t('ei_impl_add_after_branch_tooltip'))
								->setImportant(true),
						['after', $eiuEntry->getPid()]),
				$eiuControlFactory->newCmdRef(self::CONTROL_INSERT_CHILD_KEY,
						SiButton::success($dtc->translate('ei_impl_add_child_branch_label'), SiIconType::ICON_ANGLE_RIGHT)
								->setTooltip($dtc->translate('ei_impl_add_child_branch_tooltip'))
								->setImportant(true),
						['child', $eiuEntry->getPid()]));
		
		return [$groupControl];
	}
	
	public function createEiConfigurator(): EiConfigurator {
		return new AddEiConfigurator($this);
	}
}
	

class AddEiConfigurator extends EiConfiguratorAdapter {
	const OPTION_DUPLICATE_ALLOWED_KEY = 'duplicateAllowed';
	
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		$eiComponent = $this->eiComponent;
		IllegalStateException::assertTrue($eiComponent instanceof AddEiCommand);
		
		$magCollection = new MagCollection();
		$magCollection->addMag(self::OPTION_DUPLICATE_ALLOWED_KEY, new BoolMag('Duplicating Allowed', 
				$this->getDataSet()->get(
						self::OPTION_DUPLICATE_ALLOWED_KEY, false, $eiComponent->isDublicatingAllowed())));
		return new MagForm($magCollection);
	}
	
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		$this->dataSet->set(self::OPTION_DUPLICATE_ALLOWED_KEY, $magDispatchable->getPropertyValue(self::OPTION_DUPLICATE_ALLOWED_KEY));
	}
	
	public function setup(EiSetup $eiSetupProcess) {
		$eiComponent = $this->eiComponent;
		CastUtils::assertTrue($eiComponent instanceof AddEiCommand);
		
		$eiComponent->setDublicatingAllowed($this->dataSet->optBool(self::OPTION_DUPLICATE_ALLOWED_KEY,
				$eiComponent->isDublicatingAllowed()));
	}
}


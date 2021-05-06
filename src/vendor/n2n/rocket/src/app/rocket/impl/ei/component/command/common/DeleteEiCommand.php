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

use rocket\ei\component\command\PrivilegedEiCommand;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\command\adapter\IndependentEiCommandAdapter;
use rocket\si\control\SiButton;
use rocket\si\control\SiConfirm;
use rocket\si\control\SiIconType;

class DeleteEiCommand extends IndependentEiCommandAdapter implements PrivilegedEiCommand {
	const ID_BASE = 'delete';
	const CONTROL_BUTTON_KEY = 'delete'; 
	const PRIVILEGE_LIVE_ENTRY_KEY = 'eiEntityObj';
	const PRIVILEGE_DRAFT_KEY = 'draft';
	
	protected function prepare() {
	}
	
	public function getIdBase(): ?string {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Delete';
	}
	
	public function createEntryGuiControls(Eiu $eiu): array {
		$eiuEntry = $eiu->entry();
		
		if ($eiuEntry->isNew() || $eiuEntry->isDraft()) {
			return [];
		}
		
		$eiuFrame = $eiu->frame();
		$dtc = $eiu->dtc('rocket');
		
		$identityString = $eiuEntry->createIdentityString();
		$name = $dtc->t('common_delete_label');
		$tooltip = $dtc->t('ei_impl_delete_entry_tooltip', ['entry' => $eiuFrame->getGenericLabel()]);
		$confirmMessage = $dtc->t('ei_impl_delete_entry_confirm', array('entry' => $identityString));
		
		$siButton = SiButton::danger($name, SiIconType::ICON_TRASH_ALT)->setTooltip($tooltip)
				->setConfirm(new SiConfirm($confirmMessage, $dtc->t('common_yes_label'), $dtc->t('common_no_label'), true));
		
		$eiuControlFactory = $eiu->factory()->controls();
		$control = $eiuControlFactory->newCallback(self::CONTROL_BUTTON_KEY, $siButton, function (Eiu $eiu) {
			$eiu->entry()->remove();
		});
		
		return [self::CONTROL_BUTTON_KEY => $control];
	}
	
// 	public function createEntryGuiControls(Eiu $eiu): array {
// 		$eiuEntry = $eiu->entry();
// 		$eiuFrame = $eiu->frame();
		
// 		$pathExt = null;
// 		$name = null;
// 		$tooltip = null;
// 		$confirmMessage = null;
// 		$iconType = null;
// 		if ($eiuEntry->isDraft()) {
// 			$draft = $eiuEntry->getDraft();
// 			$pathExt = new Path(array('draft', $draft->getId()));
// 			$name = $view->getL10nText('ei_impl_delete_draft_label');
// 			$tooltip = $view->getL10nText('ei_impl_delete_draft_tooltip', 
// 					array('last_mod' => $view->getL10nDateTime($draft->getLastMod())));
// 			$confirmMessage = $view->getL10nText('ei_impl_delete_draft_confirm_message', 
// 					array('last_mod' => $view->getL10nDateTime($draft->getLastMod())));
// 			$iconType = SiIconType::ICON_TIMES_CIRCLE;
// 		} else {
// 			$pathExt = new Path(array('live', $eiuEntry->getPid()));
// 			$identityString = $eiuEntry->createIdentityString();
// 			$name = $view->getL10nText('common_delete_label');
// 			$tooltip = $view->getL10nText('ei_impl_delete_entry_tooltip', 
// 					array('entry' => $eiuFrame->getGenericLabel()));
// 			$confirmMessage = $view->getL10nText('ei_impl_delete_entry_confirm', array('entry' => $identityString));
// 			$iconType = SiIconType::ICON_TRASH_O;
// 		}
		
// 		$siButton = new SiButton($name, $tooltip, false, SiButton::TYPE_DANGER, $iconType);
// 		$siButton->setConfirmMessage($confirmMessage);
// 		$siButton->setConfirmOkButtonLabel($view->getL10nText('common_yes_label'));
// 		$siButton->setConfirmCancelButtonLabel($view->getL10nText('common_no_label'));
// 		$siButton->setAttrs(array('class' => 'rocket-impl-remove'));
		
// 		$query = array();
// 		if ($eiu->guiFrame()->isCompact()) {
// 			$query['refPath'] = (string) $eiuFrame->getEiFrame()->getCurrentUrl($view->getHttpContext());
// 		}
		
// 		$hrefControl = $eiu->factory()->controls()->createJhtml($siButton, $pathExt->toUrl($query))
// 		      ->setPushToHistory(false)->setForceReload(true);
		
// 		return array(self::CONTROL_BUTTON_KEY => $hrefControl);
// 	}
	
// 	public function getEntryGuiControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
// 		$dtc = new DynamicTextCollection('rocket');
		
// 		return array(self::CONTROL_BUTTON_KEY => $dtc->translate('ei_impl_delete_draft_label'));
// 	}
	
// 	public function createPartialControlButtons(EiFrame $eiFrame, HtmlView $htmlView) {
// 		$dtc = new DynamicTextCollection('rocket', $htmlView->getN2nContext()->getN2nLocale());
// 		$eiCommandButton = new SiButton(null, $dtc->translate('ei_impl_partial_delete_label'), 
// 				$dtc->translate('ei_impl_partial_delete_tooltip'), false, SiButton::TYPE_SECONDARY,
// 				SiIconType::ICON_TIMES_SIGN);
// 		$eiCommandButton->setConfirmMessage($dtc->translate('ei_impl_partial_delete_confirm_message'));
// 		$eiCommandButton->setConfirmOkButtonLabel($dtc->translate('common_yes_label'));
// 		$eiCommandButton->setConfirmCancelButtonLabel($dtc->translate('common_no_label'));
		
// 		return array(self::CONTROL_BUTTON_KEY => $eiCommandButton);
// 	}
	
// 	public function getPartialControlOptions(N2nLocale $n2nLocale) {
// 		$dtc = new DynamicTextCollection('rocket');
		
// 		return array(self::CONTROL_BUTTON_KEY => $dtc->translate('ei_impl_partial_delete_label'));
// 	}
	
// 	public function processEntries(EiFrame $eiFrame, array $entries) {
// 		$spec = N2N::getModelContext()->lookup('rocket\spec\Spec');
// 		$eiType = $this->getEiType();
// 		$em = $eiFrame->getEntityManager();
		
// 		foreach ($entries as $entry) {
// // 			$spec->notifyOnDelete($entry);
// 			$em->remove($entry);
// // 			$spec->notifyDelete($entry);
// 		}
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\component\command\PrivilegedEiCommand::createEiCommandPrivilege()
// 	 */
// 	public function createEiCommandPrivilege(Eiu $eiu): EiCommandPrivilege {
// 		$dtc = $eiu->dtc(Rocket::NS);
		
// 		$ecp = $eiu->factory()->newCommandPrivilege($dtc->t('common_delete_label'));
// 		$ecp->newSub(self::PRIVILEGE_LIVE_ENTRY_KEY, $dtc->t('ei_impl_delete_live_entry_label'));
// 		$ecp->newSub(self::PRIVILEGE_DRAFT_KEY, $dtc->t('ei_impl_delete_draft_label'));
		
// 		return $ecp;
// 	}
	
// 	public static function createPathExt($entityId, $draftId = null) {
// 		if (isset($draftId)) {
// 			return self::createHistoryPathExt($draftId);
// 		}
	
// 		return new Path(array($this->getId(), $entityId));
// 	}
	
// 	public static function createHistoryPathExt($draftId) {
// 		return new Path(array($this->getId(), 'draft', $draftId));
// 	}
}

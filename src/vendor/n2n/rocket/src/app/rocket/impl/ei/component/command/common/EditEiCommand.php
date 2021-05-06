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
use rocket\impl\ei\component\command\common\controller\EditController;
use rocket\impl\ei\component\command\adapter\IndependentEiCommandAdapter;
use rocket\ei\component\command\PrivilegedEiCommand;
use n2n\core\container\N2nContext;
use rocket\core\model\Rocket;
use n2n\util\uri\Path;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use n2n\util\ex\IllegalStateException;

class EditEiCommand extends IndependentEiCommandAdapter implements PrivilegedEiCommand {
	const ID_BASE = 'edit';
	const CONTROL_EDIT_KEY = 'edit';
	
	protected function prepare() {
	}
	
	public function getIdBase(): ?string {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Edit';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\command\EiCommand::lookupController()
	 */
	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(EditController::class);
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @param N2nLocale $n2nLocale
	 * @return string[]
	 */
	public function getEntryGuiControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_EDIT_KEY => $dtc->translate('common_edit_label'));
	}
	
	public function createEntryGuiControls(Eiu $eiu): array {
		$eiuEntry = $eiu->entry();
		
		if ($eiuEntry->isNew() || $eiu->frame()->isExecutedBy($this)) {
			return array();
		}
		
		if ($eiuEntry->isDraft()) {
			return [];
		}
		
		$eiuControlFactory = $eiu->factory()->controls();
		$dtc = $eiu->dtc(Rocket::NS);
			
		$siButton = SiButton::warning($dtc->t('common_edit_label'), SiIconType::ICON_PENCIL_ALT)
				->setTooltip($dtc->t('ei_impl_edit_entry_tooltip', array('entry' => $eiuEntry->getGenericLabel())));
		
			
		return [$eiuControlFactory->newCmdRef(self::CONTROL_EDIT_KEY, $siButton, 
				new Path([$eiuEntry->getPid()]))];
	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\component\command\PrivilegedEiCommand::createEiCommandPrivilege()
// 	 */
// 	public function createEiCommandPrivilege(Eiu $eiu): EiCommandPrivilege {
// 		$dtc = $eiu->dtc(Rocket::NS);
		
// 		$ecp = $eiu->factory()->newCommandPrivilege($dtc->t('common_edit_label'));
// 		$ecp->newSub(self::PRIVILEGE_LIVE_ENTRY_KEY, $dtc->t('ei_impl_edit_live_entry_label'));
// 		$ecp->newSub(self::PRIVILEGE_DRAFT_KEY, $dtc->t('ei_impl_edit_draft_label'));
		
// 		return $ecp;
// 	}
	
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getPrivilegeLabel(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return $dtc->translate('common_edit_label'); 
	}
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return array|string[]
	 */
	public function getPrivilegeExtOptions(N2nLocale $n2nLocale) {
		if (!$this->getEiType()->isDraftable()) return array();
		
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::PRIVILEGE_EXT_PUBLISH => $dtc->translate('ei_impl_edit_privilege_publish_label'));
	}
}

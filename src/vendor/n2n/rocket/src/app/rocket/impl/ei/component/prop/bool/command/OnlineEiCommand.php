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
namespace rocket\impl\ei\component\prop\bool\command;

use rocket\impl\ei\component\prop\bool\OnlineEiProp;
use n2n\l10n\DynamicTextCollection;
use n2n\l10n\N2nLocale;
use rocket\si\control\SiIconType;
use rocket\si\control\SiButton;
use rocket\impl\ei\component\command\adapter\EiCommandAdapter;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use n2n\core\container\N2nContext;

class OnlineEiCommand extends EiCommandAdapter {
	const CONTROL_KEY = 'online';
	const ID_BASE = 'online-status';
	
	private $onlineEiProp;
	
	public function getIdBase(): ?string {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Online Status';
	}
	
	public function setOnlineEiProp(OnlineEiProp $onlineEiProp) {
		$this->onlineEiProp = $onlineEiProp;
	}
		
	public function lookupController(Eiu $eiu): Controller {
		$controller = $eiu->lookup(OnlineController::class);
		$controller->setOnlineEiProp($this->onlineEiProp);
		$controller->setOnlineEiCommand($this);
		return $controller;
	}
	
	public function createEntryGuiControls(Eiu $eiu): array {
		$eiuEntry = $eiu->entry();

		if ($eiuEntry->isNew()) {
			return [];
		}
		
		$eiuControlFactory = $eiu->factory()->controls();
		
		$eiuFrame = $eiu->frame();
		$dtc = new DynamicTextCollection('rocket', $eiuFrame->getN2nLocale());
		
		$siButton = SiButton::success($dtc->t('ei_impl_online_offline_label'))
				->setTooltip($dtc->t('ei_impl_online_offline_tooltip', array('entry' => $eiuFrame->getGenericLabel())))
				->setIconImportant(true)
				->setIconAlways(true);
		
		$status = $eiuEntry->getValue($this->onlineEiProp);
		if ($status) {
			$siButton->setType(SiButton::TYPE_SUCCESS);
			$siButton->setIconType(SiIconType::ICON_CHECK_CIRCLE);
		} else {
			$siButton->setType(SiButton::TYPE_DANGER);
			$siButton->setIconType(SiIconType::ICON_MINUS_CIRCLE);
		}
		
		$guiControl = $eiuControlFactory->newCallback(self::CONTROL_KEY, $siButton, 
				function () use ($eiu, $eiuEntry, $status, $dtc) {
					$eiuEntry->setValue($this->onlineEiProp, !$status);
					if (!$eiuEntry->save()) {
						return $eiu->factory()->newControlResponse()
								->message($dtc->t('ei_entry_errors_must_first_be_handled_err'));
					}
				});
		
		return [self::CONTROL_KEY => $guiControl];
	}
	
	private function handle() {
		
	}

	public function getEntryGuiControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_KEY => $dtc->translate('ei_impl_online_set_label'));
	}
}

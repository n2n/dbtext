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
use rocket\impl\ei\component\command\common\controller\DetailController;
use rocket\si\control\SiButton;
use rocket\si\control\SiIconType;
use rocket\impl\ei\component\command\adapter\IndependentEiCommandAdapter;
use rocket\ei\component\command\PrivilegedEiCommand;
use n2n\util\uri\Path;
use n2n\core\container\N2nContext;
use rocket\si\control\SiNavPoint;
use rocket\core\model\Rocket;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use rocket\ei\component\command\GenericDetailEiCommand;

class DetailEiCommand extends IndependentEiCommandAdapter implements PrivilegedEiCommand, GenericDetailEiCommand {
	const ID_BASE = 'detail';
	const CONTROL_DETAIL_KEY = 'detail'; 
	const CONTROL_PREVIEW_KEY = 'preview';
	
	protected function prepare() {
	}
		
	public function getIdBase(): ?string {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Detail';
	}
	
	public function lookupController(Eiu $eiu): Controller {
		return $eiu->lookup(DetailController::class);
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\command\control\\GuiControlComponent::getEntryGuiControlOptions()
	 */
	public function getEntryGuiControlOptions(N2nContext $n2nContext, N2nLocale $n2nLocale): array {
		$dtc = new DynamicTextCollection('rocket', $n2nLocale);
		return array(self::CONTROL_DETAIL_KEY => $dtc->translate('ei_impl_detail_label'), 
				self::CONTROL_PREVIEW_KEY => $dtc->translate('ei_impl_preview_label'));
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\command\control\\GuiControlComponent::createEntryGuiControls()
	 */
	public function createEntryGuiControls(Eiu $eiu): array {
		$eiuFrame = $eiu->frame();
		$eiuEntry = $eiu->entry();
		
		if ($eiuEntry->isNew() || $eiuFrame->isExecutedBy($this)) {
			return array();
		}
		
		$pathExt = null;
		$iconType = null;
		if (!$eiuEntry->isDraft()) {
			$pathExt = new Path(array('live', $eiuEntry->getPid()));
			$iconType = SiIconType::ICON_R_FILE;
		} else if (!$eiuEntry->isDraftNew()) {
			$pathExt = new Path(array('draft', $eiuEntry->getDraftId()));
			$iconType = SiIconType::ICON_R_FILE_ALT;
		} else {
			return array();
		}
		
		$dtc = $eiu->dtc(Rocket::NS);
		$eiuControlFactory = $eiu->factory()->controls();
		
		$siButton = SiButton::secondary($dtc->t('ei_impl_detail_label'), $iconType)
				->setTooltip($dtc->t('ei_impl_detail_tooltip', array('entry' => $eiuFrame->getGenericLabel())));
		
		$controls = array($eiuControlFactory->newCmdRef(self::CONTROL_DETAIL_KEY, $siButton, $pathExt->toUrl()));
		
		if (!$eiuEntry->isPreviewSupported()) {
			return $controls;
		}
		
		$siButton = SiButton::success($dtc->t('ei_impl_detail_preview_label'), SiIconType::ICON_R_EYE)
				->setTooltip($dtc->t('ei_impl_detail_preview_tooltip', array('entry' => $eiuFrame->getGenericLabel())));
		
		$previewTypeOptions = $eiuEntry->getPreviewTypeOptions();
		
		if (empty($previewTypeOptions)) {
			$controls[] = $eiuControlFactory->newDeactivated(self::CONTROL_PREVIEW_KEY, $siButton);
			return $controls;
		}
		
		if (count($previewTypeOptions) === 1) {
			$controls[] = $eiuControlFactory->newCmdRef(self::CONTROL_PREVIEW_KEY, $siButton, 
					new Path(['livepreview', $eiuEntry->getPid(), $eiuEntry->getDefaultPreviewType()]));
			return $controls;
		}
		
		$controls[] = $groupControl = $eiuControlFactory->newGroup(self::CONTROL_PREVIEW_KEY, $siButton);
		
		foreach ($previewTypeOptions as $previewType => $label) {
			$groupControl->add($eiuControlFactory->newCmdRef($previewType, 
					SiButton::success($label, SiIconType::ICON_R_EYE),
					new Path(['livepreview', $eiuEntry->getPid(), $previewType])));
		}
		
		return $controls;
	}
	
// 	public function getDetailUrlExt(EntryNavPoint $entryNavPoint) {
// // 		if (!$this->getEiType()->getEiMask()->getisPreviewAvailable()) {
// // 			$entryNavPoint = $entryNavPoint->copy(false, false, true);
// // 		}
		
// 		return PathUtils::createPathExtFromEntryNavPoint($this, $entryNavPoint)->toUrl();
// 	}
	
// 	public function createEiCommandPrivilege(Eiu $eiu): EiCommandPrivilege {
// 		$dtc = $eiu->dtc(Rocket::NS);
// 		return $eiu->factory()->newCommandPrivilege($dtc->t('ei_impl_detail_label'));
// 	}
	
	public function buildDetailNavPoint(Eiu $eiu): ?SiNavPoint {
		return SiNavPoint::siref((new Path(['live', $eiu->object()->getPid()]))->toUrl());
	}

	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\component\command\GenericDetailEiCommand::isDetailAvailable($entryNavPoint)
// 	 */
// 	public function isDetailAvailable(EntryNavPoint $entryNavPoint): bool {
// 		return true;
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\ei\component\command\GenericDetailEiCommand::buildDetailPathExt($entryNavPoint)
// 	 */
// 	public function getDetailPathExt(EntryNavPoint $entryNavPoint): Path {
// 		return PathUtils::createPathExtFromEntryNavPoint($this, $entryNavPoint);
// 	}
}

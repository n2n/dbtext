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
namespace rocket\impl\ei\component\prop\file\command;

use rocket\impl\ei\component\command\adapter\EiCommandAdapter;
use n2n\l10n\N2nLocale;
use n2n\l10n\DynamicTextCollection;
use n2n\impl\web\ui\view\html\HtmlView;
use rocket\si\control\SiButton;
use rocket\si\control\SiIconType;
use rocket\impl\ei\component\prop\file\command\controller\MultiUploadEiController;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\file\conf\FileModel;

class MultiUploadEiCommand extends EiCommandAdapter {
	const MULTI_UPLOAD_KEY = 'multi-upload';
	/**
	 * @var \rocket\impl\ei\component\prop\file\FileEiProp
	 */
	private $fileModel;
	private $namingEiPropPath;
	
	public function __construct(FileModel $fileModel, EiPropPath $namingEiPropPath = null, string $order = null) {
		$this->fileModel = $fileModel;
		$this->namingEiPropPath = $namingEiPropPath;
		$this->order = $order;
	}

	public function lookupController(Eiu $eiu): Controller {
		$controller = new MultiUploadEiController();
		$controller->setFileModel($this->fileModel);
		$controller->setOrder($this->order);
		return $controller;
	}
	
	public function getOverallControlOptions(N2nLocale $n2nLocale) {
		$dtc = new DynamicTextCollection('rocket');
		return array(self::MULTI_UPLOAD_KEY => $dtc->translate('ei_impl_multi_upload_label'));
	}

	public function createOverallControls(Eiu $eiu, HtmlView $view): array {
		$request = $view->getRequest();
		$dtc = new DynamicTextCollection('rocket', $eiu->frame()->getN2nLocale());
		
		$name = $dtc->translate('ei_impl_multi_upload_label');
		$tooltip = $dtc->translate('ei_impl_multi_upload_tooltip');
		
		return array(self::MULTI_UPLOAD_KEY => HrefControl::create($eiu->frame()->getEiFrame(), $this, null,
				new SiButton($name, $tooltip, true, SiButton::TYPE_SECONDARY, SiIconType::ICON_UPLOAD)));
	}
}

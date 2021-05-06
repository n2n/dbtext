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
namespace rocket\ei\manage\preview\controller;

use n2n\web\http\controller\ControllerAdapter;
use rocket\ei\manage\preview\model\PreviewModel;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\Eiu;

abstract class PreviewControllerAdapter extends ControllerAdapter implements PreviewController {
	const PREVIEW_TYPE_DEFAULT = 'default';
	
	private $previewModel;
	
	public function setPreviewModel(PreviewModel $previewModel) {
		$this->previewModel = $previewModel;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\manage\preview\model\PreviewModel
	 */
	public function getPreviewModel() {
		if ($this->previewModel !== null) {
			return $this->previewModel;
		}
		
		throw new IllegalStateException('No PreviewModel assigned.');
	}
	
	/**
	 * @return string
	 * @throws IllegalStateException
	 */
	public function getPreviewType() {
		return $this->getPreviewModel()->getPreviewType();
	}
	
	/**
	 * @return \rocket\ei\util\Eiu
	 * @throws IllegalStateException
	 */
	public function eiu() {
		return $this->getPreviewModel()->getEiu();
	}
	
	public function getEntityObj() {
		return $this->eiu()->object()->getEntityObj();
	}
	
	public function getPreviewTypeOptions(Eiu $eiu): array {
		return array(self::PREVIEW_TYPE_DEFAULT => 'Default');
	}
}

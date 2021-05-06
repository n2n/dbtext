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
namespace rocket\impl\ei\component\prop\string\cke\model;

use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\UiComponent;
use rocket\impl\ei\component\prop\string\cke\ui\CkeHtmlBuilder;
use rocket\impl\ei\component\prop\string\cke\ui\Cke;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\web\dispatch\mag\UiOutfitter;
use rocket\impl\ei\component\prop\string\cke\CkeEiProp;
use n2n\util\type\ArgUtils;

class CkeMag extends StringMag {
	private $mode;
	private $bbcode;
	private $tableEditing;
	private $ckeLinkProviders;
	private $ckeCssConfig;

	public function __construct($label, $value = null, bool $mandatory = false, 
			int $maxlength = null, array $inputAttrs = null, string $mode = CkeEiProp::MODE_NORMAL, bool $bbcode = false, 
			bool $tableEditing = false, array $ckeLinkProviders, CkeCssConfig $ckeCssConfig = null) {
		ArgUtils::valArray($ckeLinkProviders, CkeLinkProvider::class);
		
		parent::__construct($label, $value, $mandatory, $maxlength, true, $inputAttrs);
		$this->bbcode = $bbcode;
		$this->mode = $mode;
		$this->tableEditing = $tableEditing;
		$this->ckeLinkProviders = $ckeLinkProviders;
		$this->ckeCssConfig = $ckeCssConfig;
	}
	
	public function isMultiline() {
		return true;
	}
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $htmlView, UiOutfitter $uo): UiComponent {
		/* , $this->bbcode, false, $this->tableEditing, 
				$this->ckeLinkProviderLookupIds, $this->ckeCssCssConfigLookupId, $this->getInputAttrs()*/
		
		$ckeHtml = new CkeHtmlBuilder($htmlView);

		return $ckeHtml->getEditor($propertyPath,
				Cke::classic()->mode($this->mode)->table($this->tableEditing)->bbcode($this->bbcode),
				$this->ckeCssConfig, $this->ckeLinkProviders);
	}
}
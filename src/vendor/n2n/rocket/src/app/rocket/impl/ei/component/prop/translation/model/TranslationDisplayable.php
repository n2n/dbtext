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
namespace rocket\impl\ei\component\prop\translation\model;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\gui\GuiProp;
use rocket\ei\manage\gui\field\GuiFieldDisplayable;
use rocket\ei\util\Eiu;
use rocket\si\content\SiField;

class TranslationDisplayable implements GuiFieldDisplayable {
	private $guiProp;
	private $localeDefs;
	private $guiFieldDisplayables = array();
	
	public function __construct(GuiProp $guiProp, array $localeDefs) {
		$this->guiProp = $guiProp;
		$this->localeDefs = $localeDefs;
	}
	
	
	public function isEmpty() {
		return empty($this->guiFieldDisplayables);
	}
	
	public function putDisplayable($n2nLocaleId, GuiFieldDisplayable $guiFieldDisplayable) {
		$this->guiFieldDisplayables[$n2nLocaleId] = $guiFieldDisplayable;
	}
	
	public function isMandatory(): bool {
		foreach ($this->guiFieldDisplayables as $translatedDisplayable) {
			if ($translatedDisplayable->isMandatory()) return true;
		}
		
		return false;
	}
	
	public function getHtmlContainerAttrs(): array {
		return array();
	}
	
	function createOutEifGuiField(Eiu $eiu): EifGuiField {
// 		$outputUiComponents = array();
// 		foreach ($this->translatedDisplayables as $n2nLocaleId => $translatedDisplayable) {
// 			$outputUiComponents[$n2nLocaleId] = $translatedDisplayable->createUiComponent($view);
// 		}
		
		return $view->getImport('\rocket\impl\ei\component\prop\translation\view\displayable.html',
				array('displayables' => $this->guiFieldDisplayables, 'label' => $this->guiProp->getDisplayLabelLstr()->t($view->getN2nLocale()),
						'localeDefs' => $this->localeDefs));
	}
}
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
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\dispatch\mag\model\ObjectMagAdapter;
use n2n\web\ui\UiComponent;
use n2n\web\dispatch\mag\UiOutfitter;

class ForkMag extends ObjectMagAdapter {
	private $n2nLocaleDefs;
	private $min;
	private $markClassKey;
	
	public function __construct($label, TranslationForm $translationForm, array $n2nLocaleDefs, int $min,
			string $markClassKey) {
		parent::__construct($label, $translationForm);
		$this->n2nLocaleDefs = $n2nLocaleDefs;
		$this->min = $min;
		$this->markClassKey = $markClassKey;
	}
	
	public function getContainerAttrs(HtmlView $view): array {
		return array('class' => 'rocket-impl-translation-manager',
				'data-rocket-impl-min' => $this->min,
				'data-rocket-impl-tooltip' => $view->getL10nText('ei_impl_tranlsation_manager_tooltip', null, null, 
						null, 'rocket'),
				'data-rocket-impl-mark-class-key' => $this->markClassKey);
	}
	
	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
		return $view->getImport('\rocket\impl\ei\component\prop\translation\view\forkMag.html', 
				array('propertyPath' => $propertyPath->ext('dispatchables'), 'localeDefs' => $this->n2nLocaleDefs,
						'label' => $this->getLabel($view->getN2nLocale()), 
						'markClassKey' => $this->markClassKey));
	}
}

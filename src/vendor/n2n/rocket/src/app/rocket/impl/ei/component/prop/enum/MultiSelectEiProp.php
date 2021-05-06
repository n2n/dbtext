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
namespace rocket\impl\ei\component\prop\enum;

use n2n\impl\persistence\orm\property\ScalarEntityProperty;
use n2n\impl\web\dispatch\mag\model\MagCollectionArrayMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\property\ConstraintsConflictException;
use n2n\util\type\ArgUtils;
use n2n\util\type\TypeConstraint;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\EiPropPath;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use rocket\si\content\SiField;

class MultiSelectEiProp extends DraftablePropertyEiPropAdapter {
	const ATTR_OPTIONS_KEY = 'options';
	const ATTR_OPTIONS_LABEL_KEY = 'label';
	const ATTR_OPTIONS_VALUE_KEY = 'value';
	const OUTPUT_SEPARATOR = ', ';
	const ATTR_MIN_KEY = 'min';
	const ATTR_MAX_KEY = 'max';

	public function setEntityProperty(?EntityProperty $entityProperty) {
		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('scalar', 
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}

	function prepare() {
	}
	
	public function setup(Eiu $eiu, DataSet $dataSet) {
		try {
			$this->objectPropertyAccessProxy->setConstraints(TypeConstraint::createSimple(null, true));
		} catch (ConstraintsConflictException $e) {
			$setupProcess->failedE($this, $e);
		}
	}
	
// 	public function checkCompatibility(CompatibilityTest $compatibilityTest) {
// 		if (!$this->isCompatibleWith($compatibilityTest->getEntityProperty())) {
// 			$compatibilityTest->entityPropertyTestFailed();
// 			return;
// 		}
	
// 		$propertyConstraints = $compatibilityTest->getPropertyAccessProxy()->getConstraint();
// 		$requiredConstraints = TypeConstraint::createSimple(null);
// 		if ($propertyConstraints !== null && !$propertyConstraints->isPassableBy($requiredConstraints)) {
// 			$compatibilityTest->propertyTestFailed('EiProp can not pass Type ' . $requiredConstraints->__toString()
// 					. ' to property due to incompatible TypeConstraint ' . $propertyConstraints->__toString());
// 		}
// 	}
	
	public function createMagCollection() {
		$magCollection = parent::createMagCollection();
		$magCollection->addMag(self::ATTR_OPTIONS_KEY, new MagCollectionArrayMag('Options', function() {
			$magCollection = new MagCollection();
			$magCollection->addMag(self::ATTR_OPTIONS_LABEL_KEY, new StringMag('Label'));
			$magCollection->addMag(self::ATTR_OPTIONS_VALUE_KEY, new StringMag('Value'));
			return $magCollection;
		}));
		$magCollection->addMag(self::OPTION_MIN_KEY, new NumericMag('Min'));
		$magCollection->addMag(self::OPTION_MAX_KEY, new NumericMag('Max'));
		return $magCollection;
	}
	
	public function getOptions() {
		$options = array();
		foreach ((array) $this->dataSet->get(self::ATTR_OPTIONS_KEY) as $attrs) {
			if (isset($attrs[self::ATTR_OPTIONS_VALUE_KEY]) && isset($attrs[self::ATTR_OPTIONS_LABEL_KEY])) {
				$options[$attrs[self::ATTR_OPTIONS_VALUE_KEY]] = $attrs[self::ATTR_OPTIONS_LABEL_KEY];
			}
		}
		return $options;
	}
	
	public function getMin() {
		return $this->dataSet->get(self::OPTION_MIN_KEY, 0);
	}
	
	public function getMax() {
		return $this->dataSet->get(self::OPTION_MAX_KEY);
	}
	
	public function isMandatory(Eiu $eiu) {
		return $this->getMin() > 0;
	}
	
	public function createInEifGuiField(Eiu $eiu): EifGuiField {
		return new MultiSelectMag($this->getLabelCode(), $this->getOptions(), array(), 
				$this->getMin(), $this->getMax());
	}
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\prop\EiProp::getTypeName()
	 */
	public function getTypeName(): string {
		return 'MultiSelect';
		
	}

	/* (non-PHPdoc)
	 * @see \rocket\ei\manage\gui\field\GuiField::createUiComponent()
	 */
	public function createUiComponent(HtmlView $view,
			Eiu $eiu) {
		return $view->getHtmlBuilder()->getEsc(
				implode(self::OUTPUT_SEPARATOR, (array)$eiEntry->getValue(EiPropPath::from($this))));
	}
	public function saveSiField(SiField $siField, Eiu $eiu) {
	}

	function createOutEifGuiField(Eiu $eiu): EifGuiField {
	}

	
}

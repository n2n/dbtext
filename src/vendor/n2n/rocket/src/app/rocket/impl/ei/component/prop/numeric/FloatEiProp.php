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
namespace rocket\impl\ei\component\prop\numeric;

// use n2n\impl\persistence\orm\property\FloatEntityProperty;
// use n2n\impl\persistence\orm\property\ScalarEntityProperty;
// use n2n\persistence\orm\property\EntityProperty;
// use n2n\reflection\property\AccessProxy;
// use n2n\util\type\ArgUtils;
// use n2n\util\type\TypeConstraint;
// use rocket\ei\util\Eiu;
// use rocket\impl\ei\component\prop\numeric\conf\FloatConfig;
// use rocket\si\content\SiField;
// use rocket\ei\util\factory\EifGuiField;

// class FloatEiProp extends NumericEiPropAdapter {
//     private $floatConfig;
    
//     function __construct() {
//         parent::__construct();
        
//         $this->floatConfig = new FloatConfig();
//     }
    
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\impl\ei\component\prop\numeric\NumericEiPropAdapter::createEiPropConfigurator()
// 	 */
// 	public function prepare() {
// 	    parent::prepare();
	    
// 	    $this->getConfigurator()->addAdaption($this->floatConfig);
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\impl\ei\component\prop\numeric\NumericEiPropAdapter::setEntityProperty()
// 	 */
// 	public function setEntityProperty(?EntityProperty $entityProperty) {
// 		ArgUtils::assertTrue($entityProperty instanceof ScalarEntityProperty
// 				|| $entityProperty instanceof FloatEntityProperty);
// 		$this->entityProperty = $entityProperty;
// 	}
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \rocket\impl\ei\component\prop\numeric\NumericEiPropAdapter::setObjectPropertyAccessProxy()
// 	 */
// 	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
// 		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('float',
// 				$propertyAccessProxy->getBaseConstraint()->allowsNull(), true));
// 		$this->objectPropertyAccessProxy = $propertyAccessProxy;
// 	}
	
// 	/**
// 	 * @return int
// 	 */
// 	public function getDecimalPlaces() {
// 		return $this->decimalPlaces;
// 	}
	
// 	/**
// 	 * @param int $decimalPlaces
// 	 */
// 	public function setDecimalPlaces(int $decimalPlaces) {
// 		$this->decimalPlaces = $decimalPlaces;
// 	}
	
// 	/**
// 	 * @return string
// 	 */
// 	public function getPrefix() {
// 		return $this->prefix;
// 	}
	
// 	/**
// 	 * @param string $prefix
// 	 */
// 	public function setPrefix(string $prefix = null) {
// 		$this->prefix = $prefix;
// 	}

// 	public function createInEifGuiField(Eiu $eiu): EifGuiField {
// 		$numericMag = new EiDecimalMag($this->getLabelLstr(), null,
// 				$this->isMandatory($eiu), $this->getMinValue(), $this->getMaxValue(), 
// 				$this->getDecimalPlaces(), array('placeholder' => $this->getLabelLstr()));
// 		$numericMag->setInputPrefix($this->prefix);
// 		return $numericMag;
// 	}
	
// 	public function saveSiField(SiField $siField, Eiu $eiu) {
// 	}

// }

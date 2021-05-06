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
namespace rocket\impl\ei\component\prop\string\conf;

use n2n\util\StringUtils;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\impl\web\dispatch\mag\model\StringArrayMag;
use n2n\util\type\TypeConstraint;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\persistence\meta\structure\Column;
use n2n\impl\web\dispatch\mag\model\StringMag;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\ArgUtils;

class UrlConfig extends PropConfigAdaption {
	const ATTR_ALLOWED_PROTOCOLS_KEY = 'allowedProtocols';
	const ATTR_RELATIVE_ALLOWED_KEY = 'relativeAllowed'; 
	const ATTR_AUTO_SCHEME_KEY = 'autoScheme';
	const ATTR_LYTEBOX_KEY = 'lytebox';
	
	private static $commonNeedles = array('url', 'link');
	private static $commonNotNeedles = array('label', 'title', 'text');
	
	private $autoScheme = null;
	private $allowedSchemes = null;
	private $relativeAllowed = false;
	private $lytebox = false;
	
	
	public function setAllowedSchemes(?array $allowedSchemes) {
		ArgUtils::valArray($allowedSchemes, 'string', true);
		$this->allowedSchemes = $allowedSchemes;
	}
	
	/**
	 * @return string[]|null
	 */
	public function getAllowedSchemes() {
		return $this->allowedSchemes;
	}
	
	public function isRelativeAllowed(): bool {
		return $this->relativeAllowed;
	}
	
	public function setRelativeAllowed(bool $relativeAllowed) {
		$this->relativeAllowed = $relativeAllowed;
	}
	
	public function setAutoScheme(string $autoScheme = null) {
		$this->autoScheme = $autoScheme;
	}
	
	public function getAutoScheme() {
		return $this->autoScheme;
	}
	
	public function isLytebox(): bool {
		return $this->lytebox;
	}
	
	public function setLytebox(bool $lytebox) {
		$this->lytebox = $lytebox;
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$level = parent::testCompatibility($propertyAssignation);
		
		if ($level <= CompatibilityLevel::NOT_COMPATIBLE) return $level;
		
		$propertyName = $propertyAssignation->getObjectPropertyAccessProxy()->getPropertyName();
		if (StringUtils::contains(self::$commonNeedles, $propertyName, false) 
				&& !StringUtils::contains(self::$commonNotNeedles, $propertyName, false)) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	public function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		$dataSet->set(self::ATTR_AUTO_SCHEME_KEY, 'https');
	}
	
	public function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_RELATIVE_ALLOWED_KEY)) {
			$this->setRelativeAllowed($dataSet->getBool(self::ATTR_RELATIVE_ALLOWED_KEY));
		}
		
		if ($dataSet->contains(self::ATTR_ALLOWED_PROTOCOLS_KEY)) {
			$this->allowedSchemes = $dataSet->optArray(self::ATTR_ALLOWED_PROTOCOLS_KEY, 'string', null, true);
			if (empty($this->allowedSchemes)) {
				$this->allowedSchemes = null;
			}
		}
		
		if ($dataSet->contains(self::ATTR_AUTO_SCHEME_KEY)) {
			$this->setAutoScheme($dataSet->getString(self::ATTR_AUTO_SCHEME_KEY, 
					false, null, true));
		}
		
		if ($dataSet->contains(self::ATTR_LYTEBOX_KEY)) {
			$this->setLytebox($dataSet->getBool(self::ATTR_LYTEBOX_KEY,
					false, null, true));
		}
	}
	
	
	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($dataSet);
		$magCollection->addMag(self::ATTR_RELATIVE_ALLOWED_KEY, new BoolMag('Relative allowed',
				$lar->getBool(self::ATTR_RELATIVE_ALLOWED_KEY, $this->isRelativeAllowed())));
	
		$magCollection->addMag(self::ATTR_ALLOWED_PROTOCOLS_KEY, 
				new StringArrayMag('Allowed protocols', $lar->getArray(self::ATTR_ALLOWED_PROTOCOLS_KEY, 
						TypeConstraint::createSimple('string'), (array) $this->getAllowedSchemes())));
	
		$magCollection->addMag(self::ATTR_AUTO_SCHEME_KEY, 
				new StringMag('Auto scheme', $lar->getString(self::ATTR_AUTO_SCHEME_KEY, 
						$this->getAutoScheme())));
		
		$magCollection->addMag(self::ATTR_LYTEBOX_KEY, new BoolMag('open in Lytebox',
				$lar->getBool(self::ATTR_LYTEBOX_KEY, $this->isLytebox())));
	}
	
	public function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$dataSet->set(self::ATTR_RELATIVE_ALLOWED_KEY, $magCollection
				->getMagByPropertyName(self::ATTR_RELATIVE_ALLOWED_KEY)->getValue());

		$allowedProtocols = $magCollection->getMagByPropertyName(self::ATTR_ALLOWED_PROTOCOLS_KEY)->getValue();
		$dataSet->set(self::ATTR_ALLOWED_PROTOCOLS_KEY, empty($allowedProtocols) ? null : $allowedProtocols);
		
		$dataSet->set(self::ATTR_AUTO_SCHEME_KEY, $magCollection
				->getMagByPropertyName(self::ATTR_AUTO_SCHEME_KEY)->getValue());
		
		$dataSet->set(self::ATTR_LYTEBOX_KEY, $magCollection
				->getMagByPropertyName(self::ATTR_LYTEBOX_KEY)->getValue());
	}
}

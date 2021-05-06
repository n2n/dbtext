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

use n2n\util\ex\IllegalStateException;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\impl\ei\component\prop\string\PathPartEiProp;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use rocket\impl\ei\component\prop\string\modificator\PathPartEiModificator;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\util\type\attrs\InvalidAttributeException;
use n2n\util\StringUtils;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\persistence\meta\structure\Column;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\manage\generic\UnknownScalarEiPropertyException;
use rocket\ei\manage\generic\UnknownGenericEiPropertyException;
use n2n\util\type\CastUtils;
use rocket\ei\manage\generic\ScalarEiProperty;
use rocket\ei\manage\generic\GenericEiProperty;
use rocket\ei\util\spec\EiuEngine;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use rocket\ei\util\Eiu;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\EiPropPath;
use n2n\impl\web\dispatch\mag\model\MagForm;

class PathPartConfig extends PropConfigAdaption {
	const ATTR_BASE_PROPERTY_FIELD_ID_KEY = 'basePropertyFieldId';
	const ATTR_NULL_ALLOWED_KEY = 'allowEmpty';
	const ATTR_UNIQUE_PER_FIELD_ID_KEY = 'uniquePerFieldId';
	const ATTR_CRITICAL_KEY = 'critical';
	const ATTR_CRITICAL_MESSAGE_KEY = 'criticalMessageCodeKey';
	
	private static $commonNeedles = array('pathPart');

	const URL_COUNT_SEPERATOR = '-';
	
	/**
	 * @var PathPartEiProp
	 */
	private $eiProp;
	private $nullAllowed = false;
	private $baseScalarEiProperty;
	private $uniquePerGenericEiProperty;
	private $critical = false;
	private $criticalMessage;
	
	public function __construct(PathPartEiProp $eiProp) {
		$this->eiProp = $eiProp;
	}
	
	public function isNullAllowed() {
		return $this->nullAllowed;
	}
	
	public function setNullAllowed(bool $nullAllowed) {
		$this->nullAllowed = $nullAllowed;
	}
	
	public function getBaseScalarEiProperty() {
		return $this->baseScalarEiProperty;
	}
	
	public function setBaseScalarEiProperty(ScalarEiProperty $baseScalarEiProperty = null) {
		$this->baseScalarEiProperty = $baseScalarEiProperty;
	}
	
	/**
	 * @return \rocket\ei\manage\generic\GenericEiProperty
	 */
	public function getUniquePerGenericEiProperty() {
		return $this->uniquePerGenericEiProperty;
	}
	
	public function setUniquePerGenericEiProperty(GenericEiProperty $uniquePerCriteriaProperty = null) {
		$this->uniquePerGenericEiProperty = $uniquePerCriteriaProperty;
	}
	
	public function isCritical() {
		return $this->critical;
	}
	
	public function setCritical(bool $critical) {
		$this->critical = $critical;
	}
	
	public function getCriticalMessage() {
		return $this->criticalMessage;
	}
	
	public function setCriticalMessage(string $criticalMessage = null) {
		$this->criticalMessage = $criticalMessage;
	}
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		if (StringUtils::contains(self::$commonNeedles, $propertyAssignation->getObjectPropertyAccessProxy()
				->getPropertyName())) {
			return CompatibilityLevel::COMMON;
		}
	
		return 0;
	}
	
	
	public function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		$options = $this->getBaseEiPropIdOptions();
		if (empty($options)) return;
		
		$dataSet->set(self::ATTR_BASE_PROPERTY_FIELD_ID_KEY, key($options));
	}
	
	
	function getEntityProperty() {
		return $this->getPropertyAssignation()->getEntityProperty(true);
	}
	
	public function setup(Eiu $eiu, DataSet $dataSet) {
		$this->entityProperty = $this->getPropertyAssignation()->getEntityProperty(true);
		
		$eiu->mask()->onEngineReady(function (EiuEngine $eiuEngine) use ($eiu, $dataSet) {
			$this->setupRef($eiu, $dataSet);
		});
		
		if ($dataSet->contains(self::ATTR_NULL_ALLOWED_KEY)) {
			$allowEmpty = $dataSet->getBool(self::ATTR_NULL_ALLOWED_KEY, false);
			if ($allowEmpty && $this->mandatoryRequired()) {
				throw new InvalidAttributeException(self::ATTR_NULL_ALLOWED_KEY 
						. ' must be false because AccessProxy does not allow null value: '
						. $this->getAssignedObjectPropertyAccessProxy());
			}
			$this->setNullAllowed($allowEmpty);
		}
		
		if ($dataSet->contains(self::ATTR_CRITICAL_KEY)) {
			$this->setCritical($dataSet->get(self::ATTR_CRITICAL_KEY));
		}
		
		if ($dataSet->contains(self::ATTR_CRITICAL_MESSAGE_KEY)) {
			$this->setCriticalMessage($dataSet->getString(self::ATTR_CRITICAL_MESSAGE_KEY));
		}

		$eiu->mask()->addEiModificator(new PathPartEiModificator($this, $eiu->prop()->getPath(), $eiu->mask()));
	}
	
	private function setupRef(Eiu $eiu, DataSet $dataSet) {
		$eiuEngine = $eiu->engine();
		
		if ($dataSet->contains(self::ATTR_BASE_PROPERTY_FIELD_ID_KEY)) {
			try {
				$this->setBaseScalarEiProperty($eiuEngine->getScalarEiProperty(
						$dataSet->getString(self::ATTR_BASE_PROPERTY_FIELD_ID_KEY)));
			} catch (\InvalidArgumentException $e) {
				throw $setupProcess->createException('Invalid base ScalarEiProperty configured.', $e);
			} catch (UnknownScalarEiPropertyException $e) {
				throw $setupProcess->createException('Configured base ScalarEiProperty not found.', $e);
			}
		}
		
		if ($dataSet->contains(self::ATTR_UNIQUE_PER_FIELD_ID_KEY)) {
			try {
				$this->setUniquePerGenericEiProperty($eiuEngine->getGenericEiProperty(
						$dataSet->getString(self::ATTR_UNIQUE_PER_FIELD_ID_KEY)));
			} catch (\InvalidArgumentException $e) {
				throw $setupProcess->createException('Invalid unique per GenericEiProperty configured.', $e);
			} catch (UnknownGenericEiPropertyException $e) {
				throw $setupProcess->createException('Configured unique per GenericEiProperty not found.', $e);
			}
		}
	}

	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$baseScalarEiPropertyId = null;
		if (null !== ($baseScalarEiProperty = $this->getBaseScalarEiProperty())) {
			$baseScalarEiPropertyId = $baseScalarEiProperty->getId();
		}
		
		$magCollection->addMag(self::ATTR_BASE_PROPERTY_FIELD_ID_KEY, new EnumMag('Base Field', 
				$this->getBaseEiPropIdOptions(), $dataSet->getString(self::ATTR_BASE_PROPERTY_FIELD_ID_KEY, 
						false, $baseScalarEiPropertyId), false));
		
		$genericEiPropertyId = null;
		if (null !== ($genericEiProperty = $this->getUniquePerGenericEiProperty())) {
			$genericEiPropertyId = $genericEiProperty->getId();
		}
		$magCollection->addMag(self::ATTR_UNIQUE_PER_FIELD_ID_KEY, new EnumMag('Unique per', 
				$this->getUniquePerOptions(), $dataSet->getString(self::ATTR_UNIQUE_PER_FIELD_ID_KEY, 
						false, $genericEiPropertyId)));
		
		$magCollection->addMag(self::ATTR_NULL_ALLOWED_KEY, new BoolMag('Null value allowed.', 
				$dataSet->getBool(self::ATTR_NULL_ALLOWED_KEY, false, 
						$this->isNullAllowed())));
		
		$magCollection->addMag(self::ATTR_CRITICAL_KEY, new BoolMag('Is critical', 
				$dataSet->getBool(self::ATTR_CRITICAL_KEY, false, $this->isCritical())));
		
		$magCollection->addMag(self::ATTR_CRITICAL_MESSAGE_KEY, new StringMag('Critical message (no message if empty)', 
				$dataSet->getString(self::ATTR_CRITICAL_MESSAGE_KEY, false, 
						$this->getCriticalMessage()), false));
		return new MagForm($magCollection);
	}
	
	private function getBaseEiPropIdOptions() {
		$baseEiPropIdOptions = array();
		foreach ($this->eiProp->getEiMask()->getEiEngine()->getScalarEiDefinition()->getMap()
				as $id => $genericScalarProperty) {
			if ($id === (string) $this->eiProp->getWrapper()->getEiPropPath()) continue;
			
			CastUtils::assertTrue($genericScalarProperty instanceof ScalarEiProperty);
			$baseEiPropIdOptions[$id] = (string) $genericScalarProperty->getLabelLstr();
		}
		return $baseEiPropIdOptions;
	}
	
	private function getUniquePerOptions() {
		$options = array();
		foreach ($this->eiProp->getWrapper()->getEiPropCollection()->getEiMask()->getEiEngine()->getGenericEiDefinition()->getGenericEiProperties() as $id => $genericEiProperty) {
			if ($id === (string) $this->eiProp->getWrapper()->getEiPropPath()) continue;
			CastUtils::assertTrue($genericEiProperty instanceof GenericEiProperty);
			$options[$id] = (string) $genericEiProperty->getLabelLstr();
		}
		return $options;
	}
	
	public function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$dataSet->appendAll($magCollection->readValues(array(
				self::ATTR_BASE_PROPERTY_FIELD_ID_KEY, self::ATTR_NULL_ALLOWED_KEY, 
				self::ATTR_UNIQUE_PER_FIELD_ID_KEY, self::ATTR_CRITICAL_KEY, 
				self::ATTR_CRITICAL_MESSAGE_KEY)), true);
	}
}

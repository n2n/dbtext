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
namespace rocket\impl\ei\component\prop\adapter\config;

use n2n\core\container\N2nContext;
use n2n\persistence\meta\structure\Column;
use n2n\reflection\property\ConstraintsConflictException;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\util\Eiu;
use n2n\util\type\CastUtils;
use rocket\impl\ei\component\config\AdaptableEiConfigurator;

class AdaptableEiPropConfigurator extends AdaptableEiConfigurator implements EiPropConfigurator {
	/**
	 * @var PropertyAssignation
	 */
	private $propertyAssignation;
	/**
	 * @var int
	 */
	private $defaultCompatibilityLevel = CompatibilityLevel::COMPATIBLE;
	
// 	public function getPropertyAssignation(): PropertyAssignation {
// 		return new PropertyAssignation($this->getAssignedEntityProperty(), 
// 				$this->getAssignedObjectPropertyAccessProxy());
// 	}

	/**
	 * @param int $defaultCompatibilityLevel
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function setDefaultCompatibilityLevel(int $defaultCompatibilityLevel) {
		$this->defaultCompatibilityLevel = $defaultCompatibilityLevel;
		return $this;
	}
	
	/**
	 * @return int
	 */
	function getDefaultCompatibilityLevel() {
		return $this->defaultCompatibilityLevel;
	}
	
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		try {
			$this->assignProperty($propertyAssignation);
		} catch (IncompatiblePropertyException $e) {
			return CompatibilityLevel::NOT_COMPATIBLE;
		}
		
		$curLevel = null;
		foreach ($this->getAdaptions() as $adaption) {
			CastUtils::assertTrue($adaption instanceof EiPropConfiguratorAdaption);
			$resultLevel = $adaption->testCompatibility($propertyAssignation);
			if ($resultLevel === null) {
				continue;
			}
			
			if ($resultLevel === CompatibilityLevel::NOT_COMPATIBLE) {
				return $resultLevel;
			}
			
			if ($curLevel === null || $curLevel > $resultLevel) {
				$curLevel = $resultLevel;
			}
		}
		return $curLevel ?? $this->defaultCompatibilityLevel;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::initAutoEiPropAttributes()
	 */
	function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
		$eiu = new Eiu($n2nContext);
		foreach ($this->getAdaptions() as $adaption) {
			CastUtils::assertTrue($adaption instanceof EiPropConfiguratorAdaption);
			$adaption->autoAttributes($eiu, $this->dataSet, $column);
		}
	}
	
	/**
	 * @param EiPropConfiguratorAdaption $adaption
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function addAdaption(EiPropConfiguratorAdaption $adaption) {
		$this->registerAdaption($adaption);
		return $this;
	}
	
	/**
	 * @param EiPropConfiguratorAdaption $adaption
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function removeAdaption(EiPropConfiguratorAdaption $adaption) {
		$this->unregisterAdaption($adaption);
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::assignProperty()
	 */
	function assignProperty(PropertyAssignation $propertyAssignation) {
// 		if (!$this->isPropertyAssignable()) {
// 			throw new IncompatiblePropertyException('EiProp can not be assigned to a property.');
// 		}
	
		if ($this->entityPropertyConfigurable !== null) {
			try {
				$this->entityPropertyConfigurable->setEntityProperty(
						$propertyAssignation->getEntityProperty(
								$this->entityPropertyConfigurable->isEntityPropertyRequired()));
			} catch (\InvalidArgumentException $e) {
				throw $propertyAssignation->createEntityPropertyException(null, $e);
			}
		}
	
		if ($this->objectPropertyConfigurable !== null) {
			try {
				$this->objectPropertyConfigurable->setObjectPropertyAccessProxy(
						$propertyAssignation->getObjectPropertyAccessProxy(
								$this->objectPropertyConfigurable->isObjectPropertyRequired()));
			} catch (\InvalidArgumentException $e) {
				throw $propertyAssignation->createAccessProxyException(null, $e);
			} catch (ConstraintsConflictException $e) {
				throw $propertyAssignation->createAccessProxyException(null, $e);
			}
		}
		
		foreach ($this->getAdaptions() as $adaption) {
			CastUtils::assertTrue($adaption instanceof EiPropConfiguratorAdaption);
			$adaption->assignProperty($propertyAssignation);
		}
		
		$this->propertyAssignation = $propertyAssignation;
	}
	
	public function getTypeName(): string {
		return self::shortenTypeName(parent::getTypeName(), array('Ei', 'Prop'));
	}
	
	public function setMaxCompatibilityLevel(int $maxCompatibilityLevel) {
		$this->defaultCompatibilityLevel = $maxCompatibilityLevel;
	}
	
	private $entityPropertyConfigurable;
	
	/**
	 * @param EntityPropertyConfigurable $entityPropertyConfigurable
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	public function setEntityPropertyConfigurable(EntityPropertyConfigurable $entityPropertyConfigurable) {
		$this->entityPropertyConfigurable = $entityPropertyConfigurable;
		return $this;
	}
	
	private $objectPropertyConfigurable;
	
	/**
	 * @param ObjectPropertyConfigurable $objectPropertyConfigurable
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	public function setObjectPropertyConfigurable(ObjectPropertyConfigurable $objectPropertyConfigurable) {
		$this->objectPropertyConfigurable = $objectPropertyConfigurable;
		return $this;
	}
	

	
// 	public function registerDraftConfigurable(DraftConfigurable $confDraftableEiProp) {
// 		$this->confDraftableEiProp = $confDraftableEiProp;		
// 	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\component\prop\indepenent\PropertyAssignation
	 */
	protected function getPropertyAssignation() {
		if ($this->propertyAssignation === null) {
			throw new IllegalStateException('No PropertyAssignation available.');
		}
		
		return $this->propertyAssignation;
	}
	
// 	/**
// 	 * @todo remove this everywhere
// 	 * @deprecated remove this everywhere
// 	 * @return boolean
// 	 */
// 	public function isPropertyAssignable(): bool {
// 		return $this->entityPropertyConfigurable !== null
// 				|| $this->objectPropertyConfigurable !== null;
// 	}
	
	protected function isAssignableToEntityProperty(): bool {
		return $this->entityPropertyConfigurable !== null;
	}
	
	protected function isAssignableToObjectProperty(): bool {
		return $this->objectPropertyConfigurable != null;
	}
	
	public function getEntityPropertyName() {
		if ($this->entityPropertyConfigurable === null) {
			return null;
		}
		
		return $this->entityPropertyConfigurable->getEntityProperty()->getName();
	}
	
	public function getObjectPropertyName() {
		if ($this->objectPropertyConfigurable === null) {
			return null;
		}
		
		return $this->objectPropertyConfigurable->getObjectPropertyAccessProxy()->getPropertyName();
	}
	
	/**
	 * @param \Closure $setupCallback
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function addSetupCallback(\Closure $setupCallback) {
		$this->registerSetupCallback($setupCallback);
		return $this;
	}
	
	/**
	 * @param \Closure $setupCallback
	 * @return \rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator
	 */
	function removeSetupCallback(\Closure $setupCallback) {
		$this->unregisterSetupCallback($setupCallback);
		return $this;
	}
}

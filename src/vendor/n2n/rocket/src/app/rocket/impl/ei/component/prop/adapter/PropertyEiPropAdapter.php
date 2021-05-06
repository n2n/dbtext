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
namespace rocket\impl\ei\component\prop\adapter;

use n2n\persistence\orm\property\EntityProperty;
use n2n\util\ex\IllegalStateException;
use rocket\ei\component\prop\indepenent\EiPropConfigurator;
use n2n\reflection\property\AccessProxy;
use rocket\impl\ei\component\prop\adapter\config\EntityPropertyConfigurable;
use rocket\impl\ei\component\prop\adapter\config\ObjectPropertyConfigurable;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;

abstract class PropertyEiPropAdapter extends IndependentEiPropAdapter 
		implements EntityPropertyConfigurable, ObjectPropertyConfigurable {
	
	protected $entityProperty;
	protected $objectPropertyAccessProxy;
	
	function getIdBase(): ?string {
		return $this->entityProperty !== null ? $this->entityProperty->getName() : null; 
	}
	
	/**
	 * @param EntityProperty $entityProperty
	 * @throws \InvalidArgumentException
	 */
	public function setEntityProperty(?EntityProperty $entityProperty) {
		if ($entityProperty === null && $this->isEntityPropertyRequired()) {
			throw new \InvalidArgumentException($this . ' requires an EntityProperty.');
		}
		
		$this->entityProperty = $entityProperty;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\config\EntityPropertyConfigurable::getEntityProperty()
	 */
	public function getEntityProperty(): ?EntityProperty {
		return $this->entityProperty;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return EntityProperty|NULL
	 */
	protected function requireEntityProperty(): ?EntityProperty  {
		if ($this->entityProperty === null) {
			throw new IllegalStateException('No EntityProperty assigned to ' . $this);
		}
		
		return $this->entityProperty;
	}
	
	public function isEntityPropertyRequired(): bool {
		return true;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \n2n\reflection\property\AccessProxy
	 */
	protected function requireObjectPropertyAccessProxy() {
		if ($this->objectPropertyAccessProxy === null) {
			throw new IllegalStateException('No ObjectPropertyAccessProxy assigned to ' . $this . '.');
		}
		
		return $this->objectPropertyAccessProxy;
	}
	
	public function getObjectPropertyAccessProxy(): ?AccessProxy {
		return $this->objectPropertyAccessProxy;
	}
	
	/**
	 * @param AccessProxy $objectPropertyAccessProxy
	 * @throws \InvalidArgumentException
	 */
	public function setObjectPropertyAccessProxy(?AccessProxy $objectPropertyAccessProxy) {
		if ($objectPropertyAccessProxy === null && $this->objectPropertyRequired) {
			throw new \InvalidArgumentException($this . ' requires an object property AccessProxy.');
		}
		
		$this->objectPropertyAccessProxy = $objectPropertyAccessProxy;
	}
	
	public function isObjectPropertyRequired(): bool {
		return true;
	}

	/**
	 * @return EiPropConfigurator
	 */
	protected function createConfigurator(): AdaptableEiPropConfigurator {
		return parent::createConfigurator()->setEntityPropertyConfigurable($this)
				->setObjectPropertyConfigurable($this);
	}
	
}

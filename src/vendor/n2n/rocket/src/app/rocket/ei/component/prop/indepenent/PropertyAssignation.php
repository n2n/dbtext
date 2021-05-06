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
namespace rocket\ei\component\prop\indepenent;

use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;

class PropertyAssignation {
	private $entityProperty;
	private $objectPropertyAccessProxy;
	private $failed = false;
	private $exception = null;
	
	public function __construct(EntityProperty $entityProperty = null, 
			AccessProxy $objectPropertyAccessProxy = null) {
		$this->entityProperty = $entityProperty;
		$this->objectPropertyAccessProxy = $objectPropertyAccessProxy;
	}
	
	/**
	 * @return boolean
	 */
	public function hasEntityProperty() {
		return $this->entityProperty !== null;
	}
	
	/**
	 * @return EntityProperty
	 * @throws IncompatiblePropertyException
	 */
	public function getEntityProperty(bool $required = false) {
		if ($required && $this->entityProperty === null) {
			throw new IncompatiblePropertyException(
					'EiProp must be assigned to a EntityProperty');
		}
		
		return $this->entityProperty;
	}
	
	/**
	 * @return boolean
	 */
	public function hasObjectPropertyAccessProxy() {
		return $this->objectPropertyAccessProxy !== null;
	}
	
	/**
	 * @return AccessProxy
	 * @throws IncompatiblePropertyException
	 */
	public function getObjectPropertyAccessProxy(bool $required = false) {
		if ($required && $this->objectPropertyAccessProxy === null) {
			throw new IncompatiblePropertyException(
					'EiProp must be assigned to a accessible object property');
		}
		
		return $this->objectPropertyAccessProxy;
	}
		
	public function createEntityPropertyException($reason = null, \Exception $e = null) {
		if ($this->entityProperty === null) {
			throw new IncompatiblePropertyException('EiProp must be assigned to a EntityProperty'
					. ($reason ? ' Reason: ' . $reason : ''), 0, $e);
		}
		
		return new IncompatiblePropertyException(
				'EiProp is not compatible with EntityProperty: ' . $this->entityProperty
						. ($reason ? ' Reason: ' . $reason : ''), 0, $e);
	}
	
	public function createAccessProxyException($reason = null, \Exception $e = null) {
		if ($this->objectPropertyAccessProxy === null) {
			throw new IncompatiblePropertyException(
					'EiProp must be assigned to a accessible object property');
		}
		
		return new IncompatiblePropertyException(
				'EiProp is not compatible with ' . $this->objectPropertyAccessProxy 
						. ($reason !== null ? ' Reason: ' . $reason : ''), 0, $e);
	}
}

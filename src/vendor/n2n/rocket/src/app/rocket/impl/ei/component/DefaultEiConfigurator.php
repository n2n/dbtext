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
namespace rocket\impl\ei\component;

use n2n\persistence\meta\structure\Column;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use rocket\ei\component\prop\indepenent\IncompatiblePropertyException;
use n2n\core\container\N2nContext;
use rocket\impl\ei\component\config\EiConfiguratorAdapter;

class DefaultEiConfigurator extends EiConfiguratorAdapter {
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::initAutoEiPropAttributes($column)
	 */
	public function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null) {
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::getAssignedEntityProperty()
	 */
	public function getAssignedEntityProperty() {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::getAssignedObjectPropertyAccessProxy()
	 */
	public function getAssignedObjectPropertyAccessProxy() {
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::isAssignableToEntityProperty()
	 */
	public function isAssignableToEntityProperty(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::isAssignableToObjectProperty()
	 */
	public function isAssignableToObjectProperty(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::testCompatibility($propertyAssignation)
	 */
	public function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		return CompatibilityLevel::NOT_COMPATIBLE;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::assignProperty($propertyAssignation)
	 */
	public function assignProperty(PropertyAssignation $propertyAssignation) {
		throw new IncompatiblePropertyException('EiProp can not be assigned to a property.');
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\prop\indepenent\EiPropConfigurator::getPropertyAssignation()
	 */
	public function getPropertyAssignation(): PropertyAssignation {
		return new PropertyAssignation();
	}	
}
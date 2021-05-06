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

use rocket\ei\component\EiConfigurator;
use n2n\persistence\meta\structure\Column;
use n2n\core\container\N2nContext;

interface EiPropConfigurator extends EiConfigurator {
	
	/**
	 * This method assigns proper default dataSet to the EiPropConfigurator. It gets called when the developer adds 
	 * new EiProp of the particular type.
	 * 
	 * EiThing is already assigned when this method gets called.
	 * 
	 * PropertyAssgination must be assigned before calling this method.
	 *   
	 * @param Column $column
	 */
	public function initAutoEiPropAttributes(N2nContext $n2nContext, Column $column = null);
	
// 	/**
// 	 * If the particular EiProp is assigned to entity property this method returns its EntityProperty object 
// 	 * otherweise it returns null.
// 	 * @return EntityProperty
// 	 */
// 	public function getAssignedEntityProperty();
	
// 	/**
// 	 * If the particular EiProp is assigned to an object property this method returns its AccessProxy otherwise it
// 	 * returns null.
// 	 * @return AccessProxy
// 	 */
// 	public function getAssignedObjectPropertyAccessProxy();
	
// 	/**
// 	 * Returns true if the particular EiProp is assignable to a single entity property
// 	 * @return bool
// 	 */
// 	public function isAssignableToEntityProperty(): bool;
	
// 	/**
// 	 * Returns true if the particular EiProp is assignable to a single object property
// 	 * @return bool
// 	 */
// 	public function isAssignableToObjectProperty(): bool;
	
// 	public function isPropertyAssignable(): bool;
	
// 	public function getPropertyAssignation(): PropertyAssignation;
	
	/**
	 * Method can be called without assigned EiType or EiMask.
	 * @param PropertyAssignation $propertyAssignation
	 * @return int
	 */
	public function testCompatibility(PropertyAssignation $propertyAssignation): ?int;
	
	/**
	 * @param PropertyAssignation $propertyAssignation
	 * @throws IncompatiblePropertyException
	 */
	public function assignProperty(PropertyAssignation $propertyAssignation);
}

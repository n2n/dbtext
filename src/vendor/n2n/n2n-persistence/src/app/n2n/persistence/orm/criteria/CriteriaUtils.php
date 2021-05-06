<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\persistence\orm\criteria;

class CriteriaUtils {

// 	public static function ensureComparable(EntityProperty $entityProperty, CriteriaProperty $criteriaProperty) {
// 		if ($entityProperty instanceof Comparable) return;
	
// 		throw new CriteriaConfigurationException(
// 				SysTextUtils::get('n2n_error_persistence_orm_criteria_property_cannot_be_used_in_comparison_clause',
// 						array('criteria_property' => $criteriaProperty->__toString(),
// 								'class' => $entityProperty->getEntityModel()->getClass()->getName(),
// 								'property' => $entityProperty->getName())));
// 	}
	
// 	public static function ensureJoinable(EntityProperty $entityProperty, CriteriaProperty $criteriaProperty) {
// 		if ($entityProperty instanceof JoinableEntityProperty) return;
	
// 		throw new CriteriaConfigurationException(SysTextUtils::get('n2n_error_persistence_orm_criteria_property_not_joinable',
// 				array('criteria_property' => $criteriaProperty->__toString(),
// 						'entity' => $entityProperty->getEntityModel()->getClass()->getName(),
// 						'property' => $entityProperty->getName())));
// 	}
}

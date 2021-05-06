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
namespace rocket\ei\util\filter\prop;

use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\web\dispatch\mag\Mag;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\core\model\Rocket;

class StringFilterProp extends FilterPropAdapter {
	
	public function createValueMag($value): Mag {
		return new StringMag('Value', $value);
	}
	
	protected function getOperators(): array {
		return array(CriteriaComparator::OPERATOR_EQUAL, CriteriaComparator::OPERATOR_NOT_EQUAL, 
				CriteriaComparator::OPERATOR_LIKE, CriteriaComparator::OPERATOR_NOT_LIKE);
	}
	
	protected function buildOperatorOptions(array $operators): array {
		$operatorOptions = array();
		foreach ($operators as $operator) {
			switch ($operator) {
				case CriteriaComparator::OPERATOR_LIKE:
					$operatorOptions[$operator] = Rocket::createLstr('ei_impl_filter_operator_like_label', Rocket::NS);
					break;
				case CriteriaComparator::OPERATOR_NOT_LIKE:
					$operatorOptions[$operator] = Rocket::createLstr('ei_impl_filter_operator_not_like_label', Rocket::NS);
					break;
				default:
					$operatorOptions[(string) $operator] = (string) $operator;
			}
		}
		return $operatorOptions;
	}
	
	protected function buildValue($operator, Mag $mag) {
		$value = $mag->getValue();
		if ($value === null || ($value === CriteriaComparator::OPERATOR_LIKE 
				|| $value === CriteriaComparator::OPERATOR_NOT_LIKE)) {
			return $value;
		}
		
		// @todo find solution with Dialect
		return str_replace(array('*', '?'), array('%', '_'), 
				str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $value));
	}
}

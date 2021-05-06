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

// use n2n\util\ex\IllegalStateException;
// use n2n\l10n\Message;
// use rocket\ei\manage\critmod\SelectorConstraint;
// use n2n\util\type\attrs\DataSet;
// use n2n\persistence\orm\criteria\compare\CriteriaComparator;

// class EnumSelectorItem extends EnumFilterProp implements SelectorItem {
	
// 	public function createSelectorConstraint(DataSet $dataSet) {
// 		return new StringSelectorConstraint($dataSet->get(self::OPERATOR_OPTION), 
// 				$dataSet->get(self::ATTR_VALUE_KEY));
// 	}
// }

// class StringSelectorConstraint implements SelectorConstraint {
// 	private $operator;
// 	private $comparableValue;
	
// 	public function __construct($operator, $comparableValue) {
// 		$this->operator = $operator;
// 		$this->comparableValue = $comparableValue;
// 	}
	
// 	private function prepareValue($value) {
// 		if ($value === null) return $value;
// 		return (string) $value;
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \rocket\ei\manage\SelectorConstraint::matches()
// 	 */
// 	public function matches($value) {
// 		$value = $this->prepareValue($value);
// 		switch ($this->operator) {
// 			case CriteriaComparator::OPERATOR_EQUAL:
// 				return $value === $this->comparableValue;
// 			case CriteriaComparator::OPERATOR_NOT_EQUAL:
// 				return $value !== $this->comparableValue;
// 			case CriteriaComparator::OPERATOR_LARGER_THAN:
// 				return $value > $this->comparableValue;
// 			case CriteriaComparator::OPERATOR_LARGER_THAN_OR_EQUAL_TO:
// 				return $value >= $this->comparableValue;
// 			case CriteriaComparator::OPERATOR_SMALLER_THAN:
// 				return $value < $this->comparableValue;
// 			case CriteriaComparator::OPERATOR_SMALLER_THAN_OR_EQUAL_TO:
// 				return $value <=  $this->comparableValue;
// 			default:
// 				throw new IllegalStateException('Unsupported operator ' . $this->operator);
// 		}
// 	}
// 	/* (non-PHPdoc)
// 	 * @see \rocket\ei\manage\critmod\SelectorConstraint::validate()
// 	 */
// 	public function validate($value) {
// 		if ($this->matches($value)) return null;
		
// 		return new Message('does not match');
// 	}
// }

<?php
/*
 * Copyright (c) 2012-2016, HofmÃ¤nner New Media.
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
 * Bert HofmÃ¤nner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas GÃ¼nther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\util\filter\prop;

use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\DataSet;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\ei\manage\critmod\filter\FilterProp;
use n2n\l10n\Lstr;
use rocket\ei\manage\critmod\filter\ComparatorConstraint;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\web\dispatch\mag\Mag;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\critmod\filter\impl\PropertyValueComparatorConstraint;

abstract class FilterPropAdapter implements FilterProp {
	const ATTR_OPERATOR_KEY = 'operator';
	const ATTR_VALUE_KEY = 'value';
	
	protected $criteriaProperty;
	protected $labelLstr;
	
	public function __construct(CriteriaProperty $criteriaProperty, $labelLstr) {
		$this->criteriaProperty = $criteriaProperty;
		$this->labelLstr = Lstr::create($labelLstr);
	}
	
	public function getLabel(): string {
		return (string) $this->labelLstr;
	}
	
	public function createMagDispatchable(DataSet $dataSet): MagDispatchable {
		$magCollection = new MagCollection();
		$magCollection->addMag(self::ATTR_OPERATOR_KEY, new EnumMag('Operator', 
				$this->buildOperatorOptions($this->getOperators()), null, true));
		$magCollection->addMag(self::ATTR_VALUE_KEY, $this->createValueMag($dataSet->get(self::ATTR_VALUE_KEY, false)));
		return new MagForm($magCollection);
	}
	
	public function buildDataSet(MagDispatchable $magDispatchable): DataSet {
		$magCollection = $magDispatchable->getMagCollection();
		$operator = $magCollection->getMagByPropertyName(self::ATTR_OPERATOR_KEY)->getValue();

		return new DataSet(array(
				self::ATTR_OPERATOR_KEY => $operator,
				self::ATTR_VALUE_KEY => $this->buildValue($operator, 
						$magCollection->getMagByPropertyName(self::ATTR_VALUE_KEY))));
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\util\filter\prop\FilterProp::createComparatorConstraint()
	 */
	public function createComparatorConstraint(DataSet $dataSet): ComparatorConstraint {
		return new PropertyValueComparatorConstraint($this->criteriaProperty,
				$dataSet->reqEnum(self::ATTR_OPERATOR_KEY, $this->getOperators()),
				CrIt::c($dataSet->get(self::ATTR_VALUE_KEY)));
	}
	
	protected function getOperators(): array {
		return array(CriteriaComparator::OPERATOR_EQUAL, CriteriaComparator::OPERATOR_NOT_EQUAL);
	}
	
	protected function buildOperatorOptions(array $operators) {
		return array_combine($operators, $operators);
	}
	
	protected abstract function createValueMag($value): Mag;
	
	protected function buildValue($operator, Mag $mag) {
		return $mag->getValue();
	}
}

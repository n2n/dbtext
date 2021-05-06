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

use rocket\core\model\Rocket;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\l10n\Lstr;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use rocket\ei\manage\entry\EiFieldConstraint;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\entry\EiFieldValidationResult;
use n2n\l10n\Message;
use rocket\ei\manage\critmod\filter\ComparatorConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\critmod\filter\impl\PropertyValueComparatorConstraint;
use rocket\ei\manage\security\filter\SecurityFilterProp;

class BoolFilterProp implements SecurityFilterProp {
	const ATTR_VALUE_KEY = 'value';
	const ATTR_VALUE_DEFAULT = false;
	
	protected $criteriaProperty;
	protected $labelLstr;
	
	public function __construct(CriteriaProperty $criteriaProperty, $labelLstr) {
		$this->criteriaProperty = $criteriaProperty;
		$this->labelLstr = Lstr::create($labelLstr);
	}
	
	public function getLabel(): string {
		return (string) $this->labelLstr;
	}
	
	private function readValue(DataSet $dataSet): bool {
		return (new LenientAttributeReader($dataSet))->getBool(self::ATTR_VALUE_KEY, self::ATTR_VALUE_DEFAULT);
	}
	
	public function createMagDispatchable(DataSet $dataSet): MagDispatchable {
		$magCollection = new MagCollection();
		$magCollection->addMag(self::ATTR_VALUE_KEY, new BoolMag($this->labelLstr, $this->readValue($dataSet)));
		return new MagForm($magCollection);
	}
	
	public function buildDataSet(MagDispatchable $magDispatchable): DataSet {
		return new DataSet($magDispatchable->getMagCollection()->readValues());
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\util\filter\prop\FilterProp::createComparatorConstraint()
	 */
	public function createComparatorConstraint(DataSet $dataSet): ComparatorConstraint {
		return new PropertyValueComparatorConstraint($this->criteriaProperty, CriteriaComparator::OPERATOR_EQUAL,
				CrIt::c($this->readValue($dataSet)));
	}
	
	public function createEiFieldConstraint(DataSet $dataSet): EiFieldConstraint {
		return new BoolEiFieldConstraint($this->labelLstr, $this->readValue($dataSet)); 
	}
}



class BoolEiFieldConstraint implements EiFieldConstraint {
	private $labelLstr;
	private $acceptedValue;

	public function __construct(Lstr $labelLstr, bool $acceptedValue) {
		$this->labelLstr = $labelLstr;
		$this->acceptedValue = $acceptedValue;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::acceptsValue($value)
	 */
	public function acceptsValue($value): bool {
		return $this->acceptedValue === $value;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::check($eiField)
	 */
	public function check(EiField $eiField): bool {
		return $this->acceptsValue($eiField->getValue()); 
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::validate($eiField, $validationResult)
	 */
	public function validate(EiField $eiField, EiFieldValidationResult $validationResult) {
		if ($this->check($eiField)) return;

		$validationResult->addError(Message::createCodeArg('ei_impl_bool_field_must_be_selected_err', 
				array('field' => $this->labelLstr)));
	}
}

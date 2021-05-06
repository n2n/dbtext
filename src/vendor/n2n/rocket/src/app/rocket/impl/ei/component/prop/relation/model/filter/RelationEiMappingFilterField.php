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
namespace rocket\impl\ei\component\prop\relation\model\filter;

use rocket\ei\manage\security\filter\SecurityFilterProp;
use rocket\ei\manage\security\filter\SecurityFilterDefinition;
use rocket\ei\manage\entry\EiFieldConstraint;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\attrs\DataSet;
use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\relation\model\RelationEntry;
use rocket\ei\manage\entry\EiField;
use rocket\ei\manage\entry\EiFieldValidationResult;
use n2n\l10n\Message;
use n2n\persistence\orm\criteria\item\CrIt;
use rocket\ei\manage\entry\EiEntryConstraint;
use rocket\ei\EiPropPath;

class RelationSecurityFilterProp extends RelationFilterProp implements SecurityFilterProp {
	
	private function getSecurityFilterDefinition(): SecurityFilterDefinition {
		$securityFilterDefinition = $this->targetFilterDef->getFilterDefinition();
		IllegalStateException::assertTrue($securityFilterDefinition instanceof SecurityFilterDefinition);
		return $securityFilterDefinition;
	}
	
	public function createEiFieldConstraint(DataSet $dataSet): EiFieldConstraint {
		$relationFilterConf = new RelationFilterConf($dataSet);
		
		$operator = $relationFilterConf->getOperator();
		switch ($operator) {
			case CriteriaComparator::OPERATOR_IN:
			case CriteriaComparator::OPERATOR_NOT_IN:
				if ($this->entityProperty->isToMany()) break;
		
				return new RelationEiFieldConstraint($operator,
						CrIt::c($this->lookupTargetEntityObjs($relationFilterConf->getTargetPids())));
			case CriteriaComparator::OPERATOR_CONTAINS:
			case CriteriaComparator::OPERATOR_CONTAINS_NOT:
				return new RelationEiFieldConstraint($operator,
						CrIt::c($this->lookupTargetEntityObjs($relationFilterConf->getTargetPids())));
				
			case CriteriaComparator::OPERATOR_EXISTS:
				$targetEiEntryConstraint = $this->getSecurityFilterDefinition()->createEimappingConstraint($filterSettingGroup);
				return new TestEiFieldConstraint($this->eiPropPath, false, $targetEiEntryConstraint);
				
			case CriteriaComparator::OPERATOR_NOT_EXISTS:
				$targetEiEntryConstraint = $this->getSecurityFilterDefinition()->createEimappingConstraint($filterSettingGroup);
				return new TestEiFieldConstraint($this->eiPropPath, false, $targetEiEntryConstraint);
		}
	}
}


class RelationEiFieldConstraint implements EiFieldConstraint {
	private $operator;
	private $targetEntityObjs;
	
	public function __construct($operator, array $targetEntityObjs) {
		$this->operator = $operator;
		$this->targetEntityObjs = $targetEntityObjs;
	}
	
	private function in($relationEntry) {
		ArgUtils::assertTrue($relationEntry instanceof RelationEntry);
		return in_array($relationEntry->getEiObject()->getEiEntityObj()->getEntityObj(), 
				$this->targetEntityObjs, true);
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::acceptsValue($value)
	 */
	public function acceptsValue($value): bool {
		switch ($this->operator) {
			case CriteriaComparator::OPERATOR_NOT_IN:
				if ($value === null) return true;
				return !$this->in($value);
			case CriteriaComparator::OPERATOR_IN:
				if ($value === null) return false;
				return !$this->in($value);
				
			case CriteriaComparator::OPERATOR_CONTAINS_NOT:
				ArgUtils::assertTrue(is_array($value));
				
				foreach ($value as $relationEntry) {
					if ($this->in($relationEntry)) return false;
				}
				
				return true;
			case CriteriaComparator::OPERATOR_CONTAINS:
				ArgUtils::assertTrue(is_array($value));
				
				foreach ($value as $relationEntry) {
					if (!$this->in($relationEntry)) return false;
				}
				
				return true;
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::check($eiField)
	 */
	public function check(EiField $eiField) {
		return $this->acceptsValue($eiField->getValue());
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::validate($eiField, $validationResult)
	 */
	public function validate(EiField $eiField, EiFieldValidationResult $validationResult) {
		if ($this->check($eiField)) return;
		
		$messageKey = null;
		switch ($this->operator) {
			case CriteriaComparator::OPERATOR_NOT_IN:
				$messageKey = 'ei_impl_relation_not_in_err';
				break;
			case CriteriaComparator::OPERATOR_IN:
				$messageKey = 'ei_impl_relation_in_err';
				break;
			case CriteriaComparator::OPERATOR_CONTAINS_NOT:
				$messageKey = 'ei_impl_relation_contains_not_err';
				break;
			case CriteriaComparator::OPERATOR_CONTAINS:
				$messageKey = 'ei_impl_relation_contains_err';
				break;
		}
		
		$validationResult->addError(Message::createCodeArg($messageKey, array('field' => $this->label,
				'target_entries' => implode(', ', $this->createTargetIdentityStrings()))));
	}
}

class TestEiFieldConstraint implements EiFieldConstraint {
	private $toMany;
	private $eiPropPath;
	private $exists;
	private $targetEiEntryConstraint;
	
	public function __construct(bool $toMany, EiPropPath $eiPropPath, bool $exists, EiEntryConstraint $targetEiEntryConstraint) {
		$this->toMany = $toMany;
		$this->eiPropPath = $eiPropPath;
		$this->exists = $exists;
		$this->targetEiEntryConstraint = $targetEiEntryConstraint;
	}
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::acceptsValue($value)
	 */
	public function acceptsValue($value) {
		if (!$this->toMany) {
			if ($value === null) {
				return !$this->exists;
			}

			ArgUtils::assertTrue($value instanceof RelationEntry);
			return $this->targetEiEntryConstraint->check($value->toEiEntry($this->targetEiuFrame));
		} 
		
		ArgUtils::assertTrue(is_array($value));
		if (empty($value)) {
			return !$this->exists;
		}
		
		if ($this->exists) {
			foreach ($value as $relationEntry) {
				ArgUtils::assertTrue($relationEntry instanceof RelationEntry);
				if (!$this->targetEiEntryConstraint->check($value->toEiEntry($this->targetEiuFrame))) {
					return false;
				}
			}
			
			return true;
		} else {
			foreach ($value as $relationEntry) {
				ArgUtils::assertTrue($relationEntry instanceof RelationEntry);
				if ($this->targetEiEntryConstraint->check($value->toEiEntry($this->targetEiuFrame))) {
					return false;
				}
			}
				
			return true;
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::check($eiField)
	 */
	public function check(EiField $eiField) {
		return $this->acceptsValue($eiField->getValue());
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiFieldConstraint::validate($eiField, $validationResult)
	 */
	public function validate(EiField $eiField, EiFieldValidationResult $validationResult) {
		if ($this->exists) {
			$value = $eiField->getValue();
			if (!$this->toMany) {
				if ($value === null) {
					$validationResult->addError(Message::createCodeArg('ei_impl_relation_must_exist_err', array('field' => $this->label)));
					return;
				}
				ArgUtils::assertTrue($value instanceof RelationEntry);
				$this->targetEiEntryConstraint->validate($value->toEiEntry($this->targetEiuFrame));
			} else {
				ArgUtils::assertTrue(is_array($value));
				foreach ($value as $relationEntry) {
					ArgUtils::assertTrue($relationEntry instanceof RelationEntry);
					$this->targetEiEntryConstraint->validate($relationEntry->toEiEntry($this->targetEiuFrame));
				}
			}

			return;
		}
		
		if (!$this->check($eiField)) {
			$validationResult->addError(Message::createCodeArg('ei_impl_relation_must_not_exist_err', array('field' => $this->label)));
		}
	}

	
	
}

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
namespace rocket\impl\ei\component\prop\relation\model;

use rocket\ei\manage\entry\EiFieldValidationResult;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\util\type\TypeConstraints;
use rocket\ei\util\entry\EiuEntry;
use n2n\validation\lang\ValidationMessages;
use n2n\util\type\ArgUtils;
use rocket\ei\util\frame\EiuFrame;

class ToManyEiField extends EiFieldAdapter {
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	/**
	 * @var RelationEiProp
	 */
	private $eiProp;
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var EiuFrame
	 */
	private $targetEiuFrame;
	
	/**
	 * @param Eiu $eiu
	 * @param RelationEiProp $eiProp
	 * @param RelationModel $relationModel
	 */
	function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationEiProp $eiProp, RelationModel $relationModel) {
		parent::__construct(TypeConstraints::array(false, EiuEntry::class));
		
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
		$this->eiProp = $eiProp;
		$this->relationModel = $relationModel;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::checkValue()
	 */
	protected function checkValue($value) {
		ArgUtils::assertTrue(is_array($value));
		foreach ($value as $eiuEntry) {
			ArgUtils::assertTrue($eiuEntry instanceof EiuEntry);
			if (!$this->relationModel->getTargetEiuEngine()->type()->matches($eiuEntry)) {
				return false;
			}
		}
		
		return true; 
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::readValue()
	 */
	protected function readValue() {
		$targetEntityObjs = $this->eiu->object()->readNativValue($this->eiProp);
		
		if ($targetEntityObjs === null) {
			return [];
		}
		
		$value = [];
		foreach ($targetEntityObjs as $key => $targetEntityObj) {
			$value[$key] = $this->targetEiuFrame->entry($targetEntityObj);
		}
		return $value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::isValueValid()
	 */
	protected function isValueValid($value) {
		ArgUtils::assertTrue(is_array($value));
		
		$min = $this->relationModel->getMin();
		$max = $this->relationModel->getMax();
		
		if (!(null === $max || count($value) <= $max) && (null === $min || count($value) >= $min)) {
			return false;
		}
		
		if (!$this->relationModel->isEmbedded() && !$this->relationModel->isIntegrated()) {
			return true;
		}
		
		foreach ($value as $targetEiuEntry) {
			ArgUtils::assertTrue($targetEiuEntry instanceof EiuEntry);
			
			if (!$targetEiuEntry->isValid()) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::validateValue()
	 */
	protected function validateValue($value, EiFieldValidationResult $validationResult) {
		$min = $this->relationModel->getMin();
		if ($min !== null && $min > count($value)) {
			$validationResult->addError(ValidationMessages::minElements($min, $this->eiu->prop()->getLabel()));
		}
		
		$max = $this->relationModel->getMax();
		if ($max !== null && $max < count($value)) {
			$validationResult->addError(ValidationMessages::maxElements($max, $this->eiu->prop()->getLabel()));
		}
		
		if (!($this->relationModel->isEmbedded() || $this->relationModel->isIntegrated())) {
			return;
		}
		
		foreach ($value as $targetEiuEntry) {
			ArgUtils::assertTrue($targetEiuEntry instanceof EiuEntry);
			$targetEiuEntry->getEiEntry()->validate();
			$validationResult->addSubEiEntryValidationResult($targetEiuEntry->getEiEntry()->getValidationResult());
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::isWritable()
	 */
	public function isWritable(): bool {
		return $this->eiu->object()->isNativeWritable($this->eiProp);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::writeValue()
	 */
	protected function writeValue($values) {
		ArgUtils::assertTrue(is_array($values));
		
		$nativeValues = new \ArrayObject();
		foreach ($values as $value) {
			ArgUtils::assertTrue($value instanceof EiuEntry);
			$nativeValues->append($value->getEntityObj());
			
			if ($this->relationModel->isEmbedded() || $this->relationModel->isIntegrated()) {
				$value->getEiEntry()->write();
			}
		}
		
		$this->eiu->object()->writeNativeValue($this->eiProp, $nativeValues);		
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::isCopyable()
	 */
	public function isCopyable(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::copyValue()
	 */
	public function copyValue(Eiu $copyEiu) {
		$targetEiuEntries = $this->getValue();
		
		if (empty($targetEiuEntries)) return [];
		
		if ($this->relationModel->isSourceMany() && !$this->relationModel->isEmbedded() 
				&& !$this->relationModel->isIntegrated()) {
			return $targetEiuEntries;
		}
		
		$copiedValues = [];
		foreach ($targetEiuEntries as $key => $targetEiuEntry) {
			$copiedValues[$key] = $targetEiuEntry->copy();	
		}
		return $copiedValues;
	}
}
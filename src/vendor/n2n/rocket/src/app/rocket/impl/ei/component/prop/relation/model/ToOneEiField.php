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
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\util\type\TypeConstraints;
use rocket\ei\util\entry\EiuEntry;
use n2n\validation\lang\ValidationMessages;
use n2n\util\type\CastUtils;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\frame\EiuFrame;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;

class ToOneEiField extends EiFieldAdapter {
	/**
	 * @var RelationEiProp
	 */
	private $eiProp;
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	/**
	 * @var bool
	 */
	private $mandatory = true;
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
	 * @param EiuFrame $targetEiuFrame
	 * @param RelationEiProp $eiProp
	 */
	function __construct(Eiu $eiu, EiuFrame $targetEiuFrame, RelationEiProp $eiProp, RelationModel $relationModel) {
		parent::__construct(TypeConstraints::type(EiuEntry::class, true));
		
		$this->eiProp = $eiProp;
		$this->relationModel = $relationModel;
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiuFrame;
	}
	
	/**
	 * @param bool $mandatory
	 */
	function setMandatory(bool $mandatory) {
		$this->mandatory = $mandatory;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\relation\conf\RelationModel
	 */
	function isMandatory() {
		return $this->mandatory;
	}
	
	protected function checkValue($value) {
		if ($value === null) return true;
		
		CastUtils::assertTrue($value instanceof EiuEntry);
		
		return $this->relationModel->getTargetEiuEngine()->type()->matches($value);
	}
	
	protected function readValue() {
		$targetEntityObj = $this->eiu->object()->readNativValue($this->eiProp);
		
		if ($targetEntityObj === null) {
			return $targetEntityObj;
		}
		
		return $this->targetEiuFrame->entry($targetEntityObj);
	}
	
	protected function isValueValid($value) {
		return $value !== null || !$this->mandatory;
	}

	protected function validateValue($value, EiFieldValidationResult $validationResult) {
		if ($this->isValueValid($value)) {
			return;
		}
		
		$validationResult->addError(ValidationMessages::mandatory($this->eiu->prop()->getLabel()));
		
		if (($this->relationModel->isEmbedded() || $this->relationModel->isIntegrated()) && $value !== null) {
			CastUtils::assertTrue($value instanceof EiuEntry);
			$value->getEiEntry()->validate();
		}
	}

	public function isWritable(): bool {
		return $this->eiu->object()->isNativeWritable($this->eiProp);
	}
	
	protected function writeValue($value) {
		$nativeValue = null;
		if ($value !== null) {
			ArgUtils::assertTrue($value instanceof EiuEntry);
			
			if ($this->relationModel->isEmbedded() || $this->relationModel->isIntegrated()) {
				$value->getEiEntry()->write();
			}
			
			$nativeValue = $value->getEntityObj();
		}
		
		$this->eiu->object()->writeNativeValue($this->eiProp, $nativeValue);		
	}
	
	public function isCopyable(): bool {
		return true;
	}

	public function copyValue(Eiu $copyEiu) {
		IllegalStateException::assertTrue($this->isCopyable());
		
		$targetEiuEntry = $this->getValue();
		
		if ($targetEiuEntry === null) return null;
		
		if ($this->relationModel->isSourceMany() && !$this->relationModel->isEmbedded() 
				&& !$this->relationModel->isIntegrated()) {
			return $targetEiuEntry;
		}
		
		return $targetEiuEntry->copy();
	}
}
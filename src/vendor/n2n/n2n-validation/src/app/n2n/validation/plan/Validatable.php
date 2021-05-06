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
namespace n2n\validation\plan;

use n2n\l10n\Message;
use n2n\util\type\TypeConstraint;
use n2n\l10n\Lstr;
use n2n\util\ex\IllegalStateException;

/**
 * Describes unit (e.g. property) that can be validated and added to a {@see ValidationGroup}.
 */
interface Validatable {
	
	/**
	 * @return string
	 */
	function getName(): string;
	
	/**
	 * @return string|Lstr|null
	 */
	function getLabel();
	
	/**
	 * @return bool
	 */
	function doesExist(): bool;
	
	/**
	 * @return mixed
	 * @throws IllegalStateException if {@see Validatable::doesExist()} returns false 
	 */
	function getValue();
	
	/**
	 * Returns true if future validations for this Validatable make sense. If this method returns false it signals 
	 * that all future validations planed for this Validatable should be skipped. This method exists to prevent the 
	 * occurrence of too many error messages for one single Validatable. All {@see Validator}s should follow this 
	 * rule but are not obligated to do so. In most cases this method returns true as long no errors have been added 
	 * through {@see self::addError()} yet.
	 * 
	 * @return bool
	 */
	function isOpenForValidation(): bool;
	
	/**
	 * @param Message $message
	 */
	function addError(Message $message);
	
	/**
	 * 
	 */
	function clearErrors(): void;
	
	/**
	 * @return TypeConstraint|NULL
	 */
	function getTypeConstraint(): ?TypeConstraint;
}
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

use n2n\util\type\TypeConstraint;
use n2n\validation\err\ValidationMismatchException;
use n2n\util\magic\MagicContext;

interface Validator {
	
	/**
	 * @param Validatable[] $validatables Validatables that were explicitly assigned to this validator
	 * @param ValidationContext $validationContext
	 * @param MagicContext $magicContext
	 * @return bool
	 * @throws ValidationMismatchException if the validator are not compatible with the validatables in the
	 * {@see ValidationGroup}
	 */
	function test(array $validatbles, ValidationContext $validationContext, MagicContext $magicContext): bool;
	
	/**
	 * @param Validatable[] $validatables Validatables that were explicitly assigned to this validator 
	 * @param ValidationContext $validationContext
	 * @param MagicContext $magicContext
	 * @throws ValidationMismatchException if the validator are not compatible with the validatables in the 
	 * {@see ValidationGroup}
	 */
	function validate(array $validatbles, ValidationContext $validationContext, MagicContext $magicContext);
	
	/**
	 * @return TypeConstraint|NULL
	 */
	function getTypeConstraint(): ?TypeConstraint;
}

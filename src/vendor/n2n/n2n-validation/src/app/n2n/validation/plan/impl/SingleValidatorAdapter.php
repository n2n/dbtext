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
namespace n2n\validation\plan\impl;

use n2n\util\type\ArgUtils;
use n2n\validation\plan\Validatable;
use n2n\validation\plan\ValidationContext;
use n2n\util\magic\MagicContext;

abstract class SingleValidatorAdapter extends ValidatorAdapter {

	final function test(array $validatables, ValidationContext $validationContext, MagicContext $magicContext): bool {
		ArgUtils::valArray($validatables, Validatable::class);
		
		foreach ($validatables as $validatable) {
			if (!$validatable->doesExist()) {
				continue;
			}
			
			if (!$this->testSingle($validatable, $magicContext)) {
				return false;
			}
		}
		
		return true;
	}
	
	protected abstract function testSingle(Validatable $validatable, MagicContext $magicContext): bool;
	
	final function validate(array $validatables, ValidationContext $validationContext, MagicContext $magicContext) {
		ArgUtils::valArray($validatables, Validatable::class);
		
		foreach ($validatables as $validatable) {
			if (!$validatable->doesExist() || !$validatable->isOpenForValidation()) {
				continue;
			}
			
			$this->validateSingle($validatable, $magicContext);
		}
	}
	
	protected abstract function validateSingle(Validatable $validatable, MagicContext $magicContext);
	
}
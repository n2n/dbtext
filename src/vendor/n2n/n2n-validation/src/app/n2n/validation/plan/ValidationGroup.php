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

use n2n\util\type\ArgUtils;
use n2n\validation\err\ValidationMismatchException;
use n2n\validation\err\UnresolvableValidationException;
use n2n\util\magic\MagicContext;

/**
 * 
 */
class ValidationGroup {
	/**
	 * @var Validator[]
	 */
	private $validators;
	/**
	 * @var Validatable[] $validatables
	 */
	private $validatables = [];
	
	/**
	 * @param Validator[] $validators
	 * @param Validatable[] $validatables
	 * @throws ValidationMismatchException if the validators are not compatible with the validatables and this 
	 * incompatibility can be detected while constructing the ValidationGroup. This is the case if {@see Validator} 
	 * and {@see Validatable} both provide a TypeConstraint (seee {@see Validator::getTypeConstraint()} and 
	 * {@see Validatable::getTypeConstraint()}.
	 */
	function __construct(array $validators, array $validatables) {
		ArgUtils::valArray($validators, Validator::class);
		ArgUtils::valArray($validatables, Validatable::class);
		
		$this->validators = $validators;
		
		foreach ($validatables as $validatable) {
			$this->addValidatable($validatable);
		}
	}
	
	/**
	 * @param Validatable $validatable
	 */
	private function addValidatable($validatable) {
		$typeConstraint = $validatable->getTypeConstraint();
		
		if ($typeConstraint === null) {
			array_push($this->validatables, $validatable);
			return;
		}
		
		foreach ($this->validators as $validator) {
			$validatorTypeConstraint = $validator->getTypeConstraint();
			
			if ($validatorTypeConstraint === null || $validatorTypeConstraint->isPassableBy($typeConstraint)) {
				continue;
			}
			
			throw new ValidationMismatchException('Validatable ' . $validatable->getName() . ' is not compatible with Validator ' 
					. (new \ReflectionClass($validator))->getShortName() . '. TypeConstraint missmatch: ' . $typeConstraint . ' / ' 
					. $validatorTypeConstraint);
		}
		
		array_push($this->validatables, $validatable);
	}
	
	/**
	 * @param ValidationContext $pool
	 * @param MagicContext $magicContext
	 * @throws ValidationMismatchException if the validators are not compatible with the validatables
	 * @throws UnresolvableValidationException if a {@see Validatable} required by a {@see Validator} could not have
	 * been resolved through the {@see ValidationContext}.
	 */
	function exec(ValidationContext $validationContext, MagicContext $magicContext) {
		foreach ($this->validators as $validator) {
			$validator->validate($this->validatables, $validationContext, $magicContext);
		}
	}
	
	function test(ValidationContext $validationContext, MagicContext $magicContext) {
		foreach ($this->validators as $validator) {
			$validator->validate($this->validatables, $validationContext, $magicContext);
		}
	}
}

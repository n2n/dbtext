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
namespace n2n\validation\plan\impl\common;

use n2n\validation\plan\Validatable;
use n2n\validation\lang\ValidationMessages;
use n2n\validation\plan\impl\SimpleValidatorAdapter;
use n2n\validation\plan\impl\ValidationUtils;
use n2n\l10n\Message;
use n2n\util\magic\MagicContext;

class MandatoryValidator extends SimpleValidatorAdapter {
	function __construct(Message $errorMessage = null) {
		parent::__construct(null, $errorMessage);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function testSingle(Validatable $validatable, MagicContext $magicContext): bool {
		$value = $this->readSafeValue($validatable);
		
		return $this->isValid($value);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function validateSingle(Validatable $validatable, MagicContext $magicContext) {
		$value = $this->readSafeValue($validatable);
		
		if (!$this->isValid($value)) {
			$validatable->addError(ValidationMessages::mandatory($this->readLabel($validatable)));
		}
	}
	
	private function isValid($value) {
		return null !== $value && (!is_scalar($value) || ValidationUtils::isNotEmpty($value));
	}
}
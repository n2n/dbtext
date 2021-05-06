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
namespace n2n\validation\plan\impl\string;

use n2n\util\uri\Url;
use n2n\validation\plan\impl\SingleValidatorAdapter;
use n2n\util\magic\MagicContext;
use n2n\validation\plan\Validatable;
use n2n\l10n\Message;
use n2n\validation\plan\impl\ValidationUtils;
use n2n\validation\lang\ValidationMessages;
use n2n\util\type\ArgUtils;

class UrlValidator extends SingleValidatorAdapter {
	private $schemeRequired;
	private $allowedSchemes;
	private $errorMessage;
	private $schemeRequiredErrorMessage;
	private $schemeErrorMessage;
	
	public function __construct(bool $schemeRequired = false, array $allowedSchemes = null, 
			Message $errorMessage = null, Message $schemeRequiredErrorMessage = null, 
			Message $schemeErrorMessage = null) {
		$this->schemeRequired = $schemeRequired;
		ArgUtils::valArray($allowedSchemes, 'string', true, 'allowedSchemes');
		$this->allowedSchemes = $allowedSchemes;
		$this->errorMessage = $errorMessage;
		$this->schemeErrorRequiredMessage = $schemeRequiredErrorMessage;
		$this->schemeErrorMessage = $schemeErrorMessage;
	}
	
	protected function testSingle(Validatable $validatable, MagicContext $magicContext): bool {
		$value = $this->readSafeValue($validatable);
		
		if ($value === null) {
			return true;
		}
		
		if (!ValidationUtils::isUrl($value)) {
			return false;
		}
		
		$url = Url::create($value);
		
		if ($this->schemeRequired && !$url->hasScheme()) {
			return false;
		}
		
		if ($this->allowedSchemes !== null && $url->hasScheme() && !in_array($url->getScheme(), $this->allowedSchemes)) {
			return false;
		}
		
		return true;
	}
	
	protected function validateSingle(Validatable $validatable, MagicContext $magicContext) {
		$value = $this->readSafeValue($validatable);
		
		if ($value === null)  {
			return;
		}
		
		if (!ValidationUtils::isUrl($value)) {
			$validatable->addError($this->errorMessage ?? ValidationMessages::url($this->readLabel($validatable)));
			return;
		}
		
		$url = Url::create($value);
		
		if ($this->schemeRequired && !$url->hasScheme()) {
			$validatable->addError($this->schemeErrorMessage 
					?? ValidationMessages::urlSchemeRequired($this->readLabel($validatable)));
			return;
		}
		
		if ($this->allowedSchemes !== null && $url->hasScheme() && !in_array($url->getScheme(), $this->allowedSchemes)) {
			$validatable->addError($this->schemeRequiredErrorMessage 
					?? ValidationMessages::urlScheme($this->allowedSchemes, $this->readLabel($validatable)));
			return;
		}
	}
}

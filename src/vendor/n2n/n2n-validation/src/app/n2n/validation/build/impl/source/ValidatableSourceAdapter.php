<?php
namespace n2n\validation\build\impl\source;

use n2n\l10n\Message;
use n2n\validation\build\ValidationResult;
use n2n\validation\plan\Validatable;
use n2n\validation\build\ErrorMap;
use n2n\util\type\ArgUtils;
use n2n\validation\build\impl\val\SimpleValidationResult;

abstract class ValidatableSourceAdapter implements ValidatableSource {
	/**
	 * @var Validatable[]
	 */
	protected $validatables = [];
	private $generalMessages = [];

	function __construct(array $validatables) {
		ArgUtils::valArray($validatables, Validatable::class);
		$this->validatables = $validatables;
	}
	
	public function addGeneralError(Message $message) {
		$this->generalMessages[] = $message;
	}
	
	function createValidationResult(): ValidationResult {
		$errorMap = new ErrorMap($this->generalMessages);
		
		foreach ($this->validatables as $key => $attrValidatable) {
			$errorMap->putChild($key, new ErrorMap($attrValidatable->getMessages()));
		}
		
		return new SimpleValidationResult($errorMap->isEmpty() ? null : $errorMap);
	}
	
	function onValidationStart() {
		$this->generalMessages = [];
		foreach ($this->validatables as $validatable) {
			$validatable->clearErrors();
		}
	}

	
}
<?php
namespace n2n\validation\build\impl\compose\union;

use n2n\util\magic\MagicContext;
use n2n\validation\build\ValidationJob;
use n2n\validation\build\ValidationResult;
use n2n\validation\plan\ValidationPlan;
use n2n\validation\plan\Validatable;
use n2n\validation\plan\Validator;
use n2n\util\type\ArgUtils;
use n2n\validation\plan\ValidationGroup;

class UnionValidationComposer implements ValidationJob {
	/**
	 * @var UnionValidatableSource
	 */
	private $validatableSource;
	/**
	 * @var ValidationPlan
	 */
	private $validationPlan;
	/**
	 * @var \Closure
	 */
	private $assembleClosures = [];
	
	/**
	 * @param UnionValidatableSource $validatableSource
	 */
	function __construct(UnionValidatableSource $validatableSource) {
		$this->validatableSource = $validatableSource;
		$this->validationPlan = new ValidationPlan($this->validatableSource);
	}
	
	/**
	 * @param Validator[] $validators
	 * @return UnionValidationComposer
	 */
	function val(Validator ...$validators) {
		array_push($this->assembleClosures, function () use ($validators) {
			$validatables = $this->validatableSource->getValidatables();
			ArgUtils::valArrayReturn($validatables, $this->validatableSource, 'getValidatables', 
					Validatable::class);
			
			$this->validationPlan->addValidationGroup(new ValidationGroup($validators, $validatables));
		});
		
		return $this;
	}
	
	private function prepareJob() {
		while (null !== ($closure = array_shift($this->assembleClosures))) {
			$closure();
		}
	}
	
	function test(MagicContext $magicContext): bool {
		$this->prepareJob();
		
		return $this->validationPlan->test($magicContext);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\validation\build\ValidationJob::exec()
	 */
	function exec(MagicContext $magicContext): ValidationResult {
		$this->prepareJob();
		
		$this->validatableSource->onValidationStart();
		$this->validationPlan->exec($magicContext);
		return $this->validatableSource->createValidationResult();
	}	
}

<?php
namespace n2n\validation\build\impl\compose\prop;

use n2n\validation\err\UnresolvableValidationException;
use n2n\validation\plan\ValidationGroup;
use n2n\validation\plan\ValidationPlan;
use n2n\validation\plan\ValidationContext;
use n2n\validation\plan\Validator;
use n2n\util\type\ArgUtils;
use n2n\validation\plan\Validatable;
use n2n\validation\build\ValidationJob;
use n2n\validation\build\ValidationResult;
use n2n\util\magic\MagicContext;
use n2n\validation\err\ValidationMismatchException;

class PropValidationComposer implements ValidationJob { 
	/**
	 * @var PropValidatableSource
	 */
	private $validatableSource;
	/**
	 * @var ValidationPlan
	 */
	private $validationPlan;
	/**
	 * @var \Closure[]
	 */
	private $assembleClosures = [];
	
	/**
	 * @param ValidationContext $validationContext
	 */
	function __construct(PropValidatableSource $validatableSource) {
		$this->validatableSource = $validatableSource;
		$this->validationPlan = new ValidationPlan($validatableSource);
	}
	
	/**
	 * 
	 * @param string $expression
	 * @param Validator ...$validators
	 * @return PropValidationComposer
	 */
	function prop(string $expression, Validator ...$validators) {
		return $this->props([$expression], ...$validators);
	}
	
	/**
	 * @param string[] $expressions
	 * @param Validator ...$validators
	 * @return PropValidationComposer
	 */
	function props(array $expressions, Validator ...$validators) {
		$this->assembleValidationGroup($expressions, $validators, true);
		return $this;
	}
	
	/**
	 *
	 * @param string $expression
	 * @param Validator ...$validators
	 * @return PropValidationComposer
	 */
	function optProp(string $expression, Validator ...$validators) {
		return $this->optProps([$expression], ...$validators); 
	}
	
	/**
	 * @param string[] $expressions
	 * @param Validator ...$validators
	 * @return PropValidationComposer
	 */
	function optProps(array $expressions, Validator ...$validators) {
		$this->assembleValidationGroup($expressions, $validators, false);
		return $this;
	}
	
	private function assembleValidationGroup(array $expressions, array $validators, bool $mustExist) {
		ArgUtils::valArray($expressions, 'string', false, 'expressions');
		
		array_push($this->assembleClosures, function () use ($expressions, $validators, $mustExist) {
			$validatables = [];
			foreach ($expressions as $expression) {
				$resolvedValidatables = $this->validatableSource->resolveValidatables($expression, $mustExist);
				ArgUtils::valArrayReturn($resolvedValidatables, $this->validatableSource, 'resolveValidatables', Validatable::class);
				array_push($validatables, ...$resolvedValidatables);
			}

			$this->validationPlan->addValidationGroup(new ValidationGroup($validators, $validatables));
		});
	}
	
	private function prepareJob() {
		while (null !== ($closure = array_shift($this->assembleClosures))) {
			$closure();
		}
	}
	
	/**
	 * @param MagicContext $magicContext
	 * @return bool
	 */
	function test(MagicContext $magicContext): bool {
		$this->prepareJob();
		
		return $this->validationPlan->test($magicContext);
	}
	
	/**
	 * @throws UnresolvableValidationException
	 * @throws ValidationMismatchException
	 * @return \n2n\validation\build\ValidationResult
	 */
	function exec(MagicContext $magicContext): ValidationResult {
		$this->prepareJob();
		
		$this->validatableSource->onValidationStart();
		$this->validationPlan->exec($magicContext);
		return $this->validatableSource->createValidationResult();
	}
}
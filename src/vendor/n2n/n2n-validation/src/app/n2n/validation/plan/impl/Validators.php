<?php
namespace n2n\validation\plan\impl;

use n2n\validation\plan\impl\common\MandatoryValidator;
use n2n\l10n\Message;
use n2n\validation\plan\impl\string\EmailValidator;
use n2n\util\type\TypeConstraint;
use n2n\validation\plan\impl\string\MinlengthValidator;
use n2n\validation\plan\impl\string\MaxlengthValidator;
use n2n\validation\plan\impl\reflection\TypeValidator;
use n2n\validation\plan\impl\enum\EnumValidator;
use n2n\validation\plan\impl\closure\ValueClosureValidator;
use n2n\validation\plan\impl\common\ExistsValidator;
use n2n\validation\plan\impl\string\UrlValidator;
use n2n\validation\plan\impl\closure\ClosureValidator;
use n2n\validation\plan\impl\number\MinValidator;
use n2n\validation\plan\impl\number\MaxValidator;

class Validators {
	
	/**
	 * @param TypeConstraint $typeConstraint
	 * @param Message|string|null $errorMessage
	 * @return \n2n\validation\plan\impl\reflection\TypeValidator
	 */
	static function type(TypeConstraint $typeConstraint, $errorMessage = null) {
		return new TypeValidator($typeConstraint, $errorMessage);
	}

	/**
	 * @param Message|null $errorMessage
	 * @return ExistsValidator
	 */
	static function exists($errorMessage = null) {
		return new ExistsValidator(Message::build($errorMessage));
	}
	
	/**
	 * @param Message|null $errorMessage
	 * @return \n2n\validation\plan\impl\common\MandatoryValidator
	 */
	static function mandatory($errorMessage = null) {
		return new MandatoryValidator(Message::build($errorMessage));
	}
	
	/**
	 * @param Message|null $errorMessage
	 * @return MinlengthValidator
	 */
	static function minlength(int $minlength, $errorMessage = null) {
		return new MinlengthValidator($minlength, Message::build($errorMessage));
	}
	
	/**
	 * @param Message|null $errorMessage
	 * @return MaxlengthValidator
	 */
	static function maxlength(int $maxlength, $errorMessage = null) {
		return new MaxlengthValidator($maxlength, Message::build($errorMessage));
	}
	
	/**
	 * @param Message|null $errorMessage
	 * @return MinValidator
	 */
	static function min(float $min, $errorMessage = null) {
		return new MinValidator($min, Message::build($errorMessage));
	}
	
	/**
	 * @param Message|null $errorMessage
	 * @return MaxValidator
	 */
	static function max(float $max, $errorMessage = null) {
		return new MaxValidator($max, Message::build($errorMessage));
	}
	
	/**
	 * @param Message|null $errorMessage
	 * @return \n2n\validation\plan\impl\common\MandatoryValidator
	 */
	static function email($errorMessage = null) {
		return new EmailValidator(Message::build($errorMessage));
	}
	
	static function url(bool $schemeRequired = false, array $allowedSchemes = null) {
		return new UrlValidator($schemeRequired, $allowedSchemes);
	}
	
	/**
	 * @param Message|null $errorMessage
	 * @return \n2n\validation\plan\impl\common\MandatoryValidator
	 */
	static function enum(array $values, $errorMessage = null) {
		return new EnumValidator($values, Message::build($errorMessage));
	}
	
	/**
	 * @param \Closure $closure
	 * @return \n2n\web\dispatch\map\val\ClosureValidator
	 */
	static function closure(\Closure $closure) {
		return new ClosureValidator($closure);
	}
	
	/**
	 * @param \Closure $closure
	 * @return ValueClosureValidator
	 */
	static function valueClosure(\Closure $closure) {
		return new ValueClosureValidator($closure);
	}
}
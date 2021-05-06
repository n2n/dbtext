<?php
namespace n2n\validation\build\impl\compose\prop;

use n2n\validation\plan\Validatable;
use n2n\validation\err\UnresolvableValidationException;
use n2n\validation\build\impl\source\ValidatableSource;

interface PropValidatableSource extends ValidatableSource {
	
	/**
	 * @param string $expression
	 * @param bool $mustExist
	 * @return Validatable[]
	 * @throws UnresolvableValidationException
	 */
	function resolveValidatables(string $expression, bool $mustExist): array;
}
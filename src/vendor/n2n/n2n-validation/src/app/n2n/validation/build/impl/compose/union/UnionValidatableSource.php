<?php
namespace n2n\validation\build\impl\compose\union;

use n2n\validation\plan\Validatable;
use n2n\validation\build\impl\source\ValidatableSource;

interface UnionValidatableSource extends ValidatableSource {
	
	/**
	 * @return Validatable[]
	 */
	function getValidatables(): array;
}

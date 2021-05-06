<?php
namespace n2n\validation\build\impl\source;

use n2n\validation\plan\Validatable;
use n2n\validation\build\impl\compose\union\UnionValidatableSource;

class StaticValidatableSource extends ValidatableSourceAdapter implements UnionValidatableSource {
	
	/**
	 * @param Validatable[] $validatables
	 */
	function __construct(array $validatables) {
		parent::__construct($validatables);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\validation\build\impl\compose\union\UnionValidatableSource::getValidatables()
	 */
	function getValidatables(): array {
		return $this->validatables;
	}	
}
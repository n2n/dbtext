<?php
namespace n2n\validation\build\impl;

use n2n\util\type\attrs\DataMap;
use n2n\util\type\attrs\AttributeReader;
use n2n\validation\build\impl\source\StaticValidatableSource;
use n2n\validation\build\impl\compose\union\UnionValidationComposer;
use n2n\validation\build\impl\source\LazyAttrsValidatableSource;
use n2n\validation\build\impl\val\ValueValidatable;
use n2n\validation\build\impl\compose\prop\PropValidationComposer;

class Validate {
	/**
	 * @param string[] $values
	 * @return UnionValidationComposer
	 */
	static function value(...$values) {
		$validatables = [];
		foreach ($values as $name => $value) {
			$validatables[] = new ValueValidatable($name, $value, true);
		}
		
		return new UnionValidationComposer(new StaticValidatableSource($validatables));
	}
	
	/**
	 * @param DataMap $attrs
	 * @return PropValidationComposer
	 */
	static function attrs(AttributeReader $attributeReader) {
		return new PropValidationComposer(new LazyAttrsValidatableSource($attributeReader));
	}
	
	/**
	 * @param array $data
	 * @return PropValidationComposer
	 */
	static function array(array $data) {
		return new PropValidationComposer(new LazyAttrsValidatableSource(new DataMap($data)));
	}
}

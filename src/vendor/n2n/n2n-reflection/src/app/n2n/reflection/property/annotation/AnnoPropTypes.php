<?php
namespace n2n\reflection\property\annotation;

use n2n\reflection\annotation\PropertyAnnotation;
use n2n\reflection\annotation\PropertyAnnotationTrait;
use n2n\reflection\annotation\AnnotationTrait;
use n2n\util\type\TypeConstraint;

class AnnoPropTypes implements PropertyAnnotation {
	use PropertyAnnotationTrait, AnnotationTrait;
	
	/**
	 * @var TypeConstraint[]
	 */
	private $typeConstraints = [];
	
	/**
	 * @param string[]|TypeConstraint[] $typeConstraints key is the name of the Property and the value its 
	 * {@see TypeConstraint::create()}.
	 */
	function __construct(array $typeConstraints) {
		foreach ($typeConstraints as $typeConstraint) {
			$this->typeConstraints[] = TypeConstraint::create($typeConstraint);
		}
	}
	
	/**
	 * @return \n2n\util\type\TypeConstraint[]
	 */
	function getTypeConstraints() {
		return $this->typeConstraints;
	}
}

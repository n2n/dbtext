<?php
namespace n2n\reflection\property\annotation;

use n2n\reflection\annotation\PropertyAnnotation;
use n2n\reflection\annotation\PropertyAnnotationTrait;
use n2n\reflection\annotation\AnnotationTrait;
use n2n\util\type\TypeConstraint;

class AnnoType implements PropertyAnnotation {
	use PropertyAnnotationTrait, AnnotationTrait;
	
	/**
	 * @var TypeConstraint
	 */
	private $typeConstraint;
	
	/**
	 * @param string|\ReflectionClass|TypeConstraint $typeConstraint {@see TypeConstraint::create()}
	 */
	function __construct($typeConstraint) {
		$this->typeConstraint = TypeConstraint::create($typeConstraint);
	}
	
	/**
	 * @return \n2n\util\type\TypeConstraint
	 */
	function getTypeConstraint() {
		return $this->typeConstraint;
	}
}

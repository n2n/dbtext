<?php
namespace n2n\util\test;

use PHPUnit\Framework\TestCase;
use n2n\util\type\TypeConstraint;
use n2n\util\type\TypeConstraints;
use n2n\util\type\AtuschMock;
use n2n\util\type\ArrayLikeMock;

class TypeConstraintTest extends TestCase  {
	
	function testCreate() {
		$tc = TypeConstraint::create('string');
		
		$this->assertTrue($tc->getTypeName() == 'string');
		$this->assertTrue(!$tc->allowsNull());
		$this->assertTrue(!$tc->isArrayLike());
		$this->assertTrue(null === $tc->getArrayFieldTypeConstraint());
		$this->assertTrue($tc->isTypeSafe());
		$this->assertTrue($tc->isScalar());
		
		
		$tc = TypeConstraint::create('?string');
		
		$this->assertTrue($tc->getTypeName() == 'string');
		$this->assertTrue($tc->allowsNull());
		$this->assertTrue(!$tc->isArrayLike());
		$this->assertTrue(null === $tc->getArrayFieldTypeConstraint());
		$this->assertTrue($tc->isTypeSafe());
		$this->assertTrue($tc->isScalar());
		
		
		$tc = TypeConstraint::create('array<string>');
		
		$this->assertTrue($tc->getTypeName() == 'array');
		$this->assertTrue(!$tc->allowsNull());
		$this->assertTrue($tc->isArrayLike());
		$this->assertTrue($tc->isTypeSafe());
		$this->assertTrue(!$tc->isScalar());
		$this->assertTrue($tc->getArrayFieldTypeConstraint()->getTypeName() == 'string');
		$this->assertTrue(!$tc->getArrayFieldTypeConstraint()->allowsNull());
		$this->assertTrue($tc->getArrayFieldTypeConstraint()->isScalar());
		$this->assertTrue($tc->getArrayFieldTypeConstraint()->isTypeSafe());
		
		
		$tc = TypeConstraint::create('?array<?string>');
		
		$this->assertTrue($tc->getTypeName() == 'array');
		$this->assertTrue($tc->allowsNull());
		$this->assertTrue($tc->isArrayLike());
		$this->assertTrue($tc->isTypeSafe());
		$this->assertTrue(!$tc->isScalar());
		$this->assertTrue($tc->getArrayFieldTypeConstraint()->getTypeName() == 'string');
		$this->assertTrue($tc->getArrayFieldTypeConstraint()->allowsNull());
		$this->assertTrue($tc->getArrayFieldTypeConstraint()->isScalar());
		$this->assertTrue($tc->getArrayFieldTypeConstraint()->isTypeSafe());
	}
	
	function testCreateSimple() {
		$tc = TypeConstraint::createSimple('string', false);
		
		$this->assertTrue($tc->getTypeName() == 'string');
		$this->assertTrue(!$tc->allowsNull());
		$this->assertTrue(!$tc->isArrayLike());
		$this->assertTrue(null === $tc->getArrayFieldTypeConstraint());
		$this->assertTrue($tc->isTypeSafe());
		$this->assertTrue($tc->isScalar());
		
		
		$tc = TypeConstraint::createSimple(AtuschMock::class, true);
		
		$this->assertTrue($tc->getTypeName() == AtuschMock::class);
		$this->assertTrue($tc->allowsNull());
		$this->assertTrue(!$tc->isArrayLike());
		$this->assertTrue(null === $tc->getArrayFieldTypeConstraint());
		$this->assertTrue($tc->isTypeSafe());
		$this->assertTrue(!$tc->isScalar());
		
		
		$tc = TypeConstraint::createSimple(ArrayLikeMock::class, true);
		
		$this->assertTrue($tc->getTypeName() == ArrayLikeMock::class);
		$this->assertTrue($tc->allowsNull());
		$this->assertTrue($tc->isArrayLike());
		$this->assertTrue(!$tc->isTypeSafe());
		$this->assertTrue(!$tc->isScalar());
		$this->assertTrue(!$tc->getArrayFieldTypeConstraint()->isTypeSafe());
		$this->assertTrue(!$tc->getArrayFieldTypeConstraint()->isScalar());
	}
	
	function testCreateArrayLike() {
		$tc = TypeConstraint::createArrayLike(ArrayLikeMock::class, true);
		
		$this->assertTrue($tc->getTypeName() == ArrayLikeMock::class);
		$this->assertTrue($tc->allowsNull());
		$this->assertTrue($tc->isArrayLike());
		$this->assertTrue(!$tc->isTypeSafe());
		$this->assertTrue(!$tc->isScalar());
		$this->assertTrue(!$tc->getArrayFieldTypeConstraint()->isTypeSafe());
		$this->assertTrue(!$tc->getArrayFieldTypeConstraint()->isScalar());
		
		$tc = TypeConstraint::createArrayLike(null, false);
		
		$this->assertTrue($tc->getTypeName() == 'arraylike');
		$this->assertTrue(!$tc->allowsNull());
		$this->assertTrue($tc->isArrayLike());
		$this->assertTrue(!$tc->isTypeSafe());
		$this->assertTrue(!$tc->isScalar());
		$this->assertTrue(!$tc->getArrayFieldTypeConstraint()->isTypeSafe());
		$this->assertTrue(!$tc->getArrayFieldTypeConstraint()->isScalar());
		$this->assertTrue($tc->getArrayFieldTypeConstraint()->isEmpty());
	}
	
	function testIsPassableBy() {
		$this->assertTrue(TypeConstraints::scalar()->isPassableBy(TypeConstraints::string()));
	}
	
	function testValidate() {
		try {
			TypeConstraints::mixed(true)->validate('somestring');
			$this->assertTrue(true);
		} catch (\n2n\util\type\ValueIncompatibleWithConstraintsException $e) {
			$this->fail('invalid ex: ' . $e->getMessage());
		}
	}
}
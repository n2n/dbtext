<?php
namespace n2n\util\type;

use PHPUnit\Framework\TestCase;

class TypeNameTest extends TestCase {
	
	function testIsA() {
		$this->assertTrue(TypeName::isA('string', 'mixed'));
	}
	
	function testA() {
		$this->assertTrue(TypeName::isValueA('somestring', 'mixed'));
		
		$this->assertTrue(TypeName::isValueA(true, 'scalar'));
	}
	
	function testIsConvertable() {
		$this->assertTrue('1' === TypeName::convertValue(1, 'string'));
		$this->assertTrue('-1' === TypeName::convertValue(-1, 'string'));
		$this->assertTrue('1.1' === TypeName::convertValue(1.1, 'string'));
		$this->assertTrue('1' === TypeName::convertValue(true, 'string'));
		$this->assertTrue('' === TypeName::convertValue(false, 'string'));
		try {
			TypeName::convertValue(null, 'string');
			$this->fail('no ex thrown');
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
			
		$this->assertTrue(1 === TypeName::convertValue('1', 'int'));
		$this->assertTrue(-1 === TypeName::convertValue('-1', 'int'));
		$this->assertTrue(1 === TypeName::convertValue(1, 'int'));
		try {
			TypeName::convertValue(null, 'string');
			$this->fail('no ex thrown');
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
		try {
			$this->assertTrue('1.1' === TypeName::convertValue(1.1, 'int'));
		} catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}
}
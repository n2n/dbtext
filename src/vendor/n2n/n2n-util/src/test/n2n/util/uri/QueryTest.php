<?php
namespace n2n\util\uri;

use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase  {
	
	function testToString() {
		$q = Query::create(['y' => 'x', 'a' => [ 'key1' => 'field1', 'key2' => 'field2']]);
		$this->assertSame('y=x&a[key1]=field1&a[key2]=field2', urldecode($q->__toString()));
		$this->assertTrue(!$q->isEmpty());
		
		
		$q2 = Query::create(['y' => null, 'a' => [ ]]);
		$this->assertSame('', urldecode($q2->__toString()));
		$this->assertTrue($q2->isEmpty());
	}
	
	function testExt() {
		$q = Query::create(['y' => 'x']);
		$this->assertSame('y=x', $q->__toString());
		
		$q2 = $q->ext(['y' => null]);	
		$this->assertSame('', $q2->__toString());
		$this->assertTrue(true, $q2->isEmpty());
		
	}
}
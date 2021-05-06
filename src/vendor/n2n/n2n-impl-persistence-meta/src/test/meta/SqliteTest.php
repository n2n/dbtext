<?php
namespace meta;

class SqliteTest extends DbTestCase {
	public function __construct($name = null, array $data = [], $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$this->setPersistenceUnitName('sqlite');
	}
	
	public function testView() {
		$this->viewTest(true);
		$this->viewTest(false);
	}
	
	public function testTable() {
		$this->tableTest(true);
		$this->tableTest(false);
	}
	
	public function isEnumAvailable() {
		return false;
	}
	
	public function isMediumAvailable() {
		return false;
	}
	
	public function isTextAvailable() {
		return false;
	}
	
	public function isColumnDetailAvailable() {
		return false;
	}
	
	function areForeignKeysAvailable() {
		return true;
	}
	
	function isCharsetAvailable() {
		return true;
	}
}
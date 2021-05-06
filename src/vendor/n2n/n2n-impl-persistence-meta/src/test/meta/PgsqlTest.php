<?php
namespace meta;

class PgsqlTest extends DbTestCase {
	public function __construct($name = null, array $data = [], $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$this->setPersistenceUnitName('pgsql');
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
		return true;
	}
	
	public function isMediumAvailable() {
		return true;
	}
	
	public function isTextAvailable() {
		return true;
	}
	
	function isColumnDetailAvailable() {
		return true;
	}
	
	function areForeignKeysAvailable() {
		return true;
	}
	
	function isCharsetAvailable() {
		return false;
	}
}
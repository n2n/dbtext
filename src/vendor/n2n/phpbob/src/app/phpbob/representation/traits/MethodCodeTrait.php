<?php
namespace phpbob\representation\traits;

use phpbob\PhpbobUtils;

trait MethodCodeTrait {
	private $methodCode;
	
	
	public function getMethodCode() {
		return $this->methodCode;
	}
	
	public function setMethodCode(string $methodCode = null) {
		$this->methodCode = (string) $methodCode;
		
		return $this;
	}
	
	public function generateMethodCodeStr() {
		if (empty($this->methodCode)) return '';

		return PHP_EOL . PhpbobUtils::removeTailingWhiteSpaces(
				PhpbobUtils::removeLeadingEOLs($this->methodCode)) . PHP_EOL;
	}
}
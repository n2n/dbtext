<?php
namespace phpbob\representation\traits;

use phpbob\PhpbobUtils;

trait AppendingCodeTrait {
	
	protected $appendingCode = null;
	
	public function getAppendingCode() {
		return $this->appendingCode;
	}
	
	public function setAppendingCode(string $prependingCode = null) {
		$this->appendingCode = $prependingCode;
		
		return $this;
	}
	
	public function hasAppendingCode() {
		return !empty(trim($this->appendingCode));
	}
	
	protected function getAppendingString() {
		if (!$this->hasAppendingCode()) {
			return '';
		}
		
		return PhpbobUtils::removeLeadingEOLs(PhpbobUtils::removeTailingWhiteSpaces($this->appendingCode)) . PHP_EOL;
	}
}
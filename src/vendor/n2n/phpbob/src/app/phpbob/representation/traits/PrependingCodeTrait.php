<?php
namespace phpbob\representation\traits;

use phpbob\PhpbobUtils;

trait PrependingCodeTrait {
	
	protected $prependingCode = null;
	
	public function getPrependingCode() {
		return $this->prependingCode;
	}
	
	public function setPrependingCode(string $prependingCode = null) {
		$this->prependingCode = $prependingCode;
		
		return $this;
	}
	
	public function hasPrependingCode() {
		return !empty(trim($this->prependingCode));
	}
	
	protected function getPrependingString() {
		if (!$this->hasPrependingCode()) {
			return '';
		}

		$this->prependingCode = PhpbobUtils::removeLeadingWhiteSpaces($this->prependingCode);
		
		return PhpbobUtils::removeTailingWhiteSpaces($this->prependingCode) . PHP_EOL;
	}
}
<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use phpbob\representation\traits\PrependingCodeTrait;
use n2n\util\StringUtils;
use phpbob\representation\traits\NameChangeSubjectTrait;

abstract class PhpVariable {
	use PrependingCodeTrait;
	use NameChangeSubjectTrait;
	
	protected $value;
	
	public function __construct(string $name, string $value = null, 
			string $prependingCode = null) {
		$this->prependingCode = $prependingCode;
		$this->name = $name;
		$this->value = $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue(string $value = null) {
		$this->value = $value;
		
		return $this;
	}
	
	public function hasValue() {
		return $this->value === Phpbob::KEYWORD_NULL;
	}
	
	protected function getNameValueString() {
		$string = $this->checkVariableName($this->name);
		if (null !== $this->value) {
			$string .= ' ' . Phpbob::ASSIGNMENT . ' ' . $this->value; 
		}
		return $string;
	}
	
	private function checkVariableName(string $name) {
		if (!StringUtils::startsWith(Phpbob::VARIABLE_PREFIX, $name)) {
			return Phpbob::VARIABLE_PREFIX . $name;
		}
		
		return $name;
	}
}
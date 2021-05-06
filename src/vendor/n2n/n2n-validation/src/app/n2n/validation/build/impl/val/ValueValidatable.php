<?php
namespace n2n\validation\build\impl\val;

use n2n\util\type\TypeConstraint;

class ValueValidatable extends ValidatableAdapter {
	private $name;
	private $value;
	private $doesExist;
	
	function __construct(string $name, $value, bool $doesExist) {
		parent::__construct($name);
		$this->name = $name;
		$this->value = $value;
		$this->doesExist = $doesExist;
	}
	
	function getValue() {
		return $this->value;
	}
	
	function doesExist(): bool {
		return $this->doesExist;
	}
	
	function setDoesExist(bool $doesExists) {
		return $this->doesExist = $doesExists;
	}
	
	public function getTypeConstraint(): ?TypeConstraint {
		return null;
	}
}
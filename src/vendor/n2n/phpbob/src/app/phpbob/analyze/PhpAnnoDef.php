<?php
namespace phpbob\analyze;

use phpbob\representation\anno\PhpAnno;

class PhpAnnoDef {
	private $typeName;
	private $paramStrs = [];
	
	public function __construct(string $typeName, array $paramStrs) {
		$this->typeName = $typeName;
		$this->paramStrs = $paramStrs;
	}
	
	public function getTypeName() {
		return $this->typeName;
	}

	public function setTypeName(string $typeName) {
		$this->typeName = $typeName;
	}

	public function getParamStrs() {
		return $this->paramStrs;
	}

	public function setParamStrs($paramStrs) {
		$this->paramStrs = $paramStrs;
	}

	public function applyTo(PhpAnno $phpAnno) {
		foreach ($this->paramStrs as $paramStr) {
			$phpAnno->createPhpAnnoParam($paramStr);
		}
	}
}
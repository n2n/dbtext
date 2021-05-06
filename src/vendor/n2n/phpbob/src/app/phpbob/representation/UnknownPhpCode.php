<?php
namespace phpbob\representation;

class UnknownPhpCode implements PhpNamespaceElement {
	private $phpFile;
	private $phpNamespace;
	private $code;
	
	public function __construct(PhpFile $phpFile, string $code, PhpNamespace $phpNamespace = null) {
		$this->phpFile = $phpFile;
		$this->phpNamespace = $phpNamespace;
		$this->code = $code;
	}
	
	public function getPhpFile() {
		return $this->phpFile;
	}

	public function getPhpNamespace() {
		return $this->phpNamespace;
	}

	public function getCode() {
		return $this->code;
	}
	
	public function setCode($code) {
		$this->code = $code;
	}

	public function __toString() {
		return $this->code;
	}
	
	public function getPhpTypeDefs() : array {
		return [];
	}
}
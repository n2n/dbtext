<?php
namespace phpbob\representation;

use phpbob\representation\traits\NameChangeSubjectTrait;
use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\Phpbob;
use phpbob\representation\traits\MethodCodeTrait;

class PhpFunction extends PhpParamContainerAdapter implements PhpNamespaceElement {
	use NameChangeSubjectTrait;
	use PrependingCodeTrait;
	use MethodCodeTrait;
	
	private $phpFile;
	private $phpNamespace;
	
	public function __construct(PhpFile $phpFile, string $name, PhpNamespace $phpNamespace = null) {
		$this->phpFile = $phpFile;
		$this->name = $name;
		$this->phpNamespace = $phpNamespace;
	}
	
	public function getPhpFile() {
		return $this->phpFile;
	}

	public function getPhpNamespace() {
		return $this->phpNamespace;
	}
	
	public function __toString() {
		return $this->getPrependingString() . Phpbob::KEYWORD_FUNCTION . ' ' . $this->name . $this->generateParamContainerStr()
				. Phpbob::GROUP_STATEMENT_OPEN . $this->generateMethodCodeStr() . Phpbob::GROUP_STATEMENT_CLOSE . PHP_EOL;
	}
}
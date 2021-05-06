<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use n2n\util\type\ArgUtils;
use phpbob\representation\traits\NameChangeSubjectTrait;
use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\traits\PhpNamespaceElementTrait;
class PhpConst implements PhpNamespaceElement {
	use NameChangeSubjectTrait;
	use PrependingCodeTrait;
	use PhpNamespaceElementTrait;
	
	private $value;
	private $phpType;
	
	public function __construct(PhpFile $phpFile, string $name, string $value, 
			PhpNamespace $phpNameSpace = null, PhpType $phpType = null) {
		$this->phpFile = $phpFile;
		$this->name = $name;
		$this->value = $value;
		$this->phpNamespace = $phpNameSpace;
		ArgUtils::assertTrue(null === $phpType || null !== $phpType && null !== $phpType, 
				'There can not be a classlike without a namespace');
		$this->phpType = $phpType;
	}

	public function getValue() {
		return $this->value;
	}

	/**
	 * @param string $value
	 * @return \phpbob\representation\PhpConst
	 */
	public function setValue(string $value) {
		$this->value = $value;
		
		return $this;
	}

	/**
	 * @return PhpType
	 */
	public function getPhpType() {
		return $this->phpType;
	}

	public function __toString() {
		$numTabs = null !== $this->phpType ? 1 : 0;
		
		return $this->getPrependingString() . str_repeat("\t", $numTabs) . Phpbob::KEYWORD_CONST . ' ' . $this->name . ' ' 
				. Phpbob::ASSIGNMENT . ' ' . $this->value . Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL;
	}
	
	public function getPhpTypeDefs() : array {
		return [];
	}
}
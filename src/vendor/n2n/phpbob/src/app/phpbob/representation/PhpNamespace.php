<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\traits\NameChangeSubjectTrait;

class PhpNamespace extends PhpNamespaceElementCreator implements PhpUseContainer {
	use PrependingCodeTrait;
	use NameChangeSubjectTrait;
	
	private $phpFile;
	private $bracketedSyntax;
	
	public function __construct(PhpFile $phpFile, 
			string $name, string $prependingCode = null, bool $bracketedSyntax = false) {
		parent::__construct(new PhpElementFactory($phpFile, $this));
		$this->phpFile = $phpFile;
		$this->name = $name;
		$this->prependingCode = $prependingCode;
		$this->bracketedSyntax = $bracketedSyntax;
	}
	
	/**
	 * @return PhpNamespaceElement []
	 */
	public function getPhpNamespaceElements() {
		return $this->phpElementFactory->getPhpFileElements();
	}
	
	/**
	 * @return \phpbob\representation\PhpFile
	 */
	public function getPhpFile() {
		return $this->phpFile;
	}
	
	public function hasBracketedSyntax() {
		return $this->bracketedSyntax;
	}
	
	public function setBracketedSyntax(bool $bracketedSyntax) {
		$this->bracketedSyntax = $bracketedSyntax;
		
		return $this;
	}
	
	public function getPhpTypeDefs(): array {
		return [];
	}
	
	public function resolvePhpTypeDefs() {
		$this->phpElementFactory->resolvePhpTypeDefs();
	}
	
	public function removeUnnecessaryPhpUses() {
		$this->phpElementFactory->removeUnnecessaryPhpUses();
	}

	public function __toString() {
		return $this->getPrependingString() . Phpbob::KEYWORD_NAMESPACE . ' ' . $this->name 
				. Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL . PHP_EOL .  $this->phpElementFactory;
	}
	
}
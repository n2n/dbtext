<?php
namespace phpbob;

class StatementGroup extends PhpStatementAdapter {
	private $startCode;
	private $childPhpStatements = array();
	private $endCode;
	
	public function __construct($startCode = null) {
		$this->startCode = $startCode;
	}
	public function getLines(): array {
		return preg_split('/(\\r\\n|\\n|\\r)/', (string) $this->startCode);
	}
	
	public function getStartCode() {
		return $this->startCode;
	}
	
	public function addChildPhpStatement(PhpStatement $phpStatement) {
		$this->childPhpStatements[] = $phpStatement;
	}

	public function getChildPhpStatements() {
		return $this->childPhpStatements;
	}
	
	public function setEndCode($endCode) {
		$this->endCode = $endCode;
	}
	
	public function getEndCode() {
		return $this->endCode;
	}
	
	public function __toString() {
		if (null === $this->startCode) {
			return $this->getStatementsString();
		}
		
		
		return $this->startCode . Phpbob::GROUP_STATEMENT_OPEN . 
				PhpbobUtils::removeTailingWhiteSpaces($this->getStatementsString()) .
				$this->endCode . Phpbob::GROUP_STATEMENT_CLOSE;
	}
	
	public function getStatementsString() {
		return implode('', array_merge($this->childPhpStatements, array($this->endCode)));
	}
}
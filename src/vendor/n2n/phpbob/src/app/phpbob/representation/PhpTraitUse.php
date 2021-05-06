<?php
namespace phpbob\representation;

use phpbob\Phpbob;

class PhpTraitUse {
	private $phpClassLike;
	private $phpTypeDef;
	
	public function __construct(PhpClassLike $phpClassLike, PhpTypeDef $phpTypeDef) {
		$this->phpClassLike = $phpClassLike;
		$this->phpTypeDef = $phpTypeDef;
	}
	
	public function getPhpTypeDef() {
		return $this->phpTypeDef;
	}
	
	public function __toString() {
		return "\t" . Phpbob::KEYWORD_USE . ' ' . $this->phpTypeDef . Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL;
	}
}
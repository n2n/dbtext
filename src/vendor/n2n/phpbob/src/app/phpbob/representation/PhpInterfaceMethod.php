<?php
namespace phpbob\representation;

use phpbob\representation\traits\NameChangeSubjectTrait;
use phpbob\Phpbob;

class PhpInterfaceMethod extends PhpParamContainerAdapter {
	use NameChangeSubjectTrait;
	
	private $phpInterFace;
	private $static = false;
	
	public function __construct(PhpInterface $phpInterface, string $name) {
		$this->phpInterFace = $phpInterface;
		$this->name = $name;	
	}
	
	public function setStatic(bool $static) {
		$this->static = $static;
	}
	
	public function isStatic() {
		return $this->static;
	}
	
	public function __toString() {
		return Phpbob::CLASSIFIER_PUBLIC . ' ' . Phpbob::KEYWORD_FUNCTION . $this->generateParamContainerStr() . Phpbob::SINGLE_STATEMENT_STOP;
	}
}
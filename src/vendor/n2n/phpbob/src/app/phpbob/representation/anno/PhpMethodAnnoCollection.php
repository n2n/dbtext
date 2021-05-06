<?php
namespace phpbob\representation\anno;

use phpbob\Phpbob;
use n2n\reflection\annotation\Annotation;

class PhpMethodAnnoCollection extends PhpAnnoCollectionAdapter {
	
	private $methodName;
	private $methodNameChangeClosures = [];
	
	public function __construct(PhpAnnotationSet $phpAnnotationSet, 
			string $methodName, $prependingCode = null) {
		parent::__construct($phpAnnotationSet, $prependingCode);

		$this->methodName = $methodName;
	}
	
	public function getMethodName() {
		return $this->methodName;
	}
	
	public function setMethodName(string $methodName) {
		if ($this->methodName !== $methodName) {
			$this->triggerMethodNameChange($this->methodName, $methodName);
			$this->methodName = $methodName;
		}
	
		return $this;
	}
	
	public function onMethodNameChange(\Closure $closure) {
		$this->methodNameChangeClosures[] = $closure;
	}

	public function determineAnnotation(PhpAnno $phpAnno): ?Annotation {
		if (!$this->phpAnnotationSet->isAnnotationSetAssigned()) return null;
	
		return $this->phpAnnotationSet->getAnnotationSet()->getMethodAnnotation($this->methodName, 
				$phpAnno->getPhpTypeDef()->determineUseTypeName());
	}
	
	private function triggerMethodNameChange(string $oldMethodName, string $newMethodName) {
		foreach ($this->methodNameChangeClosures as $methodNameChangeClosure) {
			$methodNameChangeClosure($oldMethodName, $newMethodName);
		}
	}
	
	public function __toString(): string {
		if ($this->isEmpty()) return $this->getPrependingString();
		
		return $this->getPrependingString() . "\t\t" . $this->phpAnnotationSet->getAiVariableName() . '->m(\'' . $this->methodName . '\', '
				. $this->getAnnotationString() . ')' . Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL;
	}
}
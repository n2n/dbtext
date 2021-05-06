<?php
namespace phpbob\representation\anno;

use phpbob\Phpbob;
use n2n\reflection\annotation\Annotation;

class PhpPropertyAnnoCollection extends PhpAnnoCollectionAdapter {
	private $propertyName;
	private $propertyNameChangeClosures = [];
	
	public function __construct(PhpAnnotationSet $phpAnnotationSet, string $propertyName, $prependingCode = null) {
		parent::__construct($phpAnnotationSet, $prependingCode);
		$this->propertyName = $propertyName;
	}
	
	public function getPropertyName() {
		return $this->propertyName;
	}
	
	public function setPropertyName(string $propertyName) {
		if ($this->propertyName !== $propertyName) {
			$this->triggerPropertyNameChange($this->propertyName, $propertyName);
			$this->propertyName = $propertyName;
		}
		
		return $this;
	}
	
	public function onPropertyNameChange(\Closure $closure) {
		$this->propertyNameChangeClosures[] = $closure;
	}

	public function determineAnnotation(PhpAnno $phpAnno): ?Annotation {
		if (!$this->phpAnnotationSet->isAnnotationSetAssigned()) return null;
	
		return $this->phpAnnotationSet->getAnnotationSet()->getPropertyAnnotation($this->propertyName,
				$phpAnno->getPhpTypeDef()->determineUseTypeName());
	}
	
	public function __toString(): string {
		if ($this->isEmpty()) return $this->getPrependingString();
		
		return $this->getPrependingString() . "\t\t" . $this->phpAnnotationSet->getAiVariableName() . '->p(\'' 
				. $this->propertyName . '\', ' . $this->getAnnotationString() . ')'
						. Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL;
	}
	
	private function triggerPropertyNameChange(string $oldPropertyName, string $newPropertyName) {
		foreach ($this->propertyNameChangeClosures as $propertyNameChangeClosure) {
			$propertyNameChangeClosure($oldPropertyName, $newPropertyName);
		}
	}
}
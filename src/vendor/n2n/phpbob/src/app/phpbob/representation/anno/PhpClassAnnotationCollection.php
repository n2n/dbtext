<?php
namespace phpbob\representation\anno;

use phpbob\Phpbob;
use n2n\reflection\annotation\Annotation;

class PhpClassAnnotationCollection extends PhpAnnoCollectionAdapter {
	public function determineAnnotation(PhpAnno $phpAnno): ?Annotation {
		if (!$this->phpAnnotationSet->isAnnotationSetAssigned()) return null;
		
		return $this->phpAnnotationSet->getAnnotationSet()->getClassAnnotation($phpAnno->getPhpTypeDef()->determineUseTypeName());
	}
	
	public function __toString(): string {
		if ($this->isEmpty()) return $this->getPrependingString();
		
		return $this->getPrependingString() . "\t\t" . $this->phpAnnotationSet->getAiVariableName() 
				. '->c(' . $this->getAnnotationString() . ')'. Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL;
	}
}
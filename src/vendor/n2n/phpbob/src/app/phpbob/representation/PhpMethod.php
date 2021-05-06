<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use phpbob\representation\traits\PrependingCodeTrait;
use n2n\util\type\ArgUtils;
use phpbob\representation\traits\NameChangeSubjectTrait;
use phpbob\representation\traits\MethodCodeTrait;

class PhpMethod extends PhpParamContainerAdapter {
	use PrependingCodeTrait;
	use NameChangeSubjectTrait;
	use MethodCodeTrait;

	private $phpClassLike;
	
	//http://php.net/manual/de/language.oop5.visibility.php
	private $classifier = Phpbob::CLASSIFIER_PUBLIC;
	private $static = false;
	private $final = false;
	private $abstract = false;

	private $params = array();

	public function __construct(PhpClassLike $phpClassLike, string $name, PhpTypeDef $returnPhpTypeDef = null) {
		$this->phpClassLike = $phpClassLike;
		$this->name = $name;
		$this->setReturnPhpTypeDef($returnPhpTypeDef);
		
		$that = $this;
		$this->onNameChange(function($oldName, $newName) use ($that) {
			$that->getPhpMethodAnnoCollection()->setMethodName($newName);
		});
	}
	
	public function getPhpClassLike() {
		return $this->phpClassLike;
	}

	public function getClassifier() {
		return $this->classifier;
	}
	
	public function setClassifier(string $classifier = null) {
		if ($classifier === null) {
			$classifier = Phpbob::CLASSIFIER_PUBLIC;
		}
		ArgUtils::valEnum($classifier, Phpbob::getClassifiers());
		$this->classifier = $classifier;
		
		return $this;
	}

	public function isStatic() {
		return $this->static;
	}

	public function setStatic(bool $static) {
		$this->static = $static;
		
		return $this;
	}

	public function isFinal() {
		return $this->final;
	}

	public function setFinal(bool $final) {
		$this->final = $final;
		
		return $this;
	}

	public function isAbstract() {
		return $this->abstract;
	}

	public function setAbstract(bool $abstract) {
		$this->abstract = $abstract;
		
		return $this;
	}
	
	public function getPhpMethodAnnoCollection() {
		return $this->phpClassLike->getPhpAnnotationSet()->getOrCreatePhpMethodAnnoCollection($this->name);
	}

// 	/**
// 	 * @return PhpParam []
// 	 */
// 	public function getParams() {
// 		return $this->params;
// 	}
	
// 	/**
// 	 * @return PhpParam
// 	 */
// 	public function getFirstParam() {
// 		if (count($this->params) === 0) return null;
		
// 		return reset($this->params);
// 	}

// 	public function setParams(array $params) {
// 		$this->params = $params;
// 	}

// 	public function addParam(PhpParam $param) {
// 		$this->params[$param->getName()] = $param;
// 	}

// 	public function determineParamTypeNames(PhpType $phpType) {
// 		foreach ($this->params as $param) {
// 			$typeName = $param->getTypeName();
// 			if (null === $typeName || PhpbobUtils::isInRootNamespace($typeName) 
// 					|| null === PhpbobUtils::extractNamespace($typeName)) continue;
			
// 			$param->setTypeName($phpType->extractUse($param->getTypeName()));
// 		}
// 	}

	public function __toString() {
		$string = $this->getPrependingString();
		if (!empty($string)) {
			$string = "\t" . $string;
		}
		
		$string .= "\t";
		if (null !== $this->classifier) {
			$string .= $this->classifier;
		}
		
		if ($this->static) {
			$string = $this->appendToString($string, Phpbob::KEYWORD_STATIC);
		}
		
		if ($this->final) {
			$string = $this->appendToString($string, Phpbob::KEYWORD_FINAL);
		}

		if ($this->abstract) {
			$string = $this->appendToString($string, Phpbob::KEYWORD_ABSTRACT);
		}

		$string = $this->appendToString($string, Phpbob::KEYWORD_FUNCTION)
				. ' ' . $this->name . $this->generateParamContainerStr(); 
		
		if (null !== ($typeDef = $this->getReturnPhpTypeDef())) {
			$string .= Phpbob::RETURN_TYPE_INDICATOR . ' ' . $typeDef;
		}
		
		if (!$this->abstract) {
			$string .= ' ' . Phpbob::GROUP_STATEMENT_OPEN;
		}

		
		return $string . $this->generateMethodCodeStr() . "\t" . (!$this->abstract ? Phpbob::GROUP_STATEMENT_CLOSE : ';') . PHP_EOL;
	}

	private function appendToString($string, $append) {
		if (!empty($string)) {
			$string .= ' ';
		}
		return $string . $append;
	}
}
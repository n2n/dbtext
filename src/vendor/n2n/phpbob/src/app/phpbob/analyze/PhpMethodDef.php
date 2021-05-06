<?php
namespace phpbob\analyze;

use phpbob\PhpStatement;
use n2n\util\type\ArgUtils;
use phpbob\Phpbob;
use phpbob\SingleStatement;
use phpbob\StatementGroup;

class PhpMethodDef {
	
	private $classifier;
	private $abstract = false;
	private $final = false;
	private $static = false;
	private $methodName;
	private $returnTypeName;
	private $parameterSignature;
	private $methodCode;
	
	public function getClassifier() {
		return $this->classifier;
	}

	public function isAbstract() {
		return $this->abstract;
	}

	public function isFinal() {
		return $this->final;
	}

	public function isStatic() {
		return $this->static;
	}

	public function getMethodName() {
		return $this->methodName;
	}

	public function getReturnTypeName() {
		return $this->returnTypeName;
	}

	public function getParameterSignature() {
		return $this->parameterSignature;
	}

	public function getMethodCode() {
		return $this->methodCode;
	}

	public function setClassifier(string $classifier) {
		$this->classifier = $classifier;
	}

	public function setAbstract(bool $abstract) {
		$this->abstract = $abstract;
	}

	public function setFinal(bool $final) {
		$this->final = $final;
	}

	public function setStatic(bool $static) {
		$this->static = $static;
	}

	public function setMethodName(string $methodName) {
		$this->methodName = $methodName;
	}

	public function setReturnTypeName(string $returnTypeName = null) {
		$this->returnTypeName = $returnTypeName;
	}

	public function setParameterSignature(string $parameterSignature) {
		$this->parameterSignature = $parameterSignature;
	}

	public function setMethodCode(string $methodCode = null) {
		$this->methodCode = $methodCode;
	}

	public static function fromPhpStatement(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhpbobAnalyzingUtils::isMethodStatement($phpStatement));
		$parts = preg_split('/[\(:]/', trim($phpStatement->getCode()), 3);
		if (count($parts) > 3) {
			throw new \InvalidArgumentException();
		}
		
		$methodDef = new PhpMethodDef();
		
		$signature = $parts[0];
		$methodDef->setParameterSignature(preg_replace('/\)((?!\)).)*$/', '', $parts[1]));
		
		if (count($parts) > 2) {
			$methodDef->setReturnTypeName(preg_replace('/;$/', '', trim($parts[2])));
		}
		
		foreach (self::explodeByWhiteSpaces($signature) as $part) {
			if (null !== $methodDef->getMethodName()) break;
			
			switch (strtolower($part)) {
				case Phpbob::KEYWORD_FUNCTION:
					break;
				case Phpbob::CLASSIFIER_PRIVATE:
				case Phpbob::CLASSIFIER_PROTECTED:
				case Phpbob::CLASSIFIER_PUBLIC:
					$methodDef->setClassifier($part);
					break;
				case Phpbob::KEYWORD_ABSTRACT:
					$methodDef->setAbstract(true);
					break;
				case Phpbob::KEYWORD_FINAL:
					$methodDef->setFinal(true);
					break;
				case Phpbob::KEYWORD_STATIC:
					$methodDef->setStatic(true);
					break;
				default:
					$methodDef->setMethodName($part);
			}
		}
		
		if ($methodDef->isAbstract()) {
			ArgUtils::assertTrue($phpStatement instanceof SingleStatement);
		} else {
			ArgUtils::assertTrue($phpStatement instanceof StatementGroup);
			$methodDef->setMethodCode($phpStatement->getStatementsString());
		}
		
		return $methodDef;
	}
	
	private static function explodeByWhiteSpaces($string) {
		return preg_split('/\s+/', $string, null, PREG_SPLIT_NO_EMPTY);
	}
}
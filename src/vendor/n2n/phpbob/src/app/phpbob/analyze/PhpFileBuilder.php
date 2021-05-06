<?php
namespace phpbob\analyze;

use phpbob\representation\PhpFile;
use phpbob\PhpStatement;
use n2n\util\type\ArgUtils;
use phpbob\representation\PhpNamespaceElementCreator;
use phpbob\Phpbob;
use phpbob\representation\PhpUse;
use phpbob\StatementGroup;
use phpbob\representation\PhpTypeDef;
use n2n\util\StringUtils;
use phpbob\representation\PhpType;
use phpbob\representation\PhpClassLike;
use phpbob\representation\PhpInterface;
use phpbob\representation\PhpParamContainer;

class PhpFileBuilder {
	private $phpFile;
	private $currentNamespace;
	private $unprocessedStatements = [];
	
	public function __construct() {
		$this->phpFile = new PhpFile();
	}
	
	public function getPhpFile() {
		return $this->phpFile;
	}
	
	public function processPhpStatement(PhpStatement $phpStatement) {
		if (!PhpbobAnalyzingUtils::isTypeStatement($phpStatement)) {
			if (PhpbobAnalyzingUtils::isNamespaceStatement($phpStatement)) {
				$this->createPhpNamespace($phpStatement);
			} elseif(PhpbobAnalyzingUtils::isUseStatement($phpStatement)) {
				$this->createPhpUse($phpStatement);
			} else {
				$this->unprocessedStatements[] = $phpStatement;
			}
			return;
		}
		
		if (PhpbobAnalyzingUtils::isClassStatement($phpStatement)) {
			$this->createPhpClass($phpStatement);
		} elseif (PhpbobAnalyzingUtils::isInterfaceStatement($phpStatement)) {
			$this->createPhpInterface($phpStatement);
		} else {
			$this->createPhpTrait($phpStatement);
		}
		
		$this->unprocessedStatements = [];
	}
	
	private function createPhpClass(PhpStatement $phpStatement) {
		ArgUtils::assertTrue($phpStatement instanceof StatementGroup
				&& PhpbobAnalyzingUtils::isClassStatement($phpStatement));
		
		$codeParts = self::determineCodeParts($phpStatement);
		$abstract = false;
		$final = false;
		
		while (true) {
			$codePart = strtolower(array_shift($codeParts));
			if ($codePart == Phpbob::KEYWORD_CLASS) break;
			
			switch ($codePart) {
				case Phpbob::KEYWORD_FINAL:
					$final = true;
					break;
				case Phpbob::KEYWORD_ABSTRACT:
					$abstract = true;
					break;
				case false:
					throw new \InvalidArgumentException('missing class Keyword');
			}
		}
		
		$phpClass = $this->determinePhpNamespaceElementCreator()->createPhpClass(array_shift($codeParts));
		
		$phpClass->setAbstract($abstract);
		$phpClass->setFinal($final);
		
		$inExtendsClause = false;
		$inImplementsClause = false;
		
		foreach ($codeParts as $codePart) {
			if ($inImplementsClause) {
				$codePart = str_replace(',', '', $codePart);
				$phpClass->addInterfacePhpTypeDef($this->buildTypeDef($codePart));
				continue;
			}
			
			if ($inExtendsClause) {
				$phpClass->setSuperClassTypeDef($this->buildTypeDef($codePart));
				$inExtendsClause = false;
				continue;
			}
			
			switch (strtolower($codePart)) {
				case Phpbob::KEYWORD_EXTENDS:
					$inExtendsClause = true;
					break;
				case Phpbob::KEYWORD_IMPLEMENTS:
					$inImplementsClause = true;
					break;
				default:
					throw new PhpSourceAnalyzingException('Invalid part in class statement: ' . $codePart);
			}
		}
		
		$this->applyClassLike($phpClass, $phpStatement);
	}
	
	private function createPhpInterface(PhpStatement $phpStatement) {
		ArgUtils::assertTrue($phpStatement instanceof StatementGroup
				&& PhpbobAnalyzingUtils::isInterfaceStatement($phpStatement));
		
		$codeParts = self::determineCodeParts($phpStatement);
		//shift "interface"
		array_shift($codeParts);
		$phpInterface = $this->determinePhpNamespaceElementCreator()->createPhpInterface(array_shift($codeParts));
		$phpInterface->setPrependingCode($this->determinePrependingCode($phpStatement));
		
		$inExtendsClause = false;
		foreach ($codeParts as $codePart) {			
			if ($inExtendsClause) {
				$phpInterface->addInterfacePhpTypeDef($this->buildTypeDef($codePart));
				continue;
			}
			
			switch (strtolower($codePart)) {
				case Phpbob::KEYWORD_EXTENDS:
					$inExtendsClause = true;
					break;
			}
		}
		
		foreach ($phpStatement->getChildPhpStatements() as $childPhpStatement) {
			if (PhpbobAnalyzingUtils::isConstStatement($childPhpStatement)) {
				$this->applyPhpConst($phpInterface, $childPhpStatement);
				continue;
			} elseif (PhpbobAnalyzingUtils::isMethodStatement($childPhpStatement)) {
				$this->applyPhpInterfaceMethod($phpInterface, $childPhpStatement);
				continue;
			}
			
			throw new PhpSourceAnalyzingException('Invalid interface structure detected: ' . 
					$childPhpStatement);
		}
	}
	
	private function createPhpTrait(PhpStatement $phpStatement) {
		ArgUtils::assertTrue($phpStatement instanceof StatementGroup && PhpbobAnalyzingUtils::isTraitStatement($phpStatement));
				
		$codeParts = self::determineCodeParts($phpStatement);
		//shift "trait"
		array_shift($codeParts);
				
		$traitName = array_shift($codeParts);
		$phpTrait = $this->determinePhpNamespaceElementCreator()->createPhpTrait($traitName);
				
		$this->applyClassLike($phpTrait, $phpStatement);
	}
	
	private function applyClassLike(PhpClassLike $phpClassLike, StatementGroup $statementGroup) {
		$phpClassLike->setPrependingCode($this->determinePrependingCode($statementGroup));
		foreach ($statementGroup->getChildPhpStatements() as $childPhpStatement) {
			if (PhpbobAnalyzingUtils::isConstStatement($childPhpStatement)) {
				$this->applyPhpConst($phpClassLike, $childPhpStatement);
				continue;
			}
			
			if (PhpbobAnalyzingUtils::isPropertyStatement($childPhpStatement)) {
				$this->applyPhpProperty($phpClassLike, $childPhpStatement);
				continue;
			}
			
			if (PhpbobAnalyzingUtils::isMethodStatement($childPhpStatement)) {
				if (PhpbobAnalyzingUtils::isAnnotationStatement($childPhpStatement)) {
					$this->applyAnnotationSet($phpClassLike, $childPhpStatement);
				} else {
					$this->applyPhpMethod($phpClassLike, $childPhpStatement);
				}
				continue;
			}
			
			if (PhpbobAnalyzingUtils::isTraitUseStatement($childPhpStatement)) {
				$this->applyTraitUse($phpClassLike, $childPhpStatement);
				continue;
			}
			
			throw new PhpSourceAnalyzingException('Invalid PHP Statement: ' . $childPhpStatement);
		}
		
		$phpClassLike->setAppendingCode($statementGroup->getEndCode());
	}
	
	private function determinePrependingCode(PhpStatement $phpStatement) {
		$prependingCode = implode('', $this->unprocessedStatements) . $this->createPrependingCode($phpStatement);
		if (empty($prependingCode)) return null;
		
		return $prependingCode;
	}
	
	private function createPhpNamespace(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhpbobAnalyzingUtils::isNamespaceStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement);
		
		$this->currentNamespace = $this->phpFile->createPhpNamespace($codeParts[1])
				->setPrependingCode($this->determinePrependingCode($phpStatement));
	}
	
	private function createPhpUse(PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhpbobAnalyzingUtils::isUseStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement);
		
		$typeName = $codeParts[1];
		$type = null;
		$alias = null;
		if (count($codeParts) > 2) {
			switch ($codeParts[2]) {
				case PhpUse::TYPE_CONST:
				case PhpUse::TYPE_FUNCTION:
					$type = $codeParts[2];
					if (count($codeParts) > 4) {
						$alias = $codeParts[4];
					}
					break;
				case Phpbob::KEYWORD_AS:
					if (count($codeParts) > 3) {
						$alias = $codeParts[3];
					}
			}
		}
		
		$this->determinePhpNamespaceElementCreator()->createPhpUse($typeName, $alias, $type);
	}
	
	/**
	 * @return PhpNamespaceElementCreator
	 */
	private function determinePhpNamespaceElementCreator() {
		if (null !== $this->currentNamespace) return $this->currentNamespace;
		
		return $this->phpFile;
	}
	
	private function buildTypeDef(string $localName = null) {
		if (null === $localName) return null;
		
		return new PhpTypeDef($localName, $this->determineTypeName($localName));
	}
	
	private function determineTypeName(string $localName) {
		$nec = $this->determinePhpNamespaceElementCreator();
		try {
			return $nec->determineTypeName($localName);
		} catch (\phpbob\representation\ex\DuplicateElementException $e) {
			throw new PhpSourceAnalyzingException('Invalid local name: ' . $localName, null, $e);
		}
	}
	
	private function applyPhpConst(PhpType $phpType, PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhpbobAnalyzingUtils::isConstStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement, true);
		//shift "const"
		array_shift($codeParts);
		$name = array_shift($codeParts);
		$value = implode(' ', $codeParts);
		$phpConst = $phpType->createPhpConst($name, $value);
		
		$phpConst->setPrependingCode($this->createPrependingCode($phpStatement));
	}
	
	private function applyPhpProperty(PhpClassLike $phpClassLike, PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhpbobAnalyzingUtils::isPropertyStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement, true);
		
		$classifier = null;
		$name = null;
		$value = null;
		$static = false;
		
		foreach ($codeParts as $codePart) {
			if (null === $classifier) {
				$classifier = $codePart;
				continue;
			}
			
			if (null === $name) {
				if (strtolower($codePart) == Phpbob::KEYWORD_STATIC) {
					$static = true;
				} else {
					$name = PhpbobAnalyzingUtils::purifyPropertyName($codePart);
				}
				continue;
			}
			
			if (null === $value) {
				$value = $codePart;
				continue;
			}
			
			$value .= ' ' . $codePart;
		}
		
		$phpClassLike->createPhpProperty($name, $classifier)->setValue($value)
				->setStatic($static)->setPrependingCode($this->createPrependingCode($phpStatement));
	}
	
	private function applyPhpInterfaceMethod(PhpInterface $phpInterface, PhpStatement $phpStatement) {
		$phpMethodDef = PhpMethodDef::fromPhpStatement($phpStatement);
		
		$phpInterfaceMethod = $phpInterface->createPhpInterfaceMethod($phpMethodDef->getMethodName())
				->setStatic($phpMethodDef->isStatic())
				->setReturnPhpTypeDef($this->buildTypeDef($phpMethodDef->getReturnTypeName()));
		
		$this->applyMethodParameters($phpInterfaceMethod, $phpMethodDef->getParameterSignature());
	}
	
	private function applyPhpMethod(PhpClassLike $phpClassLike, PhpStatement $phpStatement) {
		$phpMethodDef = PhpMethodDef::fromPhpStatement($phpStatement); 
		
		$phpMethod = $phpClassLike->createPhpMethod($phpMethodDef->getMethodName())
				->setFinal($phpMethodDef->isFinal())
				->setStatic($phpMethodDef->isStatic())
				->setAbstract($phpMethodDef->isAbstract())
				->setClassifier($phpMethodDef->getClassifier())
				->setMethodCode($phpMethodDef->getMethodCode())
				->setPrependingCode($this->createPrependingCode($phpStatement))
				->setReturnPhpTypeDef($this->buildTypeDef($phpMethodDef->getReturnTypeName()));
		
		$this->applyMethodParameters($phpMethod, $phpMethodDef->getParameterSignature());
	}
	
	private function applyMethodParameters(PhpParamContainer $phpParamContainer, string $signature) {
		if (empty($signature)) return;
		
		foreach (preg_split('/\s*,\s*/', $signature) as $parameter) {
			$parameterParts = self::determineCodePartsForString(str_replace('=', '', $parameter));
			
			if (count($parameterParts) > 3) {
				throw new \InvalidArgumentException('Invalid Number of Parameter Parts in Parameter: ' 
						. $parameter . ' in Method :' . $phpParamContainer);
			}
			
			$parameterName = null;
			$typeName = null;
			$value = null;
			$splat = false;
			$valueNullable = false;
			
			foreach ($parameterParts as $parameterPart) {
				if (StringUtils::startsWith(Phpbob::OPTIONAL_INDICATOR, $parameterPart)) {
					$valueNullable = true;
					if (StringUtils::endsWith(Phpbob::OPTIONAL_INDICATOR, $parameterPart)) continue;
					
					$parameterPart = substr($parameterPart, strlen(Phpbob::OPTIONAL_INDICATOR));
				}
				
				if (StringUtils::startsWith(Phpbob::SPLAT_INDICATOR, $parameterPart)) {
					$splat = true;
					if (StringUtils::endsWith(Phpbob::SPLAT_INDICATOR, $parameterPart)) continue;
					
					$parameterPart = substr($parameterPart, strlen(Phpbob::SPLAT_INDICATOR));
				}
				
				if (StringUtils::endsWith(Phpbob::SPLAT_INDICATOR, $parameterPart)) {
					$splat = true;
					$parameterPart = substr($parameterPart, 0, -strlen(Phpbob::SPLAT_INDICATOR));
				}
				
				if (StringUtils::startsWith(Phpbob::VARIABLE_PREFIX, $parameterPart) 
						|| StringUtils::startsWith(Phpbob::VARIABLE_REFERENCE_PREFIX . Phpbob::VARIABLE_PREFIX, $parameterPart)) {
					$parameterName = $parameterPart;
					continue;
				}
				
				if (null === $parameterName) {
					$typeName = $parameterPart;
				} else {
					$value = $parameterPart;
				}
			}
			
			if (null === $parameterName) {
				throw new PhpSourceAnalyzingException('Invalid method parameter: ' . $parameter . ' in method ' . "\n" . $phpParamContainer);
			}
			
			$param = $phpParamContainer->createPhpParam(PhpbobAnalyzingUtils::purifyPropertyName($parameterName),
					$value, $this->buildTypeDef($typeName), $splat);
			
			$param->setPassedByReference(StringUtils::startsWith(Phpbob::VARIABLE_REFERENCE_PREFIX, $parameterName));
			$param->setValueNullable($valueNullable);
		}
	}
	
	private function applyTraitUse(PhpClassLike $phpClassLike, PhpStatement $phpStatement) {
		ArgUtils::assertTrue(PhpbobAnalyzingUtils::isTraitUseStatement($phpStatement));
		$codeParts = self::determineCodeParts($phpStatement);
		//shift use
		array_shift($codeParts);
		
		foreach ($codeParts as $codePart) {
			foreach (array_filter(explode(',', $codePart)) as $traitName) {
				$phpClassLike->createPhpTraitUse($this->determineTypeName($traitName), $traitName);
			}
		}
	}
	
	private function applyAnnotationSet(PhpClassLike $phpClassLike, PhpStatement $phpStatement) {
		$annoSetAnalyzer = new PhpAnnoSetAnalyzer($phpClassLike);
		$annoSetAnalyzer->analyze($phpStatement);
	}
	
	private static function determineCodeParts(PhpStatement $phpStatement, bool $replaceAssignment = false) {
		$str = trim(str_replace(Phpbob::SINGLE_STATEMENT_STOP, '', $phpStatement->getCode()));
		if ($replaceAssignment) {
			$str = str_replace(Phpbob::ASSIGNMENT, '', $str);
		}
		
		return self::determineCodePartsForString($str);
	}
	
	private static function determineCodePartsForString($string) {
		if (StringUtils::isEmpty($string)) return array();
		
		$codeParts = array();
		$currentCodePart = null;
		$stringDelimiter = null;
		
		foreach (str_split($string) as $token) {
			if (null !== $stringDelimiter) {
				$currentCodePart .= $token;
				if ($token == $stringDelimiter) {
					$stringDelimiter = null;
				}
				continue;
			}
			
			if ($token == '"' || $token == "'") {
				$currentCodePart .= $token;
				$stringDelimiter = $token;
				continue;
			}
			
			if (StringUtils::isEmpty($token)) {
				if (null !== $currentCodePart) {
					$codeParts[] = $currentCodePart;
					$currentCodePart = null;
				}
				continue;
			}
			
			$currentCodePart .= $token;
		}
		if (null !== $currentCodePart) {
			$codeParts[] = $currentCodePart;
		}
		
		return $codeParts;
	}
	
	private function createPrependingCode(PhpStatement $phpStatement) {
		return implode(PHP_EOL, $phpStatement->getPrependingCommentLines());
	}
}

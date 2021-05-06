<?php
namespace phpbob\analyze;

use phpbob\Phpbob;
use n2n\util\StringUtils;
use n2n\util\type\ArgUtils;

class PhpAnnoAnalyzer {

	/**
	 * @param string $paramString
	 * @param array $variableDefinitions
	 * @throws PhpAnnotationSourceAnalyzingException
	 * @throws PhpSourceAnalyzingException
	 * @return \phpbob\analyze\PhpAnnoDef[]
	 */
	public function analyze(string $paramString, array $variableDefinitions) {
		ArgUtils::valArray($variableDefinitions, PhpAnnoDef::class);
		$annos = array();
	
		foreach (self::determineFirstLevelParamStrings($paramString) as $annoParamString) {
			if ($this->isNewClass($annoParamString)) {
				$annos[] = self::createPhpAnnoDef($annoParamString);
				continue;
			}
				
			if ($this->isVariable($annoParamString)) {
				if (!isset($variableDefinitions[$annoParamString])) {
					throw new PhpAnnotationSourceAnalyzingException('Invalid variable . ' . $annoParamString);
				}
	
				$annos[] = $variableDefinitions[$annoParamString];
				continue;
			}
				
			throw new PhpSourceAnalyzingException('Invalid anno string: ' . $annoParamString);
		}
	
		return $annos;
	}
	
	private static function determineFirstLevelParamStrings($paramString) {
		$level = 0;
		$annoParamStrings = array();
		$annoParamString = '';
		
		foreach (str_split((string) $paramString) as $char) {
			switch ($char) {
				case Phpbob::PARAMETER_GROUP_START:
					$level++;
					break;
				case Phpbob::PARAMETER_GROUP_END:
					$level--;
					break;
				default:
			}
			
			if ($level < 0) {
				throw new PhpSourceAnalyzingException('Invalid param String: ' . $paramString . '. Too many closing groups.');
			}
			
			if ($level === 0 && $char === Phpbob::PARAMETER_SEPERATOR) {
				$annoParamStrings[] = trim($annoParamString);
				$annoParamString = '';
				continue;
			}
			
			$annoParamString .= $char;
		}
		
		if (!StringUtils::isEmpty($annoParamString)) {
			$annoParamStrings[] = trim($annoParamString);
		}
		
		return $annoParamStrings;
	}
	
	public static function createPhpAnnoDef(string $newClassString) {
		$typeName = null;
		$constructorString = null;
		$level = 0;
		
		$newClassString = preg_replace('/^' . Phpbob::KEYWORD_NEW . '\s+/', '', $newClassString);
		//remove whitespaces before and after the brackets
		$newClassString = preg_replace('/\s*(' . preg_quote(Phpbob::PARAMETER_GROUP_START) . '|' 
				. preg_quote(Phpbob::PARAMETER_GROUP_END) . ')\s*/', '$1', $newClassString);
		
		foreach (str_split((string) $newClassString) as $char) {
			switch ($char) {
				case Phpbob::PARAMETER_GROUP_START:
					$level++;
					break;
				case Phpbob::PARAMETER_GROUP_END:
					$level--;
					break;
				default:
			}
			
			if ($level < 0) {
				throw new PhpSourceAnalyzingException('Invalid new class String' . $newClassString . '. Too many closing groups.');
			}
			
			if ($level === 0) {
				if ($char === Phpbob::PARAMETER_GROUP_END) continue;

				if (null !== $constructorString) {
					throw new PhpSourceAnalyzingException('invalid new Class statement:' . $newClassString);
				}
				
				$typeName .= $char;
				continue;
			}
			
			if ($level === 1 && $char == Phpbob::PARAMETER_GROUP_START) continue;
			
			$constructorString .= $char;
		}
		
		return new PhpAnnoDef($typeName, self::buildConstructorParams($constructorString));
	}
	
	private static function buildConstructorParams(string $constructor = null) {
		if (null === $constructor) return [];
		
		$constructorParams = [];
		foreach (self::determineFirstLevelParamStrings($constructor) as $constructorPart) {
			$constructorParams[] = $constructorPart;
		}
		
		return $constructorParams;
	}
	
	private function isNewClass($string) {
		return StringUtils::startsWith(Phpbob::KEYWORD_NEW, ltrim($string));
	}
	
	private function isVariable($string) {
		return StringUtils::startsWith(Phpbob::VARIABLE_PREFIX, ltrim($string));
	}
}
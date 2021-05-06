<?php
namespace phpbob\analyze;

use phpbob\PhpStatement;
use phpbob\StatementGroup;
use phpbob\Phpbob;
use n2n\util\StringUtils;
use phpbob\SingleStatement;

class PhpbobAnalyzingUtils {
	
	public static function isClassStatement(PhpStatement $phpStatement) {
		if (!($phpStatement instanceof StatementGroup)) return false;
		
		return preg_match('/^(' . preg_quote(Phpbob::KEYWORD_FINAL) .  '\s+)?('
				. preg_quote(Phpbob::KEYWORD_ABSTRACT) . '\s+)?'
				. preg_quote(Phpbob::KEYWORD_CLASS). '/i', ltrim($phpStatement->getCode()));
	}
	
	public static function isTypeStatement(PhpStatement $phpStatement) {
		return self::isClassStatement($phpStatement) || self::isInterfaceStatement($phpStatement)
				|| self::isTraitStatement($phpStatement);
	}
	
	public static function isInterfaceStatement(PhpStatement $phpStatement) {
		if (!($phpStatement instanceof StatementGroup)) return false;
		
		return StringUtils::startsWith(Phpbob::KEYWORD_INTERFACE,
				ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isTraitStatement(PhpStatement $phpStatement) {
		if (!($phpStatement instanceof StatementGroup)) return false;
		
		return StringUtils::startsWith(Phpbob::KEYWORD_TRAIT,
				ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isPropertyStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement
				&& preg_match('/(' . preg_quote(Phpbob::CLASSIFIER_PRIVATE) .
				'|' . preg_quote(Phpbob::CLASSIFIER_PROTECTED) .
				'|' . preg_quote(Phpbob::CLASSIFIER_PUBLIC) . ')\s+('
				. preg_quote(Phpbob::KEYWORD_STATIC) . '\s+)?' .
				preg_quote(Phpbob::VARIABLE_PREFIX) . '/i', $phpStatement->getCode());
	}
	
	public static function isConstStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement
				&& StringUtils::startsWith(Phpbob::KEYWORD_CONST, ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isAnnotationStatement(PhpStatement $phpStatement) {
		return self::isMethodStatement($phpStatement)
				&& preg_match('/private\s+static\s+function\s+_annos.*\(.*\)/i',
						$phpStatement->getCode());
	}
	
	public static function isMethodStatement(PhpStatement $phpStatement) {
		return !!preg_match('/' . preg_quote(Phpbob::KEYWORD_FUNCTION)
				. '.*\(.*\)/i', $phpStatement->getCode());
	}
	
	public static function isNamespaceStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement
				&& preg_match('/^\s*' . preg_quote(Phpbob::KEYWORD_NAMESPACE) . '\s+/i', $phpStatement->getCode());
	}
	
	public static function isUseStatement(PhpStatement $phpStatement) {
		return $phpStatement instanceof SingleStatement
				&& StringUtils::startsWith(Phpbob::KEYWORD_USE, ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function isTraitUseStatement(PhpStatement $phpStatement) {
		return StringUtils::startsWith(Phpbob::KEYWORD_USE, ltrim(strtolower($phpStatement->getCode())));
	}
	
	public static function purifyPropertyName($propertyName) {
		return str_replace(array(Phpbob::VARIABLE_PREFIX, Phpbob::VARIABLE_REFERENCE_PREFIX), '', $propertyName);
	}
}
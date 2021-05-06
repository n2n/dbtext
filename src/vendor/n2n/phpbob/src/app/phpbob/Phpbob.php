<?php
namespace phpbob;

class Phpbob {
	const NAMESPACE_SEPERATOR = '\\';
	const KEYWORD_USE = 'use';
	const CONST_SEPERATOR = '::';
	const PHP_BLOCK_BEGIN = '<?php';
	const ASSIGNMENT = '=';
	const KEYWORD_FUNCTION = 'function';
	
	const CLASSIFIER_PRIVATE ='private';
	const CLASSIFIER_PROTECTED = 'protected';
	const CLASSIFIER_PUBLIC = 'public';

	const VARIABLE_PREFIX = '$';
	const VARIABLE_REFERENCE_PREFIX = '&';
	const KEYWORD_CLASS = 'class';
	const KEYWORD_INTERFACE = 'interface';
	const KEYWORD_TRAIT = 'trait';
	const KEYWORD_EXTENDS = 'extends';
	const KEYWORD_IMPLEMENTS = 'implements';
	const KEYWORD_STATIC = 'static';
	const KEYWORD_FINAL = 'final';
	const KEYWORD_ABSTRACT = 'abstract';
	const KEYWORD_AS = 'as';
	const KEYWORD_CONST = 'const';
	const KEYWORD_NEW = 'new';
	const KEYWORD_NAMESPACE = 'namespace';
	const KEYWORD_RETURN = 'return';
	const KEYWORD_NULL = 'null';
	const SPLAT_INDICATOR = '...';
	const OPTIONAL_INDICATOR = '?';
	
	const SINGLE_STATEMENT_STOP = ';';
	const GROUP_STATEMENT_OPEN = '{';
	const GROUP_STATEMENT_CLOSE = '}';
	const PARAMETER_GROUP_START = '(';
	const PARAMETER_GROUP_END = ')';
	const PARAMETER_SEPERATOR = ',';
	const STRING_LITERAL_SEPERATOR = '\'';
	const STRING_LITERAL_ALTERNATIVE_SEPERATOR = '"';
	const RETURN_TYPE_INDICATOR = ':';
	
	const MULTILINE_COMMENT_START = '/*';
	const MULTILINE_COMMENT_END = '*/';
	const SINGLE_COMMENT_START = '//';
	const PHP_FILE_EXTENSION = '.php';
	
	//@see: https://www.php.net/manual/de/functions.arguments.php#functions.arguments.type-declaration
	const TYPE_BOOLEAN = 'bool';
	const TYPE_INTEGER = 'int';
	const TYPE_STRING = 'string';
	const TYPE_FLOAT = 'float';
	const TYPE_ARRAY = 'array';
	const TYPE_CALLABLE = 'callable';
	const TYPE_ITERABLE = 'iterable';
	const TYPE_OBJECT = 'object';
	const TYPE_SELF = 'self';
	
	public static function getClassifiers() {
		return array(self::CLASSIFIER_PRIVATE, self::CLASSIFIER_PROTECTED, self::CLASSIFIER_PUBLIC);
	}
	
	public static function getTypes() {
		return [self::TYPE_BOOLEAN, self::TYPE_INTEGER, self::TYPE_STRING, self::TYPE_FLOAT, self::TYPE_ARRAY, 
				self::TYPE_CALLABLE, self::TYPE_ITERABLE, self::TYPE_OBJECT, self::TYPE_OBJECT];
	}
}
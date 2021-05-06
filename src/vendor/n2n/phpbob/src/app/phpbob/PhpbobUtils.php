<?php
namespace phpbob;

use phpbob\analyze\PhpSourceAnalyzer;
use n2n\io\IoUtils;
use n2n\core\TypeLoader;
use n2n\util\type\ArgUtils;
use phpbob\representation\PhpClass;
use n2n\io\fs\FsPath;

class PhpbobUtils {
// 	public static function typeNameToPath($typeName) {
// 		return str_replace(Phpbob::NAMESPACE_SEPERATOR, DIRECTORY_SEPARATOR, trim($typeName, Phpbob::NAMESPACE_SEPERATOR)) 
// 				. Phpbob::PHP_FILE_EXTENSION;
// 	}
	
	public static function extractClassName(string $typeName) {
		if (false === $pos = mb_strrpos($typeName, Phpbob::NAMESPACE_SEPERATOR)) {
			return $typeName;
		}
		
		return mb_substr($typeName, $pos + 1); 
	}
	
	public static function isInRootNamespace(string $typeName) {
		return mb_strrpos($typeName, Phpbob::NAMESPACE_SEPERATOR) === 0;
	}
	
	public static function extractNamespace(string $typeName) {
		$lastPos = strrpos($typeName, Phpbob::NAMESPACE_SEPERATOR);
		if (false === $lastPos) return null;
		
		return mb_substr($typeName, 0, $lastPos);
	}
	
	public static function explodeTypeName(string $typename) {
		return explode(Phpbob::NAMESPACE_SEPERATOR, $typename);	
	}
	
// 	public static function isString($value) {
// 		return StringUtils::startsWith(Phpbob::STRING_LITERAL_SEPERATOR, $value) 
// 				|| StringUtils::startsWith(Phpbob::STRING_LITERAL_ALTERNATIVE_SEPERATOR, $value);
// 	}
	
// 	public static function determinSetterMethodName($propertyName) {
// 		return 'set' . ucfirst((string) $propertyName);
// 	}

// 	public static function determineGetterMethodName($propertyName, $boolean = false) {
// 		return (($boolean) ? 'is' : 'get') . ucfirst((string) $propertyName);
// 	}

	public static function removeLeadingWhiteSpaces($string) {
		return preg_replace('/^\s*/', '', (string) $string);
	}

	public static function removeLeadingEOLs($string) {
		return preg_replace('/^' . preg_quote(PHP_EOL) . '*/', '', (string) $string);
	}

	public static function removeTailingWhiteSpaces($string) {
		return preg_replace('/\s*$/', '', (string) $string);
	}
	
	public static function trimWhiteSpaces($string) {
		return preg_replace('/(^\s*|^\s*)/', '', (string) $string);
	}
	
	/**
	 * @param string $typeName
	 * @return \phpbob\representation\PhpClass
	 */
	public static function createPhpClass(string $typeName/* , AnnotationSet $as = null */) {
		$analyzer = new PhpSourceAnalyzer();
		
		$phpFile = $analyzer->analyze(IoUtils::getContents(TypeLoader::getFilePathOfType($typeName)));
		$phpClass = $phpFile->getPhpNamespace(self::extractNamespace($typeName))
				->getPhpType(self::extractClassName($typeName));
	
		ArgUtils::assertTrue($phpClass instanceof PhpClass);
				
		return $phpClass;
	}
	
	public static function savePhpClass(PhpClass $phpClass, FsPath $fsPath = null) {
		$fsPath = $fsPath ?? new FsPath(TypeLoader::getFilePathOfType($phpClass->getTypeName()));
		
		IoUtils::putContentsSafe($fsPath, $phpClass->getPhpFile()->getStringRepresentation());
	}
	
	public static function isSimpleType(string $type) {
		return in_array($type, Phpbob::getTypes());
	}
	
// 	private static function isClassifier($s) {
// 		return preg_match('/^(private|protected|public)$/i');
// 	}
}
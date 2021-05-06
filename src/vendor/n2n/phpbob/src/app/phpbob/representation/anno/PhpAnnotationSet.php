<?php
namespace phpbob\representation\anno;

use phpbob\Phpbob;
use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\PhpClass;
use phpbob\representation\PhpTypeDef;
use n2n\reflection\annotation\AnnoInit;
use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;
use n2n\reflection\annotation\AnnotationSet;

class PhpAnnotationSet {
	use PrependingCodeTrait;
	
	const DEFAULT_ANNO_INIT_VARIABLE_NAME = '$ai';
	const ANNO_METHOD_NAME = '_annos';
	const ANNO_METHOD_SIGNATURE = 'private static function ' . self::ANNO_METHOD_NAME . '(AnnoInit ';
	
	private $phpClass;
	private $aiVariableName = '$ai';
	private $phpPropertyAnnoCollections = array();
	private $phpMethodAnnoCollections = array();
	private $phpClassAnnoCollection;
	private $annotationSet;
	
	public function __construct(PhpClass $phpClass) {
		$this->phpClass = $phpClass;
	}
	
	public function getAiVariableName() {
		return $this->aiVariableName;
	}

	public function setAiVariableName(string $aiVariableName) {
		$this->aiVariableName = $aiVariableName;
	}
	
	public function isEmpty() {
		return null === $this->phpClassAnnoCollection && empty($this->phpPropertyAnnoCollections) 
				&& empty($this->phpMethodAnnoCollections); 
	}
	
	/**
	 * @param string $methodName
	 * @return bool
	 */
	public function hasPhpMethodAnnoCollection(string $methodName) {
		return isset($this->phpMethodAnnoCollections[$methodName]);
	}
	
	/**
	 * @param string $name
	 * @return PhpMethodAnnoCollection
	 */
	public function getPhpMethodAnnoCollection(string $methodName) {
		if (!isset($this->phpMethodAnnoCollections[$methodName])) {
			throw new UnknownElementException('No function with name "' . $methodName . '" given.');
		}
		
		return $this->phpMethodAnnoCollections[$methodName];
	}
	
	/**
	 * @return PhpMethodAnnoCollection []
	 */
	public function getPhpMethodAnnoCollections() {
		return $this->phpMethodAnnoCollections;
	}
	
	public function assignAnnotationSet(AnnotationSet $annotationSet = null) {
		$this->annotationSet = $annotationSet;
	}
	
	public function isAnnotationSetAssigned() {
		return null !== $this->annotationSet;
	}
	
	public function getAnnotationSet() {
		return $this->annotationSet;
	}
	
	/**
	 * @param string $methodName
	 * @param PhpTypeDef $returnPhpTypeDef
	 * @throws IllegalStateException
	 * @return PhpMethodAnnoCollection
	 */
	public function createPhpMethodAnnoCollection(string $methodName) {
		$this->checkPhpMethodName($methodName);
		
		$phpMethodAnnoCollection = new PhpMethodAnnoCollection($this, $methodName);
		
		$that = $this;
		$phpMethodAnnoCollection->onMethodNameChange(function($oldMethodName, $newMethodName) use ($that) {
			$that->checkPhpMethodName($newMethodName);
			
			$tmpPhpMethodAnnoCollection = $that->phpMethodAnnoCollections[$oldMethodName];
			unset($that->phpMethodAnnoCollections[$oldMethodName]);
			$that->phpMethodAnnoCollections[$newMethodName] = $tmpPhpMethodAnnoCollection;
			
		});
			
		$this->phpMethodAnnoCollections[$methodName] = $phpMethodAnnoCollection;
		return $phpMethodAnnoCollection;
	}
	
	/**
	 * @param string $methodName
	 * @return PhpMethodAnnoCollection
	 */
	public function getOrCreatePhpMethodAnnoCollection(string $methodName) {
		if (!$this->hasPhpMethodAnnoCollection($methodName)) return $this->createPhpMethodAnnoCollection($methodName);
		
		return $this->phpMethodAnnoCollections[$methodName];
	}
	
	/**
	 * @param string $methodName
	 */
	public function removePhpMethodAnnoCollection(string $methodName) {
		unset($this->phpMethodAnnoCollections[$methodName]);
		
		return $this;
	}
	
	private function checkPhpMethodName(string $methodName) {
		if ($this->hasPhpMethodAnnoCollection($methodName)) {
			throw new IllegalStateException('Method Anno Collection with name ' . $methodName . ' already defined.');
		}
	}
	
	/**
	 * @param string $propertyName
	 * @return bool
	 */
	public function hasPhpPropertyAnnoCollection(string $propertyName) {
		return isset($this->phpPropertyAnnoCollections[$propertyName]);
	}
	
	/**
	 * @param string $propertyName
	 * @return PhpPropertyAnnoCollection
	 */
	public function getPhpPropertyAnnoCollection(string $propertyName) {
		if (!isset($this->phpPropertyAnnoCollections[$propertyName])) {
			throw new UnknownElementException('No property anno with name "' . $propertyName . '" given.');
		}
		
		return $this->phpPropertyAnnoCollections[$propertyName];
	}
	
	/**
	 * @return PhpPropertyAnnoCollection []
	 */
	public function getPhpPropertyAnnoCollections() {
		return $this->phpPropertyAnnoCollections;
	}
	
	/**
	 * @param string $methodName
	 * @param PhpTypeDef $returnPhpTypeDef
	 * @throws IllegalStateException
	 * @return PhpPropertyAnnoCollection
	 */
	public function createPhpPropertyAnnoCollection(string $propertyName) {
		$this->checkPhpPropetyName($propertyName);
		
		$phpPropertyAnnoCollection = new PhpPropertyAnnoCollection($this, $propertyName);
		
		$that = $this;
		$phpPropertyAnnoCollection->onPropertyNameChange(function($oldPropertyName, $newPropertyName) use ($that) {
			$that->checkPhpPropetyName($newPropertyName);
			
			$tmpPhpPropertyAnnoCollection = $that->phpPropertyAnnoCollections[$oldPropertyName];
			unset($that->phpPropertyAnnoCollections[$oldPropertyName]);
			$that->phpPropertyAnnoCollections[$newPropertyName] = $tmpPhpPropertyAnnoCollection;
			
		});
			
		$this->phpPropertyAnnoCollections[$propertyName] = $phpPropertyAnnoCollection;
		return $phpPropertyAnnoCollection;
	}
	
	/**
	 * @param string $propertyName
	 * @return PhpPropertyAnnoCollection
	 */
	public function getOrCreatePhpPropertyAnnoCollection(string $propertyName) {
		if (!$this->hasPhpPropertyAnnoCollection($propertyName)) return $this->createPhpPropertyAnnoCollection($propertyName); 
		
		return $this->phpPropertyAnnoCollections[$propertyName];
	}
	
	/**
	 * @param string $methodName
	 */
	public function removePhpPropertyAnnoCollection(string $propertyName) {
		unset($this->phpPropertyAnnoCollections[$propertyName]);
		
		return $this;
	}
	
	private function checkPhpPropetyName(string $propertyName) {
		if ($this->hasPhpPropertyAnnoCollection($propertyName)) {
			throw new IllegalStateException('Method Collection with name ' . $propertyName . ' already defined.');
		}
	}
	
	/**
	 * @param string $propertyName
	 * @return bool
	 */
	public function hasPhpClassAnnoCollection() {
		return null !== $this->phpClassAnnoCollection;
	}
	
	/**
	 * @param string $propertyName
	 * @return PhpPropertyAnnoCollection
	 */
	public function getPhpClassAnnoCollection() {
		if (null === $this->phpClassAnnoCollection) {
			throw new UnknownElementException('No class anno Collection given.');
		}
		
		return $this->phpClassAnnoCollection;
	}
	
	/**
	 * @param string $methodName
	 * @param PhpTypeDef $returnPhpTypeDef
	 * @throws IllegalStateException
	 * @return PhpClassAnnotationCollection
	 */
	public function createPhpClassAnnoCollection() {
		if (null !== $this->phpClassAnnoCollection) {
			throw new IllegalStateException('Duplicate class annotation');
		}
		
		return $this->phpClassAnnoCollection = new PhpClassAnnotationCollection($this);
	}
	
	public function getOrCreatePhpClassAnnoCollection() {
		if (!$this->hasPhpClassAnnoCollection()) return $this->createPhpClassAnnoCollection();
		
		return $this->phpClassAnnoCollection;
	}
	
	/**
	 * @param string $methodName
	 */
	public function removePhpClassAnnoCollection() {
		$this->phpClassAnnoCollection = null;
		
		return $this;
	}
	
// 	public function applyTypeNames() {
// 		foreach ($this->propertyAnnoCollections as $anno) {
// 			$this->checkTypeNames($anno);
// 		}
		
// 		foreach ($this->methodAnnoCollections as $anno) {
// 			$this->checkTypeNames($anno);
// 		}
		
// 		if (null !== $this->classAnnoCollection) {
// 			$this->checkTypeNames($this->classAnnoCollection);
// 		}
// 	}
	
	public function __toString() {
		if ($this->isEmpty()) return $this->getPrependingString();
		$string = $this->getPrependingString() . "\t" . self::ANNO_METHOD_SIGNATURE . $this->aiVariableName . ') ' 
				. Phpbob::GROUP_STATEMENT_OPEN . PHP_EOL;
		
		if (null !== $this->phpClassAnnoCollection) {
			$string .= $this->phpClassAnnoCollection; 
		}
		
		foreach ($this->phpMethodAnnoCollections as $methodAnnoCollection) {
			$string .= $methodAnnoCollection; 
		}
		
		foreach ($this->phpPropertyAnnoCollections as $propertyAnnoCollection) {
			$string .= $propertyAnnoCollection; 
		}
		
		return $string . "\t" . Phpbob::GROUP_STATEMENT_CLOSE . PHP_EOL;
	}
	
// 	public static function createAnnoInitUse() {
// 		return new PhpUse('n2n\reflection\annotation\AnnoInit');
// 	}
	
	public function getPhpTypeDefs() {
		$phpTypeDefs = [PhpTypeDef::fromTypeName(AnnoInit::class)];
		
		if (null !== $this->phpClassAnnoCollection) {
			$phpTypeDefs = array_merge($phpTypeDefs, $this->phpClassAnnoCollection->getPhpTypeDefs());
		}
		
		foreach ($this->phpPropertyAnnoCollections as $phpPropertyAnnoCollection) {
			$phpTypeDefs = array_merge($phpTypeDefs, $phpPropertyAnnoCollection->getPhpTypeDefs());
		}
		
		foreach ($this->phpMethodAnnoCollections as $phpMethodAnnoCollection) {
			$phpTypeDefs = array_merge($phpTypeDefs, $phpMethodAnnoCollection->getPhpTypeDefs());
		}
		
		return $phpTypeDefs;
	}
	
	public function createPhpUse(string $typeName,
			string $alias = null, string $type = null) {
		return $this->phpClass->createPhpUse($typeName, $alias, $type);
	}
	
	public function getPhpClass() {
		return $this->phpClass;
	}
	
// 	private function checkTypeNames(PhpAnno $phpAnno) {
// 		foreach ($phpAnno->getParams() as $annoParam) {
// 			$typeName = $annoParam->getTypeName();
// 			if (null === PhpbobUtils::extractNamespace($typeName)) return;
// 			$this->phpClass->addUse(new PhpUse($typeName));
// 			$annoParam->setTypeName(PhpbobUtils::extractClassName($typeName));
// 		}
// 	} 
}
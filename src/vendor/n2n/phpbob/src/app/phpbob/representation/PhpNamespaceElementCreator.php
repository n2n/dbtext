<?php
namespace phpbob\representation;

use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;

abstract class PhpNamespaceElementCreator {

	protected $phpElementFactory;
	
	public function __construct(PhpElementFactory $phpElementFactory) {
		$this->phpElementFactory = $phpElementFactory;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpFunction(string $name) {
		return $this->phpElementFactory->hasPhpFunction($name);
	}
	
	/**
	 * @param string $name
	 * @return PhpFunction
	 */
	public function getPhpFunction(string $name) {
		return $this->phpElementFactory->getPhpFunction($name);
	}
	
	/**
	 * @return PhpFunction []
	 */
	public function getPhpFunctions() {
		return $this->phpElementFactory->getPhpFunctions();
	}
	
	/**
	 * @param string $name
	 * @param PhpTypeDef $returnPhpTypeDef
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpFunction
	 */
	public function createPhpFunction(string $name, PhpTypeDef $returnPhpTypeDef = null) {
		return $this->phpElementFactory->createPhpFunction($name, $returnPhpTypeDef);
	}
	
	/**
	 * @param string $name
	 */
	public function removePhpFunction(string $name) {
		$this->phpElementFactory->removePhpFunction($name);
	
		return $this;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpConst(string $name) {
		return $this->phpElementFactory->hasPhpConst($name);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpConst(string $name) {
		return $this->phpElementFactory->getPhpConst($name);
	}
	
	/**
	 * @return PhpConst []
	 */
	public function getPhpConsts() {
		return $this->phpElementFactory->getPhpConsts();
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpConst
	 */
	public function createPhpConst(string $name, string $value) {
		$this->phpElementFactory->createPhpConst($name, $value);
	}
	
	public function removePhpConst(string $name) {
		$this->phpElementFactory->removePhpConst($name);
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpType(string $name) {
		return $this->phpElementFactory->hasPhpType($name);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpType
	 */
	public function getPhpType(string $name) {
		return $this->phpElementFactory->getPhpType($name);
	}
	
	/**
	 * @return PhpType[]
	 */
	public function getPhpTypes() {
		return $this->phpElementFactory->getPhpTypes();
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpInterface
	 */
	public function createPhpInterface(string $name) {
		return $this->phpElementFactory->createPhpInterface($name);
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpTrait
	 */
	public function createPhpTrait(string $name) {
		return $this->phpElementFactory->createPhpTrait($name);
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpClass
	 */
	public function createPhpClass(string $name) {
		return $this->phpElementFactory->createPhpClass($name);
	}
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpFile
	 */
	public function removePhpType(string $name) {
		$this->phpElementFactory->removePhpType($name);
	
		return $this;
	}

	/**
	 * @param string $code
	 * @return \phpbob\representation\UnknownPhpCode
	 */
	public function createUnknownPhpCode(string $code) {
		return $this->phpElementFactory->createUnknownPhpCode($code);
	}
	
	/**
	 * @param string $typeName
	 * @return bool
	 */
	public function hasPhpUse(string $typeName) {
		return $this->phpElementFactory->hasPhpUse($typeName);
	}
	
	/**
	 * @param string $typeName
	 * @throws UnknownElementException
	 * @return PhpUse
	 */
	public function getPhpUse(string $typeName) {
		return $this->phpElementFactory->getPhpUse($typeName);
	}
	
	public function determineTypeName(string $localName) {
		return $this->phpElementFactory->determineTypeName($localName);
	}
 	
	/**
	 * @return PhpUse []
	 */
	public function getPhpUses() {
		return $this->phpElementFactory->getPhpUses();
	}
	
	/**
	 * @param string $typeName
	 * @param string $alias
	 * @param string $type
	 * @return \phpbob\representation\PhpUse|null
	 */
	public function createPhpUse(string $typeName,
			string $alias = null, string $type = null) {
		return $this->phpElementFactory->createPhpUse($typeName, $alias, $type);
	}
	
	/**
	 * @param string $alias
	 * @return boolean
	 */
	public function hasPhpUseAlias(string $alias) {
		return $this->phpElementFactory->hasPhpUseAlias($alias);
	}
	
	/**
	 * @param string $typeName
	 * @return \phpbob\representation\PhpNamespaceElementCreator
	 */
	public function removePhpUse(string $typeName) {
		$this->phpElementFactory->removePhpUse($typeName);
		
		return $this;
	}
}
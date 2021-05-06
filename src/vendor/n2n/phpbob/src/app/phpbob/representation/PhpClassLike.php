<?php
namespace phpbob\representation;

use phpbob\representation\anno\PhpAnnotationSet;
use phpbob\Phpbob;
use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;

interface PhpClassLike extends PhpType {
	/**
	 * @return string
	 */
	public function getTypeName(): string;
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpMethod(string $name): bool;
	
	/**
	 * @param string $name
	 * @return PhpMethod
	 */
	public function getPhpMethod(string $name): PhpMethod;
	
	/**
	 * @return PhpMethod []
	 */
	public function getPhpMethods();
	
	/**
	 * @param string $name
	 * @return PhpMethod
	 */
	public function createPhpMethod(string $name): PhpMethod;
	
	/**
	 * @param string $name
	 * @return PhpClassLike
	 */
	public function removePhpMethod(string $name): PhpClassLike;
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpProperty(string $name): bool;
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpProperty(string $name): PhpProperty;
	
	/**
	 * @return PhpProperty []
	 */
	public function getPhpProperties(): array;
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpProperty
	 */
	public function createPhpProperty(string $name, string $classifier = Phpbob::CLASSIFIER_PRIVATE): PhpProperty;
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpClassLikeAdapter
	 */
	public function removePhpProperty(string $name): PhpClassLike;
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpTraitUse(string $typeName): bool;
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpTraitUse(string $typeName): PhpTraitUse;
	
	/**
	 * @return PhpTraitUse []
	 */
	public function getPhpTraitUses(): array;
	
	/**
	 * @param string $typeName
	 * @param string $localName
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpTraitUse
	 */
	public function createPhpTraitUse(string $typeName, string $localName = null): PhpTraitUse;
	
	/**
	 * @return PhpAnnotationSet
	 */
	public function getPhpAnnotationSet();
	
	/**
	 * @param string $prependingCode
	 */
	public function setPrependingCode(string $prependingCode = null);
	
	/**
	 * @param string $appendingCode
	 */
	public function setAppendingCode(string $appendingCode = null);
	
	/**
	 * @param string $propertyName
	 * @return PhpTypeDef|NULL
	 */
	public function determinePhpTypeDef(string $propertyName): ?PhpTypeDef;
	
	/**
	 * @param string $propertyName
	 * @return PhpTypeDef|NULL
	 */
	public function determineArrayLikePhpTypeDef(string $propertyName): ?PhpTypeDef;
}
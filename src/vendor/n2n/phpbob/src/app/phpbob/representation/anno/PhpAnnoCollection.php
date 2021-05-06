<?php
namespace phpbob\representation\anno;

use n2n\reflection\annotation\Annotation;
use n2n\util\ex\IllegalStateException;
use phpbob\representation\PhpTypeDef;
use phpbob\representation\ex\UnknownElementException;

interface PhpAnnoCollection {
	public function getPhpAnnotationSet(): PhpAnnotationSet;
	/**
	 * @param string $typeName
	 * @return bool
	 */
	public function hasPhpAnno(string $typeName): bool;
	
	/**
	 * @param string $typeName
	 * @throws UnknownElementException
	 * @return PhpAnno
	 */
	public function getPhpAnno(string $typeName): PhpAnno;
	
	/**
	 * @return PhpAnno []
	 */
	public function getPhpAnnos(): array;
	
	/**
	 * @param string $typeName
	 * @param string $value
	 * @throws IllegalStateException
	 * @return PhpAnno
	 */
	public function createPhpAnno(string $typeName, string $localName = null): PhpAnno;
	
	/**
	 * @param string $typeName
	 * @return PhpAnnoCollection
	 */
	public function removePhpAnno(string $typeName): PhpAnnoCollection;
	
	public function resetPhpAnnos();
	
	/**
	 * @return PhpTypeDef []
	 */
	public function getPhpTypeDefs(): array;

	/**
	 * @return bool
	 */
	public function isEmpty(): bool;
	
	/**
	 * @param string $prependingCode
	 */
	public function appendPrependingCode(string $prependingCode = null);
	
	/**
	 * @param PhpAnno $phpAnno
	 * @return Annotation|NULL
	 */
	public function determineAnnotation(PhpAnno $phpAnno): ?Annotation;
	
	public function __toString(): string;
}
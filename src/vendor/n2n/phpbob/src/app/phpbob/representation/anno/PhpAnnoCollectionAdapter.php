<?php
namespace phpbob\representation\anno;

use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;
use phpbob\representation\PhpTypeDef;
use phpbob\PhpbobUtils;

abstract class PhpAnnoCollectionAdapter implements PhpAnnoCollection {
	use PrependingCodeTrait;
	
	protected $phpAnnotationSet;
	protected $phpAnnos = array();
	
	public function __construct(PhpAnnotationSet $phpAnnotationSet, $prependingCode = null) {
		$this->phpAnnotationSet = $phpAnnotationSet;
		$this->prependingCode = $prependingCode;
	}

	/**
	 * @param string $typeName
	 * @return bool
	 */
	public function hasPhpAnno(string $typeName): bool {
		return isset($this->phpAnnos[$typeName]);
	}
	
	/**
	 * @param string $typeName
	 * @throws UnknownElementException
	 * @return PhpAnno
	 */
	public function getPhpAnno(string $typeName): PhpAnno {
		if (!isset($this->phpAnnos[$typeName])) {
			throw new UnknownElementException('No Anno Param with name "' . $typeName . '" given.');
		}
	
		return $this->phpAnnos[$typeName];
	}
	
	/**
	 * @param string $typeName
	 * @throws UnknownElementException
	 * @return PhpAnno
	 */
	public function getOrCreatePhpAnno(string $typeName) {
		if ($this->hasPhpAnno($typeName)) return $this->getPhpAnno($typeName);
	
		return $this->createPhpAnno($typeName);
	}
	
	/**
	 * @return PhpAnno []
	 */
	public function getPhpAnnos(): array {
		return $this->phpAnnos;
	}
	
	/**
	 * @param string $typeName
	 * @param string $value
	 * @throws IllegalStateException
	 * @return PhpAnno
	 */
	public function createPhpAnno(string $typeName, string $localName = null): PhpAnno {
		$this->checkPhpAnnoName($typeName);
		
		if ($localName === null) {
			$localName = PhpbobUtils::extractClassName($typeName);
		}
	
		$phpAnnoParam = new PhpAnno($this, new PhpTypeDef($localName, $typeName));
	
		$this->phpAnnos[$typeName] = $phpAnnoParam;

		return $phpAnnoParam;
	}

	/**
	 * {@inheritDoc}
	 * @see \phpbob\representation\anno\PhpAnnoCollection::removePhpAnno()
	 */
	public function removePhpAnno(string $typeName): PhpAnnoCollection {
		unset($this->phpAnnos[$typeName]);
		
		return $this;
	}
	
	public function resetPhpAnnos() {
		$this->phpAnnos = [];
		
		return $this;
	}
	
	private function checkPhpAnnoName(string $typeName) {
		if (!isset($this->phpAnnos[$typeName])) return;
		
		throw new IllegalStateException('Anno Param with tyename ' . $typeName . ' already defined.');
 	}
	
 	public function getPhpAnnotationSet(): PhpAnnotationSet {
 		return $this->phpAnnotationSet;
 	}
 	
	public function getAnnotationString() {
		return implode(', ', $this->phpAnnos);
 	}
 	
 	public function getPhpTypeDefs(): array {
 		$phpTypeDefs = [];
 		foreach ($this->phpAnnos as $phpAnno) {
 			$phpTypeDefs[] = $phpAnno->getPhpTypeDef(); 
 		}
 		
 		return $phpTypeDefs;
 	}
 	
 	public function appendPrependingCode(string $prependingCode = null) {
 		if (null !== $prependingCode) return null;
 		
 		$this->prependingCode .= $prependingCode;
 	}
	
	public function isEmpty(): bool {
		return count($this->phpAnnos) === 0;
	}
}
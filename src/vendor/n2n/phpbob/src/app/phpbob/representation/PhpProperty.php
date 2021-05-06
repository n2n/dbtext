<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use n2n\util\type\ArgUtils;

class PhpProperty extends PhpVariable {

	private $phpClassLike;
	private $classifier;
	private $static;
	
	public function __construct(PhpClassLike $phpClassLike, string $classifier, 
			string $name, string $value = null, string $prependingCode = null) {
		parent::__construct($name, $value, $prependingCode);
		$this->phpClassLike = $phpClassLike;
		$this->classifier = $classifier;
		
		$that = $this;
		$this->onNameChange(function($oldName, $newName) use ($that) {
			$this->getPhpPropertyAnnoCollection()->setPropertyName($newName);
		});
	}
	
	public function setStatic(bool $static) {
		$this->static = $static;
		
		return $this;
	}
	
	public function isStatic() {
		return $this->static;
	}
	
	public function getClassifier() {
		return $this->classifier;
	}

	public function setClassifier(string $classifier) {
		ArgUtils::valEnum($classifier, Phpbob::getClassifiers());
		
		$this->classifier = $classifier;
	}
	
	/**
	 * @return \phpbob\representation\anno\PhpPropertyAnnoCollection
	 */
	public function getPhpPropertyAnnoCollection() {
		return $this->phpClassLike->getPhpAnnotationSet()
				->getOrCreatePhpPropertyAnnoCollection($this->getName());
	}
	
	/**
	 * @param string $typeName
	 * @return \phpbob\representation\PhpProperty
	 */
	public function removePhpUse(string $typeName) {
		$this->phpClassLike->removePhpUse($typeName);
		
		return $this;
	}
	
	/**
	 * @return \phpbob\representation\PhpClassLike
	 */
	public function getPhpClassLike() {
		return $this->phpClassLike;
	}
	
	/**
	 * @param string $typeName
	 * @param string $alias
	 * @param string $type
	 * @return \phpbob\representation\PhpProperty
	 */
	public function createPhpUse(string $typeName, string $alias = null, string $type = null) {
		$this->phpClassLike->createPhpUse($typeName, $alias, $type);
		
		return $this;
	}
	
	public function determineTypeName(string $localName) {
		return $this->phpClassLike->determineTypeName($localName);
	}
	
	/**
	 * @return \phpbob\representation\PhpTypeDef
	 */
	public function determinePhpTypeDef() {
		return $this->phpClassLike->determinePhpTypeDef($this->name);
	}
	
	/**
	 * @return \phpbob\representation\PhpTypeDef
	 */
	public function determineArrayLikePhpTypeDef() {
		return $this->phpClassLike->determineArrayLikePhpTypeDef($this->name);
	}

	public function __toString() {
		$string = $this->getPrependingString() . "\t";
		if (null !== $this->classifier) {
			$string .= $this->classifier . ' ';
		}
		
		return $string .  $this->getNameValueString() . Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL;
	}
}
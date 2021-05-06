<?php
namespace phpbob\representation\anno;

use phpbob\Phpbob;
use n2n\util\StringUtils;
use phpbob\representation\PhpTypeDef;

class PhpAnno {
	private $phpAnnoCollection;
	private $phpAnnoParams = array();
	private $phpTypeDef;
	
	public function __construct(PhpAnnoCollection $phpAnnoCollection, PhpTypeDef $phpTypeDef) {
		$this->phpAnnoCollection = $phpAnnoCollection;
		$this->phpTypeDef = $phpTypeDef;
	}
	
	public function resetPhpAnnoParams() {
		$this->phpAnnoParams = [];
		
		return $this;
	}
	
	public function getPhpAnnoParams() {
		return $this->phpAnnoParams;
	}

	public function getPhpTypeDef() {
		return $this->phpTypeDef;
	}
	
	public function createPhpAnnoParam(string $value, bool $escape = false) {
		$this->phpAnnoParams[] = new PhpAnnoParam($this, $escape ? self::escapeString($value) : $value);	
	}
	
	public function hasPhpAnnoParam(int $position) {
		return isset($this->phpAnnoParams[$position - 1]);
	}
	
	public function getNumPhpAnnoParams() {
		return count($this->phpAnnoParams);	
	}
	
	/**
	 * @param int $position
	 * @throws \InvalidArgumentException
	 * @return PhpAnnoParam
	 */
	public function getPhpAnnoParam(int $position, bool $lenient = true) {
		if (!$this->hasPhpAnnoParam($position)) {
			if ($lenient) return null;
			
			throw new \InvalidArgumentException('Position ' . $position
					. ' not Available in \"' . $this->phpTypeDef . '\"');
		}
		
		return $this->phpAnnoParams[$position - 1];
	}

	/**
	 * @param int $position
	 * @param string $value
	 * @return PhpAnnoParam
	 */
	public function getOrCreatePhpAnnoParam(int $position, string $value) {
		if (!$this->hasPhpAnnoParam($position)) {
			$this->phpAnnoParams[$position - 1] = new PhpAnnoParam($this, $value);
		} else {
			$this->phpAnnoParams[$position - 1]->setValue($value);
		}
		
		return $this->phpAnnoParams[$position - 1];
	}
 
	public function setPhpTypeDef(PhpTypeDef $phpTypeDef) {
		$this->phpTypeDef = $phpTypeDef;
	}
	
	public function isForAnno(string $typeName) {
		return $this->phpTypeDef->determineUseTypeName() === $typeName;
	}
	
	/**
	 * @return \n2n\reflection\annotation\Annotation
	 */
	public function determineAnnotation() {
		return $this->phpAnnoCollection->determineAnnotation($this);
	}
	
	private static function escapeString(string $str) {
		if (StringUtils::startsWith(Phpbob::VARIABLE_PREFIX, $str)
				|| mb_strpos($str, Phpbob::CONST_SEPERATOR) !== false
				|| StringUtils::startsWith($str, Phpbob::STRING_LITERAL_SEPERATOR)
				|| StringUtils::startsWith($str, Phpbob::STRING_LITERAL_ALTERNATIVE_SEPERATOR)) {
					return $str;
				}
				
				return Phpbob::STRING_LITERAL_SEPERATOR . $str . Phpbob::STRING_LITERAL_SEPERATOR;
	}
	
	public function __toString() {
		return Phpbob::KEYWORD_NEW . ' ' . $this->phpTypeDef . '(' . implode(', ', $this->phpAnnoParams) . ')';
	}
}
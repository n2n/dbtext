<?php
namespace phpbob\representation;

use phpbob\Phpbob;

abstract class PhpParamContainerAdapter implements PhpParamContainer {
	
	private $returnPhpTypeDef;
	private $returnValueNullable = false;
	private $phpParams = [];

	/**
	 * @return \phpbob\representation\PhpTypeDef
	 */
	public function getReturnPhpTypeDef() {
		return $this->returnPhpTypeDef;
	}

	/**
	 * @param PhpTypeDef $returnPhpTypeDef
	 * @return \phpbob\representation\PhpParamContainerAdapter
	 */
	public function setReturnPhpTypeDef(PhpTypeDef $returnPhpTypeDef = null) {
		$this->returnPhpTypeDef = $returnPhpTypeDef;
		
		return $this;
	}
	
	/**
	 * @param boolean $returnValueNullable
	 * @return \phpbob\representation\PhpParamContainerAdapter
	 */
	public function setReturnValueNullable(bool $returnValueNullable) {
		$this->returnValueNullable = $returnValueNullable;
		
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function isReturnValueNullable() {
		return $this->returnValueNullable;
	}
	
	
	/**
	 * @return PhpParam []
	 */
	public function getPhpParams() {
		return $this->phpParams;
	}
	
	/**
	 * @return \phpbob\representation\PhpParamContainerAdapter
	 */
	public function resetPhpParams() {
		$this->phpParams = [];
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @return PhpParam|NULL
	 */
	public function getPhpParam(string $name) {
		if (isset($this->phpParams[$name])) return $this->phpParams[$name];
		
		return null;
	}
	
	/**
	 * @return NULL|PhpParam
	 */
	public function getFirstPhpParam() {
		if (count($this->phpParams) === 0) return null;
		
		return reset($this->phpParams);
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @param PhpTypeDef $phpTypeDef
	 * @param bool $splat
	 * 
	 * @return PhpParam
	 * 
	 * Creates a PhpParam for this Container, if there is already a param with this name, it gets replaced
	 */
	public function createPhpParam(string $name, string $value = null, 
			PhpTypeDef $phpTypeDef = null, bool $splat = false) {
		$phpParam = new PhpParam($this, $name, $value, $phpTypeDef);
		$phpParam->setSplat($splat);
		$this->phpParams[$name] = $phpParam;
		
		return $phpParam;
	}
	
	public function getPhpTypeDefs() : array {
		$typeDefs = [];
		if (null !== $this->returnPhpTypeDef) {
			$typeDefs[] = $this->returnPhpTypeDef;
		}
		
		foreach ($this->phpParams as $phpParam) {
			if (!$phpParam->hasPhpTypeDef()) continue;
			
			$typeDefs[] = $phpParam->getPhpTypeDef();
		}
		
		return $typeDefs;
	}
	
	public function generateParamContainerStr() {
		$str = Phpbob::PARAMETER_GROUP_START . implode(', ', $this->phpParams) . Phpbob::PARAMETER_GROUP_END;
		if (null !== $this->returnPhpTypeDef) {
			$str . ': ' . ($this->returnValueNullable ? '?' : '') .  $this->returnPhpTypeDef;
		}
		
		return $str;
	}
}
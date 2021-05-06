<?php
namespace phpbob\representation;

interface PhpParamContainer {
	/**
	 * @return \phpbob\representation\PhpTypeDef
	 */
	public function getReturnPhpTypeDef();
	
	/**
	 * @param PhpTypeDef $returnPhpTypeDef
	 * @return \phpbob\representation\PhpParamContainerAdapter
	 */
	public function setReturnPhpTypeDef(PhpTypeDef $returnPhpTypeDef = null);
	
	/**
	 * @return PhpParam []
	 */
	public function getPhpParams();
	
	/**
	 * @return \phpbob\representation\PhpParamContainerAdapter
	 */
	public function resetPhpParams();
	
	/**
	 * @param string $name
	 * @return PhpParam|NULL
	 */
	public function getPhpParam(string $name);
	
	/**
	 * @return PhpParam
	 */
	public function getFirstPhpParam();
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
			PhpTypeDef $phpTypeDef = null, bool $splat = false);
	
	/**
	 * @return string
	 */
	public function __toString();
}
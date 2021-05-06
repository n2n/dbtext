<?php
namespace phpbob\representation;

interface PhpFileElement {
	/**
	 * @return PhpFile
	 */
	public function getPhpFile();
	
	/**
	 * @return PhpTypeDef []
	 */
	public function getPhpTypeDefs();
	public function __toString();
}
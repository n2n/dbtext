<?php
namespace phpbob\representation\traits;

use phpbob\representation\PhpNamespaceElementCreator;
use phpbob\representation\PhpFile;
use phpbob\representation\PhpNamespace;

trait PhpNamespaceElementTrait {
	protected $phpFile;
	protected $phpNamespace;
	
	/**
	 * @return PhpFile
	 */
	public function getPhpFile() {
		return $this->phpFile;
	}
	
	/**
	 * @return PhpNamespace
	 */
	public function getPhpNamespace() {
		return $this->phpNamespace;
	}
	
	/**
	 * @return PhpNamespaceElementCreator
	 */
	protected function determinePhpNamespaceElementCreator() {
		if (null !== $this->phpNamespace) return $this->phpNamespace;
		
		return $this->phpFile;
	}
}
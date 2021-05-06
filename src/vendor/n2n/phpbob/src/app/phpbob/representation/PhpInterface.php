<?php
namespace phpbob\representation;

use phpbob\representation\traits\InterfacesTrait;
use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;
use phpbob\Phpbob;

class PhpInterface extends PhpTypeAdapter {
	use InterfacesTrait;
	
	private $phpInterfaceMethods = [];
	
	public function extendsInterface(string $typeName) {
		return $this->hasInterfacePhpTypeDef($typeName);
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpInterfaceMethod(string $name) {
		return isset($this->phpInterfaceMethods[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpInterfaceMethod(string $name) {
		if (!isset($this->phpInterfaceMethods[$name])) {
			throw new UnknownElementException('No interface method with name "' . $name . '" given.');
		}
		
		return $this->phpInterfaceMethods[$name];
	}
	
	/**
	 * @return PhpInterfaceMethod []
	 */
	public function getPhpInterfaceMethods() {
		return $this->phpInterfaceMethods;
	}

	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpInterfaceMethod
	 */
	public function createPhpInterfaceMethod(string $name) {
		$this->checkPhpInterfaceMethodName($name);
		
		$phpInterfaceMethod = new PhpInterfaceMethod($name);
		$that = $this;
		$phpInterfaceMethod->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpInterfaceMethodName($newName);
			
			$tmpPhpInterfaceMethod = $that->phpInterfaceMethods[$oldName];
			unset($that->phpInterfaceMethods[$oldName]);
			$that->phpInterfaceMethods[$newName] = $tmpPhpInterfaceMethod;
		});
		
		return $phpInterfaceMethod;
	}
	
	public function getPhpTypeDefs() : array {
		$typeDefs = $this->interfacePhpTypeDefs;
		
		foreach ($this->phpInterfaceMethods as $phpInterfaceMethod) {
			$typeDefs = array_merge($typeDefs, $phpInterfaceMethod->getPhpTypeDefs());
		}
		
		return $typeDefs;
	}
	
	public function __toString() {
		$interfacesStr = '';
		if (count($this->interfacePhpTypeDefs) > 0) {
			$interfacesStr = ' extends ' . $this->generateInterfacesStr();
		}
		
		return $this->getPrependingCode() . Phpbob::KEYWORD_INTERFACE . $interfacesStr 
				. Phpbob::GROUP_STATEMENT_OPEN . PHP_EOL . $this->generateBody() ;
	}
	
	protected function generateBody() {
		return rtrim($this->generateConstStr() . $this->generateMethodStr()) . PHP_EOL;
	}
	
	
	protected function generateMethodStr() {
		if (empty($this->phpInterfaceMethods)) return '';
		
		$str = '';
		foreach ($this->phpInterfaceMethods as $phpInterfaceMethod) {
			$str .=  "\t" . trim((string) $phpInterfaceMethod) . PHP_EOL . PHP_EOL ;
		}
		
		return $str;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 */
	private function checkPhpInterfaceMethodName(string $name) {
		if (isset($this->phpInterfaceMethods[$name])) {
			throw new IllegalStateException('Interface method with name ' . $name . ' already defined.');
		}
	}
}
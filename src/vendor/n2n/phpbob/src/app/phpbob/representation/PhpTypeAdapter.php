<?php
namespace phpbob\representation;

use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\traits\NameChangeSubjectTrait;
use n2n\util\ex\IllegalStateException;
use phpbob\representation\ex\UnknownElementException;
use phpbob\representation\traits\PhpNamespaceElementTrait;
use phpbob\Phpbob;

abstract class PhpTypeAdapter implements PhpType {
	use PrependingCodeTrait;
	use NameChangeSubjectTrait;
	use PhpNamespaceElementTrait;
	
	private $phpConsts = [];
	
	public function __construct(PhpFile $phpFile, string $name, PhpNamespace $phpNamespace = null) {
		$this->phpFile = $phpFile;
		$this->phpNamespace = $phpNamespace;
		$this->name = $name;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpConst(string $name) {
		return isset($this->phpConsts[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpConst(string $name) {
		if (!isset($this->phpConsts[$name])) {
			throw new UnknownElementException('No constant with name "' . $name . '" given.');
		}
		
		return $this->phpConsts[$name];
	}
	
	/**
	 * @return PhpConst[]
	 */
	public function getPhpConsts() {
		return $this->phpConsts;
	}
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpConst
	 */
	public function createPhpConst(string $name, string $value) {
		$this->checkPhpConstName($name);
		
		$phpConst = new PhpConst($this->getPhpFile(), $name, $value, $this->getPhpNamespace(), $this);
		$that = $this;
		$phpConst->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpConstName($newName);
			
			$tmpPhpConst = $that->phpConsts[$oldName];
			unset($that->phpConsts[$oldName]);
			$that->phpConsts[$newName] = $tmpPhpConst;
		});
		
		$this->phpConsts[$name] = $phpConst;
			
		return $phpConst;
	}
	
	public function createPhpUse(string $typeName, string $alias = null, string $type = null) {
		return $this->determinePhpNamespaceElementCreator()->createPhpUse($typeName, $alias, $type);
	}
	
	public function hasPhpUse(string $typeName) {
		return $this->determinePhpNamespaceElementCreator()->hasPhpUse($typeName);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \phpbob\representation\PhpType::removePhpUse()
	 */
	public function removePhpUse(string $typeName) {
		$this->determinePhpNamespaceElementCreator()->removePhpUse($typeName);
		
		return $this;
	}
	
	public function removePhpConst(string $name) {
		unset($this->phpConsts[$name]);
		
		return $this;
	}
	
	public function determineTypeName(string $localName) {
		return $this->determinePhpNamespaceElementCreator()->determineTypeName($localName);
	}
	
	public function getTypeName(): string {
		if (null === $this->getPhpNamespace()) return $this->getName();
		
		return $this->getPhpNamespace()->getName() . Phpbob::NAMESPACE_SEPERATOR .  $this->getName();
	}
	
	private function checkPhpConstName(string $name) {
		if (isset($this->phpConsts[$name])) {
			throw new IllegalStateException('Constant with name ' . $name . ' already defined.');
		}
	}
	
	protected function generateConstStr() {
		if (empty($this->phpConsts)) return '';
		
		return implode('', $this->phpConsts) . PHP_EOL;
	}
 }
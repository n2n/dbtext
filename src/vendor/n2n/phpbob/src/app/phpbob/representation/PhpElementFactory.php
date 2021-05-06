<?php
namespace phpbob\representation;

use n2n\util\ex\IllegalStateException;
use phpbob\representation\ex\UnknownElementException;
use n2n\util\StringUtils;
use n2n\util\type\ArgUtils;
use phpbob\PhpbobUtils;
use phpbob\representation\ex\DuplicateElementException;
use phpbob\Phpbob;

class PhpElementFactory {
	const FUNCTION_PREFIX = 'func-';
	const CONST_PREFIX = 'const-';
	const TYPE_PREFIX = 'type-';
	
	private $phpFile;
	private $phpNamespace;
	private $namespacesOnly = false;
	/**
	 * @var PhpFileElement []
	 */
	private $phpFileElements = array();
	/**
	 * @var PhpUse []
	 */
	private $phpUses = array();
	
	public function __construct(PhpFile $phpFile, PhpNamespace $phpNamespace = null) {
		$this->phpFile = $phpFile;
		$this->phpNamespace = $phpNamespace;
	}
	
	public function getPhpFileElements() {
		return $this->phpFileElements;
	}
	
	public function hasNamespaces() {
		return $this->namespacesOnly;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpNamespace(string $name) {
		return $this->namespacesOnly && isset($this->phpFileElements[$name]);
	}
	
	/**
	 * @param string $name
	 * @return PhpFunction
	 */
	public function getPhpNamespace(string $name) {
		if (!$this->namespacesOnly || !isset($this->phpFileElements[$name])) {
			throw new UnknownElementException('No namespace with name "' . $name . '" given.');
		}
		
		return $this->phpFileElements[$name];
	}
	
	/**
	 * @return PhpNamespace []
	 */
	public function getPhpNameSpaces() {
		if (!$this->namespacesOnly) return [];
		
		return $this->phpFileElements;
	}
	
	/**
	 * @return PhpNamespace
	 */
	public function getFirstPhpNameSpace() {
		foreach ($this->phpFileElements as $phpNamespace) {
			return $phpNamespace;
		}
		
		return null;
	}
	
	/**
	 * @param string $name
	 * @param PhpTypeDef $returnPhpTypeDef
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpFunction
	 */
	public function createPhpNamespace(string $name) {
		if (null !== $this->phpNamespace) {
			throw new IllegalStateException('Nested namespaces are not allowed');
		}
		
		if (!$this->namespacesOnly && !empty($this->phpFileElements) || !empty($this->phpUses)) {
			throw new IllegalStateException('Namespace must be the first element in a php file');
		}
		
		$this->namespacesOnly = true;
		
 		$phpNamespace = new PhpNamespace($this->phpFile, $name);
				
		$that = $this;
		$phpNamespace->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkNamespaceName($newName);
			$that->changePhpFileElementsKey($oldName, $newName);
		});
			
 		$this->phpFileElements[$name] = $phpNamespace;
 		return $phpNamespace;
	}
	
	/**
	 * @param string $name
	 */
	public function removePhpNamespace(string $name) {
		if (!$this->namespacesOnly) return;
		
		unset($this->phpFileElements[$name]);
		
		return $this;
	}
	
	private function checkNamespaceName(string $name) {
		if ($this->hasPhpNamespace($name)) {
			throw new IllegalStateException('Namespace with name ' . $name . ' already defined.');
		}
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpFunction(string $name) {
		return isset($this->phpFileElements[$this->buildFunctionKey($name)]);
	}
	
	/**
	 * @param string $name
	 * @return PhpFunction
	 */
	public function getPhpFunction(string $name) {
		$key = $this->buildFunctionKey($name);
		if (!isset($this->phpFileElements[$key])) {
			throw new UnknownElementException('No function with name "' . $name . '" given.');
		}
		
		return $this->phpFileElements[$key];
	}
	
	/**
	 * @return PhpFunction []
	 */
	public function getPhpFunctions() {
		return $this->getElementsWithPrefix(self::FUNCTION_PREFIX);
	}
	
	/**
	 * @param string $name
	 * @param PhpTypeDef $returnPhpTypeDef
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpFunction
	 */
	public function createPhpFunction(string $name, PhpTypeDef $returnPhpTypeDef = null) {
		$this->checkNamespaceOnly();
		$this->checkPhpFunctionName($name);
		
		$phpFunction = new PhpFunction($this->phpFile, $name, $this->phpNamespace);
		$phpFunction->setReturnPhpTypeDef($returnPhpTypeDef);
		
		$that = $this;
		$phpFunction->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpFunctionName($newName);
			$that->changePhpFileElementsKey($that->buildFunctionKey($oldName), 
					$that->buildFunctionKey($newName));
		});
		
		$this->phpFileElements[$this->buildFunctionKey($name)] = $phpFunction;
		return $phpFunction;
	}
	
	/**
	 * @param string $name
	 */
	public function removePhpFunction(string $name) {
		unset($this->phpFileElements[$this->buildFunctionKey($name)]);
		
		return $this;
	}
	
	private function checkPhpFunctionName(string $name) {
		if ($this->hasPhpFunction($name)) {
			throw new IllegalStateException('Function with name ' . $name . ' already defined.');
		}
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpConst(string $name) {
		return isset($this->phpFileElements[$this->buildConstKey($name)]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpConst(string $name) {
		$key = $this->buildConstKey($name);
		if (!isset($this->phpFileElements[$key])) {
			throw new UnknownElementException('No const with name "' . $name . '" given.');
		}
		
		return $this->phpFileElements[$key];
	}
	
	/**
	 * @return PhpConst []
	 */
	public function getPhpConsts() {
		return $this->getElementsWithPrefix(self::CONST_PREFIX);
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpConst
	 */
	public function createPhpConst(string $name, string $value) {
		$this->checkNamespaceOnly();
		$this->checkPhpConstName($name);
		
		$phpConst = new PhpConst($this->phpFile, $name, $value, $this->phpNamespace);
		
		$that = $this;
		$phpConst->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpConstName($newName);
			$that->changePhpFileElementsKey($that->buildConstKey($oldName),
					$that->buildConstKey($newName));
		});
		
		$this->phpFileElements[$this->buildConstKey($name)] = $phpConst;
		
		return $phpConst;
	}
	
	public function removePhpConst(string $name) {
		unset($this->phpFileElements[$this->buildConstKey($name)]);
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 */
	private function checkPhpConstName(string $name) {
		if ($this->hasPhpConst($name)) {
			throw new IllegalStateException('Const with name ' . $name . ' already defined.');
		}
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpType(string $name) {
		return isset($this->phpFileElements[$this->buildTypeKey($name)]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpType(string $name) {
		$key = $this->buildTypeKey($name);
		if (!isset($this->phpFileElements[$key])) {
			throw new UnknownElementException('No type with name "' . $name . '" given.');
		}
		
		return $this->phpFileElements[$key];
	}
	
	/**
	 * @return PhpType[]
	 */
	public function getPhpTypes() {
		return $this->getElementsWithPrefix(self::TYPE_PREFIX);
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpInterface
	 */
	public function createPhpInterface(string $name) {
		$this->checkNamespaceOnly();
		$this->checkPhpTypeName($name);
		
		$phpInterface = new PhpInterface($this->phpFile, $name, $this->phpNamespace);
		$this->applyPhpTypeOnNameChange($phpInterface);
		$this->phpFileElements[$this->buildTypeKey($name)] = $phpInterface;
		
		return $phpInterface;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpTrait
	 */
	public function createPhpTrait(string $name) {
		$this->checkNamespaceOnly();
		$this->checkPhpTypeName($name);
		
		$phpTrait = new PhpTrait($this->phpFile, $name, $this->phpNamespace);
		$this->applyPhpTypeOnNameChange($phpTrait);
		$this->phpFileElements[$this->buildTypeKey($name)] = $phpTrait;
		
		return $phpTrait;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpClass
	 */
	public function createPhpClass(string $name) {
		$this->checkNamespaceOnly();
		$this->checkPhpTypeName($name);
		
		$phpClass = new PhpClass($this->phpFile, $name, $this->phpNamespace);
		$this->applyPhpTypeOnNameChange($phpClass);
		$this->phpFileElements[$this->buildTypeKey($name)] = $phpClass;
		
		return $phpClass;
	}
	
	/**
	 * @param string $code
	 * @return \phpbob\representation\UnknownPhpCode
	 */
	public function createUnknownPhpCode(string $code) {
		$unknownPhpCode = new UnknownPhpCode($this->phpFile, $code, $this->phpNamespace);
		$this->phpFileElements[] = $unknownPhpCode;
		
		return $unknownPhpCode;
	}
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpElementFactory
	 */
	public function removePhpType(string $name) {
		unset($this->phpFileElements[$this->buildTypeKey($name)]);
		
		return $this;
	}
	
	/**
	 * @param string $typeName
	 * @return bool
	 */
	public function hasPhpUse(string $typeName) {
		return isset($this->phpUses[$typeName]);
	}
	
	/**
	 * @param string $typeName
	 * @throws UnknownElementException
	 * @return PhpUse
	 */
	public function getPhpUse(string $typeName) {
		if (!isset($this->phpUses[$typeName])) {
			throw new UnknownElementException('No use for type name "' . $typeName . '" available.');
		}
		
		return $this->phpUses[$typeName];
	}
	
	/**
	 * @param string $typeName
	 * @throws DuplicateElementException
	 * @return PhpUse
	 */
	public function determineTypeName(string $localName) {
		if (StringUtils::startsWith(Phpbob::NAMESPACE_SEPERATOR, $localName)) {
			return $localName;
		}
		
		if (PhpbobUtils::isSimpleType($localName)) {
			return $localName;
		}
		
		$thePhpUse = null;
		$localNameParts = PhpbobUtils::explodeTypeName($localName);
		$alias = null;
		if (count($localNameParts) > 1) {
			$alias = array_shift($localNameParts);
		}
		
		foreach ($this->phpUses as $phpUse) {
			if (null !== $alias) {
				if ($phpUse->getAlias() !== $alias) {
					continue;
				}
			} elseif (null !== $phpUse->getAlias()) {
				continue;
			} else {
				if (!StringUtils::endsWith(Phpbob::NAMESPACE_SEPERATOR . $localName, $phpUse->getTypeName())) continue;
			}
			
			if (null !== $thePhpUse) {
				throw new DuplicateElementException();
			}
			
			$thePhpUse = $phpUse;
		}
		
		if (null === $thePhpUse) {
			if (count($localNameParts) > 1) return $localName;
			
			return $this->phpNamespace->getName() . Phpbob::NAMESPACE_SEPERATOR .  $localName;
		}
		
		if (null === $alias) return $thePhpUse->getTypeName();
		
		return $thePhpUse->getTypeName() . Phpbob::NAMESPACE_SEPERATOR . implode(Phpbob::NAMESPACE_SEPERATOR, $localNameParts);
	}
	
	/**
	 * @return PhpUse []
	 */
	public function getPhpUses() {
		return $this->phpUses;
	}

	
	/**
	 * @param string $typeName
	 * @param string $alias
	 * @param string $type
	 * @param bool $lenient
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpUse
	 */
	public function createPhpUse(string $typeName, 
			string $alias = null, string $type = null, bool $lenient = true) {
		if ($this->hasPhpUse($typeName)) {
			if ($lenient) return $this->getPhpUse($typeName);
			
			throw new IllegalStateException('Use for typename ' . $typeName . ' already defined.');
		}
		
		$phpUse = (new PhpUse($this->phpFile, $typeName, $this->phpNamespace))->setAlias($alias)->setType($type);
		
		$this->phpUses[$typeName] = $phpUse;
		
		return $phpUse;
	}
	
	/**
	 * @param string $alias
	 * @return boolean
	 */
	public function hasPhpUseAlias(string $alias) {
		foreach ($this->phpUses as $phpUse) {
			if ($phpUse->getAlias() === $alias) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @param string $alias
	 * @return PhpUse
	 */
	public function getPhpUseForAlias(string $alias) {
		foreach ($this->phpUses as $phpUse) {
			if ($phpUse->getAlias() === $alias) return $phpUse;
		}
		
		return null;
	}
	
	/**
	 * @param string $typeName
	 * @return \phpbob\representation\PhpElementFactory
	 */
	public function removePhpUse(string $typeName) {
		unset($this->phpUses[$typeName]);
		
		return $this;
	}
	
	public function resetPhpUses() {
		$this->phpUses = [];
	}
	
	private function getElementsWithPrefix(string $prefix) {
		$phpFileElements = [];
		foreach ($this->phpFileElements as $key => $phpFileElement) {
			if (!StringUtils::startsWith($key, $prefix)) continue;
			$phpFileElements[] = $phpFileElement;
		}
			
		return $phpFileElements;
	}
	
	private function checkPhpTypeName(string $name) {
		if ($this->hasPhpType($name)) {
			throw new IllegalStateException('Type with name ' . $name . ' already defined.');
		}
	}
	
	private function applyPhpTypeOnNameChange(PhpType $phpType) {
		$that = $this;
		$phpType->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpTypeName($newName);
			$that->changePhpFileElementsKey($that->buildTypeKey($oldName),
					$that->buildTypeKey($newName));
		});
	}
	
	private function changePhpFileElementsKey(string $oldKey, string $newKey) {
		$tmpFileElement = $this->phpFileElements[$oldKey];
		unset($this->phpFileElements[$oldKey]);
		$this->phpFileElements[$newKey] = $tmpFileElement;
	}
	
	private function checkNamespaceOnly() {
		if (!$this->namespacesOnly) return;
		
		throw new IllegalStateException('Only namespaces are allowed in this php file.');
	}
	
	private function buildFunctionKey(string $name) {
		return self::FUNCTION_PREFIX . $name;
	}
	
	private function buildConstKey(string $name) {
		return self::CONST_PREFIX . $name;
	}
	
	private function buildTypeKey(string $name) {
		return self::TYPE_PREFIX . $name;
	}
	
	
	public function resolvePhpTypeDefs() {
		foreach ($this->phpFileElements as $phpFileElement) {
			foreach ($phpFileElement->getPhpTypeDefs() as $phpTypeDef) {
				ArgUtils::valTypeReturn($phpTypeDef, PhpTypeDef::class, $phpFileElement, 'getPhpTypeDefs');
				if (!$phpTypeDef->needsPhpUse()) continue;
				
				$typeName = $phpTypeDef->determineUseTypeName();
				$alias = $phpTypeDef->determineAlias();
				if (null !== $alias
						&& $this->hasPhpUseAlias($alias)
						&& $this->getPhpUseForAlias($alias)->getTypeName() !== $typeName) {
					throw new IllegalStateException('duplicate alias ' . $alias . ' for use statements given');
				}
				
				if (!$this->hasPhpUse($typeName)) {
					$this->createPhpUse($typeName, $alias);
				}
			}
			
			if ($phpFileElement instanceof PhpUseContainer) {
				$phpFileElement->resolvePhpTypeDefs();
				$phpFileElement->removeUnnecessaryPhpUses();
			}
		}
	}
	
	public function isInSameNamespace(string $typeName) {
		return StringUtils::startsWith($this->phpNamespace->getName(), $typeName) && 
				count(PhpbobUtils::explodeTypeName($typeName)) === count(PhpbobUtils::explodeTypeName($this->phpNamespace->getName())) + 1;
	}
	
	public function removeUnnecessaryPhpUses() {
		$phpUses = [];
		foreach ($this->phpUses as $phpUse) {
			if (!$phpUse->hasAlias() && $this->isInSameNamespace($phpUse->getTypeName())) {
				continue;
			}
			$phpUses[] = $phpUse;
		}
		
		$this->phpUses = $phpUses;
	}
	
	public function __toString() {
		$str = '';
		
		if (count($this->phpUses) > 0) {
			$str .= implode('', $this->phpUses) . PHP_EOL;
		}
		
		if (count($this->phpFileElements) > 0) {
			$str .= implode(PHP_EOL, $this->phpFileElements);
		}
		
		return $str;
	}
}
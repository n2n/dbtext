<?php
namespace phpbob\representation;

use phpbob\representation\ex\UnknownElementException;
use n2n\util\ex\IllegalStateException;
use phpbob\representation\anno\PhpAnnotationSet;
use phpbob\Phpbob;
use phpbob\representation\traits\PrependingCodeTrait;
use n2n\util\StringUtils;
use n2n\util\type\ArgUtils;
use phpbob\representation\traits\AppendingCodeTrait;

abstract class PhpClassLikeAdapter extends PhpTypeAdapter implements PhpClassLike {
	use PrependingCodeTrait;
	use AppendingCodeTrait;
	
	private $phpAnnotationSet;
	private $phpProperties = [];
	private $phpMethods = [];
	private $phpTraitUses = [];
	
	/**
	 * @return PhpAnnotationSet
	 */
	public function getPhpAnnotationSet() {
		if (null === $this->phpAnnotationSet) {
			$this->phpAnnotationSet = new PhpAnnotationSet($this);
		}
		return $this->phpAnnotationSet;
	}
	
	public function isPhpAnnotationSetAvailable() {
		return null !== $this->phpAnnotationSet && !$this->phpAnnotationSet->isEmpty();
	}
	
	public function setAnnotationSet(PhpAnnotationSet $annotationSet) {
		$this->phpAnnotationSet = $annotationSet;
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpMethod(string $name): bool {
		return isset($this->phpMethods[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpMethod(string $name): PhpMethod {
		if (!isset($this->phpMethods[$name])) {
			throw new UnknownElementException('No method with name "' . $name . '" given.');
		}
		
		return $this->phpMethods[$name];
	}
	
	/**
	 * @param string $propertyName
	 * @param bool $bool
	 * @return boolean
	 */
	public function hasPhpGetter(string $propertyName, bool $bool = false) {
		return $this->hasPhpMethod(self::determineGetterMethodName($propertyName, $bool));
	}
	
	/**
	 * @param string $propertyName
	 * @param bool $bool
	 * @return \phpbob\representation\PhpMethod
	 */
	public function getPhpGetter(string $propertyName, bool $bool = false) {
		return $this->getPhpMethod(self::determineGetterMethodName($propertyName, $bool));
	}
	
	/**
	 * @param string $propertyName
	 * @return boolean
	 */
	public function hasPhpSetter(string $propertyName) {
		return $this->hasPhpMethod(self::determineSetterMethodName($propertyName));
	}
	
	/**
	 * @param string $propertyName
	 * @return \phpbob\representation\PhpMethod
	 */
	public function getPhpSetter(string $propertyName) {
		return $this->getPhpMethod(self::determineSetterMethodName($propertyName));
	}
	
	/**
	 * @param PhpTypeDef
	 */
	public function determinePhpTypeDef(string $propertyName): ?PhpTypeDef {
		if ($this->hasPhpGetter($propertyName) && 
				null !== $phpTypeDef = $this->getPhpGetter($propertyName)->getReturnPhpTypeDef()) {
			return $phpTypeDef;
		}
		
		if ($this->hasPhpGetter($propertyName, true) && 
				null !== $phpTypeDef = $this->getPhpGetter($propertyName, true)->getReturnPhpTypeDef()) {
			return $phpTypeDef;
		}
		
		if ($this->hasPhpSetter($propertyName)) {
			$phpSetter = $this->getPhpSetter($propertyName);
			if (null !== ($firstPhpParam = $phpSetter->getFirstPhpParam()) 
					&& (null !== $phpTypeDef = $firstPhpParam->getPhpTypeDef())) {
				return $phpTypeDef;			
			}
		}
		
		if ($this->hasPhpGetter($propertyName, true)) {
			return new PhpTypeDef('bool');
		}
		
		if ($this->hasPhpGetter($propertyName)) {
			foreach (explode(PHP_EOL, (string) $this->getPhpGetter($propertyName)->getPrependingCode()) as $line) {
				$pureLine = preg_replace('/^\s*\*\s*/', '', $line);
				if (!StringUtils::startsWith('@return', $pureLine)) continue;
				if (preg_match('/\s*\[\]\s*$/', $pureLine)) continue;
				
				return PhpTypeDef::fromTypeName($this->determineTypeName(preg_replace('/(^\@return\s*|\s*$)/', '', $pureLine)));
			}
		}
		
		return null;
	}
	
	/**
	 * @param string $propertyName
	 * @return \phpbob\representation\PhpTypeDef|NULL
	 */
	public function determineArrayLikePhpTypeDef(string $propertyName): ?PhpTypeDef {
		if ($this->hasPhpGetter($propertyName)) {
			foreach (explode(PHP_EOL, (string) $this->getPhpGetter($propertyName)->getPrependingCode()) as $line) {
				$pureLine = preg_replace('/^\s*\*\s*/', '', $line);
				if (!StringUtils::startsWith('@return', $pureLine)) continue;
				if (!preg_match('/\s*\[\]\s*$/', $pureLine)) continue;
				
				return PhpTypeDef::fromTypeName($this->determineTypeName(preg_replace('/(^\@return\s*|\s*\[\]\s*$)/', '', $pureLine)));
			}
		}
		
		return null;
	}
	
	/**
	 * @return PhpMethod []
	 */
	public function getPhpMethods() {
		return $this->phpMethods;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpMethod
	 */
	public function createPhpMethod(string $name): PhpMethod {
		$this->checkPhpMethodName($name);
		
		$phpMethod = new PhpMethod($this, $name);
		$that = $this;
		$phpMethod->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpMethodName($newName);
			
			$tmpPhpMethod = $that->phpMethods[$oldName];
			unset($that->phpMethods[$oldName]);
			$that->phpMethods[$newName] = $tmpPhpMethod;
		});
		
		$this->phpMethods[$name] = $phpMethod;
			
		return $phpMethod;
	}
	
	/**
	 * @param PhpMethod $phpMethod
	 * @return PhpMethod
	 */
	public function createPhpMethodClone(PhpMethod $phpMethod) {
		$phpMethodClone = $this->createPhpMethod($phpMethod->getName())
				->setAbstract($phpMethod->isAbstract())->setClassifier($phpMethod->getClassifier())
				->setFinal($phpMethod->isFinal())->setPrependingCode($phpMethod->getPrependingCode())
				->setMethodCode($phpMethod->getMethodCode())->setReturnPhpTypeDef($phpMethod->getReturnPhpTypeDef());
		
		foreach ($phpMethod->getPhpParams() as $phpParam) {
			$phpMethodClone->createPhpParam($phpParam->getName(), $phpParam->getValue(),
					$phpParam->getPhpTypeDef(), $phpParam->isSplat())->setPassedByReference($phpParam->isPassedByReference());
		}
		
		return $phpMethodClone;
	}
	
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpMethod
	 */
	public function createPhpSetter(string $propertyName, PhpTypeDef $phpTypeDef = null, string $value = null) {
		$methodName = self::determineSetterMethodName($propertyName);
		if ($this->hasPhpMethod($methodName)) {
			$this->removePhpMethod($methodName);
		}
		
		return $this->updateOrCreatePhpSetter($propertyName, $phpTypeDef, $value);
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpMethod
	 */
	public function updateOrCreatePhpSetter(string $propertyName, PhpTypeDef $phpTypeDef = null, string $value = null,
			string $newMethodCode = null) {
		if (!$this->hasPhpProperty($propertyName)) {
			throw new IllegalStateException('No property with name \'' . $propertyName . '\' available.');
		}
		
		$methodName = self::determineSetterMethodName($propertyName);
		
		$phpMethod = null;
		if ($this->hasPhpMethod($methodName)) {
			$phpMethod = $this->getPhpMethod($methodName);
			$phpMethod->getFirstPhpParam()->setName($propertyName)->setValue($value)->setPhpTypeDef($phpTypeDef);
		} else {
			// the methodcode should only be created if no method code is available,
			// otherwise custom getter methods are overwritten
			$phpMethod = $this->createPhpMethod($methodName);
			$phpMethod->setMethodCode("\t\t" . '$this->' . $propertyName . ' ' . Phpbob::ASSIGNMENT . ' $' . $propertyName . Phpbob::SINGLE_STATEMENT_STOP);
			$phpMethod->createPhpParam($propertyName, $value, $phpTypeDef);
		}
		if (!empty($newMethodCode)) {
			$phpMethod->setMethodCode($newMethodCode);
		}
		
			
		return $phpMethod;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpMethod
	 */
	public function createPhpGetter(string $propertyName, PhpTypeDef $phpTypeDef = null) {
		$methodName = self::determineGetterMethodName($propertyName, (null !== $phpTypeDef && $phpTypeDef->isBool()));
		if ($this->hasPhpMethod($methodName)) {
			$this->removePhpMethod($methodName);
		}
		
		return $this->updateOrCreatePhpGetter($propertyName, $phpTypeDef);
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpMethod
	 */
	public function updateOrCreatePhpGetter(string $propertyName, PhpTypeDef $phpTypeDef = null, 
			string $newMethodCode = null) {
		if (!$this->hasPhpProperty($propertyName)) {
			throw new IllegalStateException('No property with name \'' . $propertyName . '\' available.');
		}
		
		$methodName = self::determineGetterMethodName($propertyName, (null !== $phpTypeDef && $phpTypeDef->isBool()));
		
		if (null !== $phpTypeDef && $phpTypeDef->isBool()) {
			if (!$this->hasPhpMethod($methodName) && $this->hasPhpMethod(self::determineGetterMethodName($propertyName))) {
				$methodName = self::determineGetterMethodName($propertyName);
			}
		}
		
		if ($this->hasPhpMethod($methodName)) {
			$phpMethod = $this->getPhpMethod($methodName);
		} else {
			// the methodcode should only be created if no method code is available, 
			// otherwise custom getter methods are overwritten 
			$phpMethod = $this->createPhpMethod($methodName);
			$phpMethod->setMethodCode("\t\t" . 'return $this->' . $propertyName . Phpbob::SINGLE_STATEMENT_STOP);
		}
		
		if (!empty($newMethodCode)) {
			$phpMethod->setMethodCode($newMethodCode);
		}
			
		return $phpMethod;
	}
	
	public function updateOrCreatePhpGetterAndSetter(string $propertyName, 
			PhpTypeDef $phpTypeDef = null, string $value = null, string $newGetterMethodCode = null,
			string $newSetterMethodCode = null) {
		
		$this->updateOrCreatePhpGetter($propertyName, $phpTypeDef, $newGetterMethodCode);
		$this->updateOrCreatePhpSetter($propertyName, $phpTypeDef, $value, $newSetterMethodCode);
		
		return $this;
	}
	
	public function createPhpGetterAndSetter(string $propertyName, PhpTypeDef $phpTypeDef = null, string $value = null) {
		
		$this->createPhpGetter($propertyName, $phpTypeDef);
		$this->createPhpSetter($propertyName, $phpTypeDef, $value);
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpClassLikeAdapter
	 */
	public function removePhpMethod(string $name): PhpClassLike {
		unset($this->phpMethods[$name]);
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 */
	private function checkPhpMethodName(string $name) {
		if (isset($this->phpMethods[$name])) {
			throw new IllegalStateException('Method with name ' . $name . ' already defined.');
		}
		
		if ($name === PhpAnnotationSet::ANNO_METHOD_NAME) {
			throw new IllegalStateException('Work with ' . get_class($this) . '::getPhpAnnotationSet() to work with Annotations.');
		}
	}
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpProperty(string $name): bool {
		return isset($this->phpProperties[$name]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpProperty(string $name): PhpProperty {
		if (!isset($this->phpProperties[$name])) {
			throw new UnknownElementException('No property with name "' . $name . '" given.');
		}
		
		return $this->phpProperties[$name];
	}
	
	/**
	 * @return PhpProperty []
	 */
	public function getPhpProperties(): array {
		return $this->phpProperties;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpProperty
	 */
	public function createPhpProperty(string $name, string $classifier = Phpbob::CLASSIFIER_PRIVATE): PhpProperty {
		$this->checkPhpPropertyName($name);
		
		$phpProperty = new PhpProperty($this, $classifier, $name);
		$that = $this;
		$phpProperty->onNameChange(function($oldName, $newName) use ($that) {
			$that->checkPhpPropertyName($newName);
			$tmpPhpProperties = [];
			//keep the same order
			foreach ($that->phpProperties as $aName => $aPhpProperty) {
				if ($aName === $oldName) {
					$tmpPhpProperties[$newName] = $aPhpProperty;
				}  else {
					$tmpPhpProperties[$aName] = $aPhpProperty;
				}
			}
			$that->phpProperties = $tmpPhpProperties;
		});
		
		$this->phpProperties[$name] = $phpProperty;
			
		return $phpProperty;
	}
	
	
	/**
	 * @param array $propertyNames
	 * @param bool $strict - if set to true, all properties must be in the propertyNames - by default the not set property names are prepended
	 */
	public function orderProperties(array $propertyNames, bool $strict = false) {
		ArgUtils::valArray($propertyNames, 'string', false, 'propertyNames');
		if ($strict) {
			ArgUtils::assertTrue(count($propertyNames) === count($this->phpProperties), 'Num properties doesn\'t match.');
		}
		
		$tmpPhpProperties = [];
		foreach ($this->phpProperties as $phpProperty) {
			$aPropertyName = $phpProperty->getName();
			if (!in_array($aPropertyName, $propertyNames)) {
				if ($strict) {
					throw new \InvalidArgumentException('Property name ' . $aPropertyName . 'is missing');
				}
				$tmpPhpProperties[$aPropertyName] = $phpProperty;
			}
		}
		
		foreach ($propertyNames as $aPropertyName) {
			ArgUtils::assertTrue(isset($this->phpProperties[$aPropertyName]),
					'Property with name \'' . $aPropertyName . '\' not defined in \'' . $this->getName() . '\'.');
			$tmpPhpProperties[$aPropertyName] = $this->phpProperties[$aPropertyName];
		}
		$this->phpProperties = $tmpPhpProperties;
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @return \phpbob\representation\PhpClassLikeAdapter
	 */
	public function removePhpProperty(string $name): PhpClassLike {
		$this->phpAnnotationSet->removePhpPropertyAnnoCollection($name); 
		
		unset($this->phpProperties[$name]);
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @throws IllegalStateException
	 */
	private function checkPhpPropertyName(string $name) {
		if (isset($this->phpProperties[$name])) {
			throw new IllegalStateException('property with name ' . $name . ' already defined.');
		}
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasPhpTraitUse(string $typeName): bool {
		return isset($this->phpTraitUses[$typeName]);
	}
	
	/**
	 * @param string $name
	 * @throws UnknownElementException
	 * @return PhpConst
	 */
	public function getPhpTraitUse(string $typeName): PhpTraitUse {
		if (!isset($this->phpTraitUses[$typeName])) {
			throw new UnknownElementException('No php trait use with typename "' . $typeName . '" given.');
		}
		
		return $this->phpProperties[$typeName];
	}
	
	public function getPhpTraitUses(): array {
		return $this->phpTraitUses;
	}
	
	/**
	 * @param string $typeName
	 * @param string $localName
	 * @throws IllegalStateException
	 * @return \phpbob\representation\PhpTraitUse
	 */
	public function createPhpTraitUse(string $typeName, string $localName = null): PhpTraitUse {
		$this->checkPhpTraitUseTypeName($typeName);
		
		if (null === $localName) {
			$localName = $typeName;
		}
		
		$phpTypeDef = new PhpTypeDef($localName, $typeName);
		
		$that = $this;
		$phpTypeDef->onTypeNameChange(function($oldTypeName, $newTypeName) use ($that) {
			$that->checkPhpPropertyName($newTypeName);
			
			$tmpPhpProperty = $that->phpProperties[$oldTypeName];
			unset($that->phpProperties[$oldTypeName]);
			$that->phpProperties[$newTypeName] = $tmpPhpProperty;
		});
		
		$phpTraitUse = new PhpTraitUse($this, $phpTypeDef);
		$this->phpTraitUses[$typeName] = $phpTraitUse;
		
		return $phpTraitUse;
	}
	
	/**
	 * @param string $typeName
	 * @throws IllegalStateException
	 */
	private function checkPhpTraitUseTypeName(string $typeName) {
		if (isset($this->phpTraitUses[$typeName])) {
			throw new IllegalStateException('Trait use ' . $typeName . ' already defined.');
		}
	}
	
	public function getPhpTypeDefs() : array {
		$typeDefs = [];
		
		foreach ($this->phpMethods as $phpMethod) {
			$typeDefs = array_merge($typeDefs, $phpMethod->getPhpTypeDefs());
		}
		
		foreach ($this->phpTraitUses as $phpTraitUse) {
			$typeDefs[] = $phpTraitUse->getPhpTypeDef();
		}
		
		if ($this->isPhpAnnotationSetAvailable()) {
			$typeDefs = array_merge($typeDefs, $this->phpAnnotationSet->getPhpTypeDefs());
		}
		
		return $typeDefs;
	}
	
	protected function generateBody() {
		$str = '';
		
		if (null !== $this->phpAnnotationSet) {
			$str = $this->phpAnnotationSet . PHP_EOL;
		}
		
		return $str . $this->generateTraitsStr() . $this->generateConstStr() . $this->generatePropertiesStr()  
				. $this->generateMethodStr() . $this->getAppendingString();
	}
	
	protected function generateTraitsStr() {
		if (empty($this->phpTraitUses)) return '';
		
		return implode('', $this->phpTraitUses) . PHP_EOL; 
	}
	
	protected function generatePropertiesStr() {
		if (empty($this->phpProperties)) return '';
		
		return implode('', $this->phpProperties) . PHP_EOL;
	}
	
	protected function generateMethodStr() {
		if (empty($this->phpMethods)) return '';
		
		return implode(PHP_EOL, $this->phpMethods);
	}
	
	public static function determineSetterMethodName(string $propertyName) {
		return 'set' . ucfirst((string) $propertyName);
	}
	
	public static function determineGetterMethodName(string $propertyName, bool $bool = false) {
		return (($bool) ? 'is' : 'get') . ucfirst((string) $propertyName);
	}
}
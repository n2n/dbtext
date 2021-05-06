<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\persistence\orm\proxy;

use n2n\util\ex\NotYetImplementedException;
use n2n\reflection\ReflectionUtils;
use n2n\util\StringUtils;

class EntityProxyManager {
	const PROXY_NAMESPACE_PREFIX = 'n2n\\persistence\\orm\\proxy\\entities';
	const PROXY_ACCESS_LISTENR_PROPERTY = '_accessListener';
	const PROXY_TRIGGER_ACCESS_METHOD = '_triggerOnAccess';
	const PROXY_DUP_SUFFIX = '_';

	private static $instance = null;
	
	private $proxyClasses = array();
	private $accessListenerPropertyNames = array();
	private $accessListeners = array();
	
	private function __construct() {
	}
	
	static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new EntityProxyManager();
		}
		
		return self::$instance;
	}
	/**
	 * @param \ReflectionClass $class
	 * @param EntityProxyAccessListener $proxyAccessListener
	 * @return object
	 * @throws CanNotCreateEntityProxyClassException
	 */
	public function createProxy(\ReflectionClass $class, EntityProxyAccessListener $proxyAccessListener) {
		$className = $class->getName();
		if (!isset($this->proxyClasses[$className])) {
			$this->proxyClasses[$className] = $this->createProxyClass($class);
		}

		$proxyClass = $this->proxyClasses[$className];
		$proxy = ReflectionUtils::createObject($proxyClass);
		$property = $proxyClass->getProperty($this->accessListenerPropertyNames[$proxyClass->getName()]);
		$property->setAccessible(true);
		$property->setValue($proxy, $proxyAccessListener);
		
		$this->accessListeners[spl_object_hash($proxy)] = $proxyAccessListener;
		
		return $proxy;
	}
	
	/**
	 * @param EntityProxy $proxy
	 * @throws \n2n\persistence\orm\EntityNotFoundException
	 */
	public function initializeProxy(EntityProxy $proxy) {
		$objHash = spl_object_hash($proxy);
		if (!isset($this->accessListeners[$objHash])) return;
		$this->accessListeners[$objHash]->onAccess($proxy);
	}
	
	public function isProxyInitialized(EntityProxy $proxy) {
		return !isset($this->accessListeners[spl_object_hash($proxy)]);
	}
	
	public function disposeProxyAccessListenerOf($entity) {
		if (!($entity instanceof EntityProxy)) return;
		$objHash = spl_object_hash($entity);
		if (!isset($this->accessListeners[$objHash])) return;
		$this->accessListeners[$objHash]->dispose();
		unset($this->accessListeners[$objHash]);
	}

	private function createProxyClass(\ReflectionClass $class) {
		if ($class->isAbstract()) {
			throw new CanNotCreateEntityProxyClassException('Can not create proxy of abstract class ' . $class->getName() . '.');
		}
		
		if (sizeof($class->getProperties(\ReflectionProperty::IS_PUBLIC))) {
			throw new CanNotCreateEntityProxyClassException('Can not create proxy of class ' . $class->getName() . ' because it has public properties.');
		}
		
		$proxyNamespaceName =  self::PROXY_NAMESPACE_PREFIX;
		$namespaceName = $class->getNamespaceName();
		if ($namespaceName) {
			$proxyNamespaceName .= '\\' . $namespaceName;
		}
		$proxyClassName = mb_substr($class->getName(), mb_strlen($namespaceName) + 1);

		$accessListenerPropertyName = self::PROXY_ACCESS_LISTENR_PROPERTY;
		while ($class->hasProperty($accessListenerPropertyName)) {
			$accessListenerPropertyName += self::PROXY_DUP_SUFFIX;
		}
		$this->accessListenerPropertyNames[$proxyNamespaceName . '\\' . $proxyClassName] = $accessListenerPropertyName;

		$accessMethodName = self::PROXY_TRIGGER_ACCESS_METHOD;
		while ($class->hasMethod($accessMethodName)) {
			$accessMethodName += self::PROXY_DUP_SUFFIX;
		}

		$phpProxyStr = 'namespace ' . $proxyNamespaceName . ' { '
		. 'class ' . $proxyClassName . ' extends \\' . $class->getName() . ' implements \n2n\persistence\orm\proxy\EntityProxy {'
		. 'private $' . $accessListenerPropertyName . ';'
		. 'private function ' . $accessMethodName . '() {'
		. 'if (null === $this->' . $accessListenerPropertyName . ') return;'
		. '$this->' . $accessListenerPropertyName . '->onAccess($this);'
		. '$this->' . $accessListenerPropertyName . ' = null;'
		. '}';

		foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			if ($method->isStatic()) continue;
				
			$phpParameterStrs = array();
			$phpParameterCallStrs = array();
			foreach ($method->getParameters() as $parameter) {				
				$phpParameterStrs[] = $this->buildPhpParamerStr($parameter);
				$phpParameterCallStrs[] = $this->buildDollar($parameter, false) . $parameter->getName();
			}
				
			$methodReturnTypeStr = '';
			if (null !== ($returnType = $method->getReturnType())) {
				$methodReturnTypeStr .= ': ' . $this->buildTypeStr($returnType);
			}
			
			$phpProxyStr .= "\r\n" . 'public function ' . $method->getName() . '(' . implode(', ', $phpParameterStrs) . ') ' 
					. $methodReturnTypeStr . ' { '
					. '$this->' . self::PROXY_TRIGGER_ACCESS_METHOD . '(); '
					. 'return parent::' . $method->getName() . '(' . implode(', ', $phpParameterCallStrs) . '); '
					. '}';
		}

		$phpProxyStr .= '}'
		. '}';
		
		if (false === eval($phpProxyStr)) {
			die();
		}
		
		return new \ReflectionClass($proxyNamespaceName . '\\' . $proxyClassName);
	}

	private function buildDollar(\ReflectionParameter $parameter, bool $includeRef) {
		$str = '';
		if ($parameter->isVariadic()) {
			$str .= '...';
		}
		if ($includeRef && $parameter->isPassedByReference()) {
			$str .= '&';
		}
		$str .= '$';
		return $str;
	}
	
	private function buildPhpParamerStr(\ReflectionParameter $parameter) {
		$phpParamStr = '';
		
		if (null !== ($type = $parameter->getType())) {
			$phpParamStr .= $this->buildTypeStr($type) . ' ';
		}
		
		$phpParamStr .= $this->buildDollar($parameter, true) . $parameter->getName();
		
		if ($parameter->isDefaultValueAvailable()) {
			if ($parameter->isDefaultValueConstant()) {
				
				$phpParamStr .= ' = ' . $this->buildDefaultConstStr($parameter->getDefaultValueConstantName());
			} else {
				$phpParamStr .= ' = ' . $this->buildValueStr($parameter->getDefaultValue());
			}
		}
		
		return $phpParamStr;
	}

	private function buildTypeStr(\ReflectionType $type) {
		$prefix = $type->allowsNull() ? '?' : '';
		
		if ($type->isBuiltin()) {
			return $prefix . $type->getName();
		}
	
		return $prefix . '\\' . $type->getName();
	}
	
	private function buildDefaultConstStr($defaultConstName) {
		if (StringUtils::startsWith('self::', $defaultConstName)) {
			return $defaultConstName;
		}
		
		return '\\' . $defaultConstName;
		
	}
	
	private function buildValueStr($value) {
		if ($value === null) {
			return 'null';
		} else if (is_string($value)) {
			return '\'' . $value . '\'';
		} else if (is_bool($value)) {
			return $value ? 'true' : 'false';
		} else if (is_numeric($value)) {
			return (string) $value;
		} else if (is_array($value)) {
			$fieldStrs = array();
			foreach ($value as $key => $fieldValue) {
				$fieldStrs[] = buildValueStr($key) . ' => ' . buildValueStr($fieldValue);
			}
			return 'array(' . implode(', ', $fieldStrs) . ')';
		}
	
		throw new \InvalidArgumentException('Cannot print value str of type: ' . gettype($value));
	}
	
	private function determineDefaultValue($defaultValue) {
		if (is_null($defaultValue)) {
			return 'null';
		}
		
		if (is_numeric($defaultValue)) {
			return $defaultValue;
		}
		
		if (is_scalar($defaultValue)) {
			// @todo \"
			return '\'' . addslashes($defaultValue) . '\'';
		}
		
		if (is_array($defaultValue)) {
			$fields = array();
			foreach ($defaultValue as $key => $value) {
				$fields[] = $this->determineDefaultValue($key) . ' => ' . $value;
			}
			return implode(', ', $fields);
		}
		
		throw new NotYetImplementedException();
	}
}

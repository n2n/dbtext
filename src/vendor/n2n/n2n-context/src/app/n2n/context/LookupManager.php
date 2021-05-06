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
namespace n2n\context;

use n2n\util\UnserializationFailedException;
use n2n\core\ShutdownListener;
use n2n\core\N2N;
use n2n\reflection\ReflectionUtils;
use n2n\core\container\N2nContext;
use n2n\reflection\ReflectionContext;
use n2n\util\cache\CacheStore;
use n2n\web\http\HttpContextNotAvailableException;
use n2n\util\StringUtils;
use n2n\util\ex\IllegalStateException;
use n2n\reflection\annotation\PropertyAnnotation;
use n2n\reflection\magic\MagicUtils;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\web\http\Session;
use n2n\context\annotation\AnnoApplicationScoped;

class LookupManager implements ShutdownListener {
	const SESSION_KEY_PREFIX = 'lookupManager.sessionScoped.';
	const SESSION_CLASS_PROPERTY_KEY_SEPARATOR = '.';
	const ON_SERIALIZE_METHOD = '_onSerialize';
	const ON_UNSERIALIZE_METHOD = '_onUnserialize';
	
	private $n2nContext;
	private $session;
	private $cacheStore;
	private $shutdownClosures = array();
	
	private $requestScope = array();
	private $sessionScope = array();
	private $applicationScope = array();
	/**
	 * @param N2nContext $n2nContext
	 */
	public function __construct(N2nContext $n2nContext) {
		$this->n2nContext = $n2nContext;
	}
	
	public function clear() {
		$this->requestScope = array();
		$this->sessionScope = array();
		$this->applicationScope = array();
	}
	/**
	 * @return boolean 
	 */
	public function hasCacheStore() {
		return $this->cacheStore !== null;
	}
	/**
	 * @throws IllegalStateException
	 * @return CacheStore
	 */
	public function getCacheStore() {
		if ($this->cacheStore === null) {
			$this->cacheStore = $this->n2nContext->getAppCache()->lookupCacheStore(self::class);
		}
		
		return $this->cacheStore;
	}
	/**
	 * @param CacheStore $cacheStore
	 */
	public function setCacheStore(CacheStore $cacheStore) {
		$this->cacheStore = $cacheStore;
	}
	/**
	 * @param string $className
	 * @throws LookupFailedException
	 * @return Lookupable
	 */
	public function lookup($className) {
		if (empty($className)) {
			throw new LookupFailedException('Name is empty.');
		}
		$class = null;
		if ($className instanceof \ReflectionClass) {
			$class = $className;
		} else {
			$class = ReflectionUtils::createReflectionClass($className);
		}
		
		return $this->lookupByClass($class);
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 * @throws ModelErrorException
	 * @return Lookupable
	 */
	public function lookupByClass(\ReflectionClass $class) {
		if ($class->implementsInterface(RequestScoped::class)) {
			return $this->checkoutRequestScoped($class);
		}
		
		if ($class->implementsInterface(SessionScoped::class)) {
			return $this->checkoutSessionModel($class, $class->implementsInterface(AutoSerializable::class));
		}
		
		if ($class->implementsInterface(ApplicationScoped::class)) {
			return $this->checkoutApplicationModel($class, $class->implementsInterface(AutoSerializable::class));
		}
		
		if ($class->implementsInterface(Lookupable::class)) {
			return $this->checkoutLookupable($class);
		}
		
		throw new LookupFailedException('Class is not marked as lookupable: ' . $class->getName());
	}
	/**
	 * @param PropertyAnnotation $annotation
	 * @return ModelErrorException
	 */
	private function createErrorException(PropertyAnnotation $annotation) {
		return new ModelErrorException('Annotation disallowed for simple Lookupables',
				$annotation->getFileName(), $annotation->getLine());
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws ModelErrorException
	 * @return Lookupable
	 */
	private function checkoutLookupable(\ReflectionClass $class) {
		$annotationSet = ReflectionContext::getAnnotationSet($class);
		foreach ($annotationSet->getPropertyAnnotationsByName('n2n\context\annotation\AnnoSessionScoped') as $annotation) {
			throw $this->createErrorException($annotation);
		}
		foreach ($annotationSet->getPropertyAnnotationsByName('n2n\context\annotation\AnnoApplicationScoped') as $annotation) {
			throw $this->createErrorException($annotation);
		}
		
		$obj = ReflectionUtils::createObject($class);
		$this->n2nContext->magicInit($obj);
		return $obj;
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 * @return RequestScoped
	 */
	private function checkoutRequestScoped(\ReflectionClass $class) {
		if (isset($this->requestScope[$class->getName()])) {
			return $this->requestScope[$class->getName()];
		}
		
		$obj = ReflectionUtils::createObject($class);
		$this->checkForSessionProperties($class, $obj);
		$this->checkForApplicationProperties($class, $obj);
		$this->requestScope[$class->getName()] = $obj;
		$this->n2nContext->magicInit($obj);
		return $obj;
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 */
	private function checkForSessionProperties(\ReflectionClass $class, $obj) {
		$annotationSet = ReflectionContext::getAnnotationSet($class);
		if (!$annotationSet->containsPropertyAnnotationName('n2n\context\annotation\AnnoSessionScoped')) {
			return;
		}
		
		$session = null;
		try {
			$session = $this->n2nContext->getHttpContext()->getSession();
		} catch (HttpContextNotAvailableException $e) {
			throw new LookupFailedException('Could not check out session properties for: ' 
					. $class->getName(), 0, $e);
		}
		
		foreach ($annotationSet->getPropertyAnnotationsByName('n2n\context\annotation\AnnoSessionScoped') as $sessionScopedAnno) {
			$property = $sessionScopedAnno->getAnnotatedProperty();
			$propertyName = $property->getName();
			
			$property->setAccessible(true);
			
			$key = self::SESSION_KEY_PREFIX 
					. $class->getName() . self::SESSION_CLASS_PROPERTY_KEY_SEPARATOR . $propertyName;
			if ($session->has(N2N::NS, $key)) {
				try {
					$property->setValue($obj, StringUtils::unserialize($session->get(N2N::NS, $key)));
				} catch (UnserializationFailedException $e) { 
					$session->remove(N2N::NS, $key);	
				}
			}
		
			$this->shutdownClosures[] = function () use ($key, $session, $property, $obj) {
				$session->set(N2N::NS, $key, serialize($property->getValue($obj)));
				$property->setValue($obj, null);
			};
		}
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 * @return SessionScoped
	 */
	private function checkoutSessionModel(\ReflectionClass $class, $autoSerializable) {
		if (isset($this->sessionScope[$class->getName()])) {
			return $this->sessionScope[$class->getName()];
		}
		
		$session = null;
		try {
			$session = $this->n2nContext->getHttpContext()->getSession();
		} catch (HttpContextNotAvailableException $e) {
			throw new LookupFailedException('Could not check out session model: ' . $class->getName(), 0, $e);
		}

		$key = self::SESSION_KEY_PREFIX . $class->getName();
		$obj = $this->readSessionModel($session, $key, $class, $autoSerializable);
		
		if ($obj === null) {
			$obj = ReflectionUtils::createObject($class);
			$this->checkForApplicationProperties($class, $obj);
			$this->n2nContext->magicInit($obj);
		}

		$this->shutdownClosures[] = function () use ($key, $session, $class, $obj, $autoSerializable) {
			$this->writeSessionModel($session, $key, $obj, $class, $autoSerializable);
		};
		
		return $this->sessionScope[$class->getName()] = $obj;
	}
	
	private function readSessionModel(Session $session, $key, \ReflectionClass $class, $autoSerializable) {
		if (!$session->has(N2N::NS, $key)) return null;
		
		$serData = $session->get(N2N::NS, $key);
		
		if ($autoSerializable) {
			try {
				$obj = StringUtils::unserialize($serData);
				if (ReflectionUtils::isObjectA($obj, $class)) { 
					$this->checkForApplicationProperties($class, $obj);
					return $obj;
				}
			} catch (UnserializationFailedException $e) {}
			
			$session->remove(N2N::NS, $key);
			return null;
		} 
		
		$obj = ReflectionUtils::createObject($class);
		$this->checkForApplicationProperties($class, $obj);
		
		try {
			$this->callOnUnserialize($class, $obj, SerDataReader::createFromSerializedStr($serData));
		} catch (UnserializationFailedException $e) {
			$session->remove(N2N::NS, $key);
			throw new LookupFailedException('Falied to unserialize session model: ' . $class->getName(), 0, $e);
		}
		
		return $obj;
	}

	private function writeSessionModel(Session $session, $key, $obj, \ReflectionClass $class, $autoSerializable) {
		if ($autoSerializable) {
			$session->set(N2N::NS, $key, serialize($obj));
			return;
		}

		$serDataWriter = new SerDataWriter();
		$this->callOnSerialize($class, $obj, $serDataWriter);
		$session->set(N2N::NS, $key, $serDataWriter->serialize());
	}
	/**
	 * @param \ReflectionClass $class
	 * @param Lookupable $obj
	 * @throws LookupFailedException
	 */
	private function checkForApplicationProperties(\ReflectionClass $class, $obj) {
		$annotationSet = ReflectionContext::getAnnotationSet($class);
		if (!$annotationSet->containsPropertyAnnotationName(AnnoApplicationScoped::class))  {
			return;
		}
	
		try {
			$this->getCacheStore();
		} catch (IllegalStateException $e) {
			throw new LookupFailedException('Could not check out application scoped properties for: ' . $class->getName(), 0, $e);
		}
	
		$className = $class->getName();
		foreach ($annotationSet->getPropertyAnnotationsByName(AnnoApplicationScoped::class) as $applicationScopedAnno) {
			$property = $applicationScopedAnno->getAnnotatedProperty();
			$characteristics = array('prop' => $property->getName());
				
			$property->setAccessible(true);
				
			$propValueSer = null;
			if (null !== ($cacheItem = $this->cacheStore->get($className, $characteristics))) {
				$propValueSer = $cacheItem->getData();
				try {
					$property->setValue($obj, StringUtils::unserialize($propValueSer));	
				} catch (UnserializationFailedException $e) {
					$this->cacheStore->remove($className, $characteristics);
				}
			}

			$this->shutdownClosures[] = function () use ($className, $characteristics, $propValueSer, $property, $obj) {
				$newPropValueSer = serialize($property->getValue($obj));
				$property->setValue($obj, null);
				
				if ($newPropValueSer != $propValueSer) {
					$this->cacheStore->store($className, $characteristics, $newPropValueSer);
				}
			};
		}
	}
	/**
	 * @param \ReflectionClass $class
	 * @throws LookupFailedException
	 * @return ApplicationScoped
	 */
	private function checkoutApplicationModel(\ReflectionClass $class, $autoSerializable) {
		try {
			$this->getCacheStore();
		} catch (IllegalStateException $e) {
			throw new LookupFailedException('Could not check out application scoped object: ' . $class->getName(), 0, $e);
		}
		
		$className = $class->getName();
		if (isset($this->applicationScope[$className])) {
			return $this->applicationScope[$className];
		}
		
		$serData = null;
		$obj = $this->readApplicationModel($class, $autoSerializable, $serData);
		
		if ($obj === null) {
			$obj = ReflectionUtils::createObject($class);
			$this->checkForSessionProperties($class, $obj);
			$this->n2nContext->magicInit($obj);
		}
		
		$this->shutdownClosures[] = function () use ($class, $obj, $autoSerializable, $serData) {
			$this->writeApplicationModel($obj, $class, $autoSerializable, $serData);
		};
		
		return $this->applicationScope[$class->getName()] = $obj;
	}
	

	private function readApplicationModel(\ReflectionClass $class, $autoSerializable, &$serData) {
		$className = $class->getName();
		
		$cacheItem = $this->cacheStore->get($className, array());
		if (null === $cacheItem) return null;
		
		$serData = $cacheItem->getData();
		
		if ($autoSerializable) {
			try {
				$obj = StringUtils::unserialize($serData);
					
				if (ReflectionUtils::isObjectA($obj, $class)) {
					$this->checkForSessionProperties($class, $obj);
					return $obj;	
				}
			} catch (UnserializationFailedException $e) {}
			
			$this->cacheStore->remove($className, array());
			return null;
		}	
	
		$obj = ReflectionUtils::createObject($class);
		$this->checkForSessionProperties($class, $obj);
	
		try {
			$this->callOnUnserialize($class, $obj, SerDataReader::createFromSerializedStr($serData));
		} catch (UnserializationFailedException $e) {
			$this->cacheStore->remove($className, array());
			throw new LookupFailedException('Falied to unserialize application model: ' . $class->getName(), 0, $e);
		}
	
		return $obj;
	}
	
	private function writeApplicationModel($obj, \ReflectionClass $class, $autoSerializable, $oldSerData) {
		$className = $class->getName();
		
		$serData = null;
		if ($autoSerializable) {
			$serData = serialize($obj);
		} else {
			$serDataWriter = new SerDataWriter();
			$this->callOnSerialize($class, $obj, $serDataWriter);
			$serData = $serDataWriter->serialize();
		}
		
		if ($serData != $oldSerData) {
			$this->cacheStore->store($className, array(), $serData);
		}
	}
	
	private function callOnUnserialize(\ReflectionClass $class, $obj, SerDataReader $serDataReader) {
		$magicMethodInvoker = new MagicMethodInvoker($this->n2nContext);
		$magicMethodInvoker->setClassParamObject(get_class($serDataReader), $serDataReader);
		$this->callMagcMethods($class, self::ON_UNSERIALIZE_METHOD, $obj, $magicMethodInvoker);
	}
	
	private function callOnSerialize(\ReflectionClass $class, $obj, SerDataWriter $serDataWriter) {
		$magicMethodInvoker = new MagicMethodInvoker($this->n2nContext);
		$magicMethodInvoker->setClassParamObject(get_class($serDataWriter), $serDataWriter);
		$this->callMagcMethods($class, self::ON_SERIALIZE_METHOD, $obj, $magicMethodInvoker);
	}
		
	private function callMagcMethods(\ReflectionClass $class, $methodName, $obj, MagicMethodInvoker $magicMethodInvoker) {
		$methods = ReflectionUtils::extractMethodHierarchy($class, $methodName);
		
		if (0 == count($methods)) {
			throw new ModelErrorException('Magic method missing: ' . $class->getName() . '::'
					. $methodName . '()', $class->getFileName(), $class->getStartLine());
		}
		
		foreach ($methods as $method) {
			MagicUtils::validateMagicMethodSignature($method);

			$method->setAccessible(true);
			$magicMethodInvoker->invoke($obj, $method);
		}
	}
	/* (non-PHPdoc)
	 * @see \n2n\core\ShutdownListener::onShutdown()
	 */
	public function onShutdown() {
		foreach ($this->shutdownClosures as $shutdownClosure) {
			$shutdownClosure();
		}
	}
}

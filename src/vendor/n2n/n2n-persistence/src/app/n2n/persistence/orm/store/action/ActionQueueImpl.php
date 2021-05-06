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
namespace n2n\persistence\orm\store\action;

use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\LifecycleEvent;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\persistence\orm\LifecycleListener;
use n2n\util\magic\MagicContext;
use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\LifecycleUtils;
use n2n\util\ex\IllegalStateException;
use n2n\reflection\magic\MagicUtils;

class ActionQueueImpl implements ActionQueue {
	protected $em;
	protected $magicContext;
	protected $actionQueueListeners = array();
	protected $actionJobs = array();
	protected $atStartClosures = array();
	protected $atEndClosures = array();
	protected $atPrepareCycleEndClosures = array();
	private $persistActionPool;
	private $removeActionPool;
	private $flushing = false;
	private $bufferedEvents = array();
	private $entityListeners = array();
	private $lifecylceListeners = array();
	
	const MAGIC_ENTITY_OBJ_PARAM = 'entityObj';
	
	public function __construct(EntityManager $em, MagicContext $magicContext = null) {
		$this->em = $em;
		$this->magicContext = $magicContext;
		$this->persistActionPool = new PersistActionPool($this);
		$this->removeActionPool = new RemoveActionPool($this, $this->persistActionPool);
	}
	
	public function getEntityManager() {
		return $this->em;
	}
	
	public function removeAction($entity) {
		$this->persistActionPool->removeAction($entity);
		$this->removeActionPool->removeAction($entity);
	}
	
	public function containsPersistAction($entity) {
		return $this->persistActionPool->containsAction($entity);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\ActionQueue::getOrCreatePersistAction()
	 */
	public function getOrCreatePersistAction($entity) {
		$this->removeActionPool->removeAction($entity);
		return $this->persistActionPool->getOrCreateAction($entity);
	}

	public function getPersistAction($entity) {
		return $this->persistActionPool->getAction($entity);
	}
	
	public function containsRemoveAction($entity) {
		return $this->removeActionPool->containsAction($entity);
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\action\ActionQueue::getOrCreateRemoveAction()
	 */
	public function getOrCreateRemoveAction($entity) {
		$this->persistActionPool->removeAction($entity);
		return $this->removeActionPool->getOrCreateAction($entity);
	}

	public function getRemoveAction($entity) {
		return $this->removeActionPool->getAction($entity);
	}
	
	public function add(Action $action) {
		$this->actionJobs[spl_object_hash($action)] = $action;
	}
	
	public function remove(Action $action) {
		unset($this->actionJobs[spl_object_hash($action)]);
	}
						
// 	public function announceLifecycleEvent(LifecycleEvent $event) {
// 		foreach ($this->actionQueueListeners as $actionQueueListener) {
// 			$actionQueueListener->onLifecycleEvent($event);
// 		}
// 	}
	
// 	protected function supplyMetaIdOnInit(ActionMeta $meta, Entity $object) {
// 		$that = $this;
// 		$this->initClosures[] = function() use ($that, $meta, $object) {
// 			$meta->setId($that->getPersistenceContext()->getIdOfEntity($object));
// 		};
// 	}

	public function executeAtStart(\Closure $closure) {
		$this->atStartClosures[] = $closure;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\Action::executeAtEnd()
	 */
	public function executeAtEnd(\Closure $closure) {
		$this->atEndClosures[] = $closure;
	}
	
	public function executeAtPrepareCycleEnd(\Closure $closure) {
		$this->atPrepareCycleEndClosures[] = $closure;
	}
	
	protected function triggerAtStartClosures() {
		while (null !== ($closure = array_shift($this->atStartClosures))) {
			$closure($this);
		}
	}

	protected function triggerAtEndClosures() {
		while (null !== ($closure = array_shift($this->atEndClosures))) {
			$closure($this);
		}
	}
	
	protected function triggerAtPrepareCycleEndClosures() {
		if (empty($this->atPrepareCycleEndClosures)) return false;
		
		while (null !== ($closure = array_shift($this->atPrepareCycleEndClosures))) {
			$closure($this);
		}
		
		return true;
	}
	
	public function flush() {
		IllegalStateException::assertTrue(!$this->flushing);
		$this->flushing = true;
		
		$this->triggerAtStartClosures();
			
		do {
			do {
				$this->persistActionPool->prepareSupplyJobs();
			} while ($this->removeActionPool->prepareSupplyJobs() || $this->triggerAtPrepareCycleEndClosures());
		} while ($this->triggerPreFinilizeAttempt() 
				&& ($this->persistActionPool->prepareSupplyJobs() || $this->removeActionPool->prepareSupplyJobs()));
	
		$this->persistActionPool->freeze();
		$this->removeActionPool->freeze();
		
		$this->persistActionPool->supply();
		$this->removeActionPool->supply();
				
		while (null != ($job = array_shift($this->actionJobs))) {
			$job->execute();
		}
		
		$this->persistActionPool->clear();
		$this->removeActionPool->clear();
				
		$this->triggerAtEndClosures();
		
		$this->flushing = false;
		
		while (null !== ($event = array_shift($this->bufferedEvents))) {
			$this->triggerLifecycleEvent($event);
		}
	}
	
	public function commit() {
		$this->em->getPersistenceContext()->detachNotManagedEntityObjs();
	}
	
	public function clear() {
		$this->removeActionPool->clear();
		$this->persistActionPool->clear();
		$this->actionJobs = [];
	}
		
	public function announceLifecycleEvent(LifecycleEvent $event) {
		switch ($event->getType()) {
			case LifecycleEvent::PRE_PERSIST:
			case LifecycleEvent::PRE_REMOVE:
			case LifecycleEvent::PRE_UPDATE:
				return $this->triggerLifecycleEvent($event);
				
			case LifecycleEvent::POST_LOAD:
				$this->triggerLifecycleEvent($event);
				if ($this->flushing) {
					$this->persistActionPool->getOrCreateAction($event->getEntityObj(), false);
				}
				break;
				
			default:
				IllegalStateException::assertTrue($this->flushing);
				$this->bufferedEvents[] = $event;
		}
		
		return false;
	}		

	private function triggerLifecycleEvent(LifecycleEvent $event) {
		$triggered = $this->triggerLifecycleCallbacks($event);
		
		foreach ($this->lifecylceListeners as $listener) {
			$listener->onLifecycleEvent($event, $this->em);
			$triggered = true;
		}
		
		return $triggered;
	}
	
	private function triggerLifecycleCallbacks(LifecycleEvent $event) {
		$eventType = $event->getType();
		$entityModel = $event->getEntityModel();
		
		$methods = $entityModel->getLifecycleMethodsByEventType($eventType);
		$entityListenerClasses = $entityModel->getEntityListenerClasses();
		
		if (empty($methods) && empty($entityListenerClasses)) {
			return false;
		}
		
		$entityObj = $event->getEntityObj();
		$methodInvoked = false;
		
		$methodInvoker = new MagicMethodInvoker($this->magicContext);
		$methodInvoker->setClassParamObject('n2n\persistence\orm\model\EntityModel', $entityModel);
		$paramClass = $entityModel->getClass();
		do {
			$methodInvoker->setClassParamObject($paramClass->getName(), $entityObj);
		} while (false !== ($paramClass = $paramClass->getParentClass()));
		$methodInvoker->setParamValue(self::MAGIC_ENTITY_OBJ_PARAM, $entityObj);
		$methodInvoker->setClassParamObject('n2n\persistence\orm\EntityManager', $this->em);
		
		foreach ($methods as $method) {
			$method->setAccessible(true);
			$methodInvoker->invoke($entityObj, $method);
			$methodInvoked = true;
		}
		
		foreach ($entityListenerClasses as $entityListenerClass) {
			$callbackMethod = LifecycleUtils::findEventMethod($entityListenerClass, $eventType);
			if ($callbackMethod !== null) {
				$callbackMethod->setAccessible(true);
				$methodInvoker->invoke($this->lookupEntityListener($entityListenerClass), $callbackMethod);
				$methodInvoked = true;
			}
		}
		
		return $methodInvoked;
	}
	
	private function lookupEntityListener(\ReflectionClass $entityListenerClass) {
		$className = $entityListenerClass->getName();
		if (!isset($this->entityListeners[$className])) {
			$this->entityListeners[$className] = $entityListener = ReflectionUtils::createObject($entityListenerClass);
			MagicUtils::init($entityListener, $this->magicContext);
		}
		
		return $this->entityListeners[$className];
	}
	
	private function triggerPreFinilizeAttempt() {
		$triggered = false;
		
		foreach ($this->lifecylceListeners as $listener) {
			$listener->onPreFinalized($this->em);
			$triggered = true;
		}
		
		return $triggered;
	}
	
	public function registerLifecycleListener(LifecycleListener $listener) {
		$this->lifecylceListeners[spl_object_hash($listener)] = $listener;
	}
	
	public function unregisterLifecycleListener(LifecycleListener $listener) {
		unset($this->lifecylceListeners[spl_object_hash($listener)]);
	}
}

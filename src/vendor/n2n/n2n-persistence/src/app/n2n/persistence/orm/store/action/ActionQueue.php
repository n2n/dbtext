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

use n2n\persistence\orm\LifecycleEvent;
use n2n\persistence\orm\LifecycleListener;

interface ActionQueue {
	/**
	 * @return \n2n\persistence\orm\EntityManager 
	 */
	public function getEntityManager();
	/**
	 * @param Action $action
	 */
	public function add(Action $action);
	/**
	 * @param object $entity
	 * @param bool $manageIfTransient
	 * @return PersistAction
	 */
	public function getPersistAction($entity);
	/**
	 * @param object $entity
	 * @param bool $manageIfTransient
	 * @return PersistAction
	 */
	public function getOrCreatePersistAction($entity);
	/**
	 * @param object $object
	 */
	public function containsPersistAction($object);
	/**
	 * @param $entity
	 * @return RemoveAction returns null if object already removed or has state new
	 */
	public function getRemoveAction($object);
	/**
	 * @param $entity
	 * @return RemoveAction returns null if object already removed or has state new
	 */
	public function getOrCreateRemoveAction($object);
	/**
	 * @param object $object
	 */
	public function containsRemoveAction($object);
	/**
	 * @param object $entity
	 * @param string $type
	 */
	public function announceLifecycleEvent(LifecycleEvent $e);

	/**
	 * @param LifecycleListener $listener
	 */
	public function registerLifecycleListener(LifecycleListener $listener);
	
	/**
	 * @param LifecycleListener $listener
	 */
	public function unregisterLifecycleListener(LifecycleListener $listener);
	
	/**
	 * 
	 */
	public function flush();
	
	/**
	 * 
	 */
	public function commit();
	
	/**
	 * 
	 */
	public function clear();
	
	/**
	 * @param \Closure $closure
	 */
	public function executeAtStart(\Closure $closure);
	
	/**
	 * @param \Closure $closure
	 */
	public function executeAtEnd(\Closure $closure);
	
	/**
	 * @param \Closure $closure
	 */
	public function executeAtPrepareCycleEnd(\Closure $closure);
}

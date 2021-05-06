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
namespace n2n\persistence\orm;

use n2n\persistence\orm\store\PersistenceOperationException;
use n2n\persistence\orm\store\LoadingQueue;

interface EntityManager {
	const SIMPLE_ALIAS = 'e';
	
	const SCOPE_EXTENDED = 'extended';
	const SCOPE_TRANSACTION = 'transaction'; 
	/**
	 * @return \n2n\persistence\Pdo 
	 */
	public function getPdo();
	/**
	 * @return \n2n\persistence\orm\store\PersistenceContext
	 */
	public function getPersistenceContext();
	/**
	 * @return \n2n\persistence\orm\model\EntityModelManager
	 */
	public function getEntityModelManager();
	/**
	 * @return \n2n\util\magic\MagicContext 
	 */
	public function getMagicContext();
	
	/**
	 * @return \n2n\persistence\orm\store\action\ActionQueue 
	 */
	public function getActionQueue();
	
	/**
	 * @return LoadingQueue 
	 */
	public function getLoadingQueue();
	/**
	 *
	 * @param \ReflectionClass $class
	 * @param string $entityAlias
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function createCriteria();
	/**
	 *
	 * @param \ReflectionClass $class
	 * @param array $matches
	 * @param array $order
	 * @param int $limit
	 * @param int $num
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function createSimpleCriteria(\ReflectionClass $class, array $matches = null, 
			array $order = null, $limit = null, $num = null);
	/**
	 * @param string $nql
	 * @param array $params
	 * @return \n2n\persistence\orm\criteria\Criteria
	 * @throws \n2n\persistence\orm\nql\NqlParseException
	 */
	public function createNqlCriteria($nql, array $params = array());
	/**
	 * @param \ReflectionClass $class
	 * @param mixed $id
	 * @return object
	 */
	public function find(\ReflectionClass $class, $id);
	/**
	 * Get an instance, whose state may be lazily fetched.
	 * @param \ReflectionClass $class
	 * @param mixed $id
	 * @return object
	 */
	public function getReference(\ReflectionClass $class, $id);
	/**
	 * Merge the state of the given entity into the current persistence context. 
	 * @param object $entity
	 * @return object the managed instance that the state was merged to 
	 */
	public function merge($entity);
	/**
	 * Make an instance managed and persistent.
	 * @param object $entity
	 * @return object
	 */
	public function persist($entity);
	/**
	 * Refresh the state of the instance from the database, overwriting changes made to the entity, if any. 
	 * @param object $entity
	 * @throws PersistenceOperationException
	 * @throws EntityNotFoundException if the entity no longer exists in the database
	 */
	public function refresh($entity);
	/**
	 * Remove the entity instance. 
	 * @param object $entity
	 */
	public function remove($entity);
	/**
	 * Remove the given entity from the persistence context, causing a managed entity to become detached. Unflushed 
	 * changes made to the entity if any (including removal of the entity), will not be synchronized to the database. 
	 * Entities which previously referenced the detached entity will continue to reference it. 
	 * @param object $entity
	 */
	public function detach($entity);
	/**
	 * Synchronize the persistence context to the underlying database.
	 */
	public function flush();
	/**
	 * Check if the instance is a managed entity instance belonging to the current persistence context. 
	 * @param object $entity
	 * @return boolean indicating if entity is in persistence context 
	 */
	public function contains($entity);
	/**
	 * Clear the persistence context, causing all managed entities to become detached. Changes made to entities that
	 * have not been flushed to the database will not be persisted.
	 */
	public function clear();
	/**
	 * 
	 */
	public function close();
	/**
	 * Determine whether the entity manager is open. 
	 * @return boolean
	 */
	public function isOpen();
// 	/**
// 	 * 
// 	 * @param object $entity
// 	 * @param object $newEntity
// 	 */
// 	public function swap($entity, $newEntity);	
	
	public function registerLifecycleListener(LifecycleListener $listener);
	
	public function getScope(): string;
}

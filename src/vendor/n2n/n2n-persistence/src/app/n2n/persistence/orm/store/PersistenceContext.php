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
namespace n2n\persistence\orm\store;

use n2n\persistence\orm\model\EntityModel;
use n2n\reflection\ReflectionUtils;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\model\EntityPropertyCollection;
use n2n\persistence\orm\proxy\EntityProxyManager;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\util\type\ArgUtils;
use n2n\persistence\orm\proxy\LazyInitialisationException;
use n2n\persistence\orm\proxy\EntityProxyAccessListener;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\proxy\EntityProxyInitializationException;
use n2n\persistence\orm\proxy\CanNotCreateEntityProxyClassException;
use n2n\reflection\ObjectCreationFailedException;
use n2n\persistence\orm\EntityCreationFailedException;
use n2n\persistence\orm\proxy\EntityProxy;
use n2n\persistence\orm\property\BasicEntityProperty;

class PersistenceContext {
	private $entityProxyManager;
	
	private $managedEntityObjs = array();
	private $removedEntityObjs = array();
	
	private $entityValueHashCols = array();
	private $entityIdReps = array();
	private $entityIdentifiers = array();
	private $entityModels = array();
	
	public function __construct(EntityProxyManager $entityProxyManager) {
		$this->entityProxyManager = $entityProxyManager;
	}
	/**
	 * @return EntityProxyManager
	 */
	public function getEntityProxyManager() {
		return $this->entityProxyManager;
	}
	/**
	 * 
	 */
	public function clear() {
		$this->managedEntityObjs = array();
		$this->entityValueHashCols = array();
		
		$this->removedEntityObjs = array();
		
		$this->entityIdReps = array();
		$this->entityIdentifiers = array();
		$this->entityModels = array();
	}
	
	public function getIdByEntityObj($entityObj) {
		ArgUtils::assertTrue(is_object($entityObj));
		
		$objHash = spl_object_hash($entityObj);
		if (isset($this->entityIdReps[$objHash])) {
			return $this->entityIdReps[$objHash];
		}
		
		return null;
	}
	
	public function getEntityById(EntityModel $entityModel, $id) {
		return $this->getEntityByIdRep($entityModel, 
				$entityModel->getIdDef()->getEntityProperty()->valueToRep($id));
	}
	
	public function getEntityByIdRep(EntityModel $entityModel, string $idRep) {
		$className = $entityModel->getClass()->getName();
		
		if (isset($this->entityIdentifiers[$className][$idRep])) {
			return $this->entityIdentifiers[$className][$idRep];
		}
		
		return null;
	}
	
	public function getRemovedEntity(EntityModel $entityModel, $id) {
		if ($id === null) return null;
	
		$idRep = $entityModel->getIdDef()->getEntityProperty()->valueToRep($id);
	
		return $this->getRemovedEntityByIdRep($entityModel, $idRep);
	}
	
	public function getRemovedEntityByIdRep(EntityModel $entityModel, string $idRep) {
		$className = $entityModel->getClass()->getName();
		
		if (!isset($this->entityIdentifiers[$className][$idRep])) return null;
		
		$objHash = spl_object_hash($this->entityIdentifiers[$className][$idRep]);
		if (isset($this->removedEntityObjs[$objHash])) {
			return $this->removedEntityObjs[$objHash];
		}
		
		return null;
	}
	
	/**
	 * @param $entity
	 * @return \n2n\persistence\orm\store\EntityInfo
	 */
	public function getEntityInfo($entityObj, EntityModelManager $entityModelManager) {
		ArgUtils::assertTrue(is_object($entityObj));
		
		$objectHash = spl_object_hash($entityObj);
		$entityModel = null;
		$id = null;
		if (isset($this->entityIdReps[$objectHash])) {
			$id = $this->entityModels[$objectHash]->getIdDef()->getEntityProperty()->repToValue(
					$this->entityIdReps[$objectHash]);	
		}
		
		if (isset($this->managedEntityObjs[$objectHash])) {
			return new EntityInfo(EntityInfo::STATE_MANAGED, $this->entityModels[$objectHash], $id);
		}
			
		if (isset($this->removedEntityObjs[$objectHash])) {
			return new EntityInfo(EntityInfo::STATE_REMOVED, $this->entityModels[$objectHash], $id);
		}
		
		$entityModel = $entityModelManager->getEntityModelByEntityObj($entityObj);
		$idDef = $entityModel->getIdDef();
		$id = $idDef->getEntityProperty()->readValue($entityObj);
	
		if ($idDef->isGenerated()) {
			return new EntityInfo(($id === null ? EntityInfo::STATE_NEW : EntityInfo::STATE_DETACHED), 
					$entityModel, $id);
		}
	
		return new EntityInfo(EntityInfo::STATE_NEW, $entityModel, $id);
	}
	
	public function getManagedEntityObjs() {
		return $this->managedEntityObjs;
	}
	
	public function getRemovedEntityObjs() {
		return $this->removedEntityObjs;
	}
	
	public function getManagedEntityObjByIdRep(EntityModel $entityModel, $idRep) {
		$className = $entityModel->getClass()->getName();
		
		if (isset($this->entityIdentifiers[$className][$idRep])
				&& $this->containsManagedEntityObj($this->entityIdentifiers[$className][$idRep])) {
			return $this->entityIdentifiers[$className][$idRep];
		}
		
		return null;
	}
	
	public function getManagedEntityObj(EntityModel $entityModel, $id) {
		if ($id === null) return null;
		
		$idRep = $entityModel->getIdDef()->getEntityProperty()->valueToRep($id);
		
		return $this->getManagedEntityObjByIdRep($entityModel, $idRep);
	}
	
	public function getOrCreateManagedEntity(EntityModel $entityModel, $id) {
		if (null !== ($entity = $this->getManagedEntityObj($entityModel, $id))) {
			return $entity;
		}
		
		return $this->createManagedEntityObj($entityModel, $id);
	}
	
	/**
	 * @param EntityModel $entityModel
	 * @param mixed $id
	 * @throws EntityCreationFailedException
	 * @return object
	 */
	public function createManagedEntityObj(EntityModel $entityModel, $id) {
		$entityObj = null;
		try {
			$entityObj = ReflectionUtils::createObject($entityModel->getClass(), true);
		} catch (ObjectCreationFailedException $e) {
			throw new EntityCreationFailedException('Could not create entity object for ' 
					. EntityInfo::buildEntityString($entityModel, $id), 0, $e);
		}
		$this->manageEntityObj($entityObj, $entityModel);
		$this->identifyManagedEntityObj($entityObj, $id);
		return $entityObj;
	}

	/**
	 * @param EntityModel $entityModel
	 * @param mixed $id
	 * @param EntityManager $em
	 * @throws EntityProxyInitializationException
	 * @throws LazyInitialisationException
	 * @return EntityProxy
	 */
	public function getOrCreateEntityProxy(EntityModel $entityModel, $id, EntityManager $em) {
		if ($id === null) return null;
	
		if (null !== ($entity = $this->getEntityById($entityModel, $id))) {
			return $entity;	
		}
	
		if ($entityModel->hasSubEntityModels()) {
			throw new EntityProxyInitializationException(
					'Entity which gets inherited by other entities can not be lazy initialized: '
							. EntityInfo::buildEntityString($entityModel, $id));
		}
	
		try {
			$entity = $this->entityProxyManager->createProxy($entityModel->getClass(), 
					new EntityProxyAccessListener($em, $entityModel, $id));
		} catch (CanNotCreateEntityProxyClassException $e) {
			throw new LazyInitialisationException('Cannot lazy initialize class: '
					. EntityInfo::buildEntityString($entityModel, $id), 0, $e);
		}
		
		$entityModel->getIdDef()->getEntityProperty()->writeValue($entity, $id);
		
		$this->manageEntityObj($entity, $entityModel);
		$this->identifyManagedEntityObj($entity, $id);
	
		return $entity;
	}
	
	/**
	 * @param object $entity
	 * @param EntityModel $entityModel
	 */
	public function manageEntityObj($entity, EntityModel $entityModel) {
		$objHash = spl_object_hash($entity);
		unset($this->removedEntityObjs[$objHash]);
		
		$this->managedEntityObjs[$objHash] = $entity;
		$this->entityModels[$objHash] = $entityModel;
	}
	
	/**
	 * @param object $entityObj
	 * @return bool
	 */
	public function containsManagedEntityObj($entityObj) {
		ArgUtils::assertTrue(is_object($entityObj));
		return isset($this->managedEntityObjs[spl_object_hash($entityObj)]);
	}
	
	/**
	 * @param object $entity
	 */
	public function removeEntityObj($entity) {
		$this->validateEntityObjManaged($entity);
		
		$objHash = spl_object_hash($entity);
		unset($this->managedEntityObjs[$objHash]);
		$this->removedEntityObjs[$objHash] = $entity;
	}
	
	/**
	 * @param object $entityObj
	 * @return bool
	 */
	public function containsRemovedEntityObj($entityObj) {
		ArgUtils::assertTrue(is_object($entityObj));
		return isset($this->removedEntityObjs[spl_object_hash($entityObj)]);
	}
	
	/**
	 * @param EntityModel $entityModel
	 * @param string $idRep
	 */
	private function removeEntityObjIdentifiction(EntityModel $entityModel, string $idRep) {
		do {
			unset($this->entityIdentifiers[$entityModel->getClass()->getName()][$idRep]);
		} while (null !== ($entityModel = $entityModel->getSuperEntityModel()));
	}
	
	/**
	 * @param object $entity
	 */
	public function detachEntityObj($entity) {
		$objHash = spl_object_hash($entity);
		
		if (isset($this->entityModels[$objHash])) {
			$entityModel = $this->entityModels[$objHash];
			unset($this->entityModels[$objHash]);
			
			if (isset($this->entityIdReps[$objHash])) {
				$this->removeEntityObjIdentifiction($entityModel, $this->entityIdReps[$objHash]);
				unset($this->entityIdReps[$objHash]);
			}
		}
		
		unset($this->entityIdReps[$objHash]);
		unset($this->managedEntityObjs[$objHash]);
		unset($this->entityValueHashCols[$objHash]);
		unset($this->removedEntityObjs[$objHash]);
// 		$this->detachedEntities[$objHash] = $entity;
	}
	                
	/**
	 * 
	 */
	public function detachNotManagedEntityObjs() {
		foreach ($this->removedEntityObjs as $entity) {
			$this->detachEntityObj($entity);
// 			unset($this->entityIdReps[$objHash]);
// 			unset($this->managedEntities[$objHash]);
// 			unset($this->entityValueHashes[$objHash]);
// 			unset($this->removedEntities[$objHash]);
		}
	}
	
	/**
	 * @param object $entity
	 * @throws \InvalidArgumentException
	 */
	private function validateEntityObjManaged($entityObj) {
		if ($this->containsManagedEntityObj($entityObj)) return;
		
		throw new \InvalidArgumentException('Passed entity not managed');
	}
	
	/**
	 * @param object $entity
	 * @param mixed $id
	 * @throws IllegalStateException
	 */
	public function identifyManagedEntityObj($entityObj, $id) {
		ArgUtils::assertTrue(is_object($entityObj));
		ArgUtils::assertTrue($id !== null);
		
		$objHash = spl_object_hash($entityObj);
		if (!isset($this->managedEntityObjs[$objHash])) {
			throw new IllegalStateException('Unable to identify non managed entity.');
		}
		
		$entityModel = $this->entityModels[$objHash];
		$idRep = $entityModel->getIdDef()->getEntityProperty()->valueToRep($id);
		
		if (isset($this->entityIdReps[$objHash]) && $this->entityIdReps[$objHash] !== $idRep) {
			throw new IllegalStateException('Entity already identified with other id: '
					. $this->entityIdReps[$objHash]);
		}
				
		$this->entityIdReps[$objHash] = $idRep;
		
		do {		
			$className = $entityModel->getClass()->getName();
			if (!isset($this->entityIdentifiers[$className])) {
				$this->entityIdentifiers[$className] = array();
			} else if (isset($this->entityIdentifiers[$className][$idRep])) {
				if ($this->entityIdentifiers[$className][$idRep] === $entityObj) return;
				
				throw new IllegalStateException('Other entity instance already exists in persistence context: ' 
						. EntityInfo::buildEntityString($entityModel, $id));
			}
			
			$this->entityIdentifiers[$className][$idRep] = $entityObj;
		} while (null !== ($entityModel = $entityModel->getSuperEntityModel()));
	}
	
	/**
	 * @param object $entity
	 * @throws \InvalidArgumentException
	 * @return EntityModel
	 */
	public function getEntityModelByEntityObj($entityObj) {
		$objHash = spl_object_hash($entityObj);
		if (isset($this->entityModels[$objHash])) {
			return $this->entityModels[$objHash];
		}
		
		throw new \InvalidArgumentException('Entity has status new');
	}	
	
	/**
	 * @param object $entity
	 * @param array $values
	 */
	public function mapValues($entityObj, array $values) {
		$this->validateEntityObjManaged($entityObj);
		
		$entityModel = $this->getEntityModelByEntityObj($entityObj);
		$this->entityProxyManager->disposeProxyAccessListenerOf($entityObj);
		
		foreach ($entityModel->getEntityProperties() as $propertyString => $entityProperty) {
			$propertyString = $entityProperty->toPropertyString();
			
			if (!array_key_exists($propertyString, $values)) continue;
			
			$entityProperty->writeValue($entityObj, $values[$propertyString]);
		}
	}
	
	/**
	 * @param object $entity
	 * @return bool
	 */
	public function containsValueHashes($entityObj) {
		$this->validateEntityObjManaged($entityObj);
		
		return isset($this->entityValueHashCols[spl_object_hash($entityObj)]);
	}
	
	/**
	 * @param object $entityObj
	 * @param ValueHashCol $valueHashCol See {@see ValueHashColFactory}
	 * @param EntityManager $em
	 */
	public function updateValueHashes($entityObj,  ValueHashCol $valueHashCol) {
		$this->validateEntityObjManaged($entityObj);
	
// 		$entityModel = $this->getEntityModelByEntityObj($entityObj);
		
// 		$hashFactory = new ValueHashColFactory($entityModel, $em);
// 		$hashFactory->setValues($values);
// 		$hashFactory->setValueHashes($valueHashes);
		
		$this->entityValueHashCols[spl_object_hash($entityObj)] = $valueHashCol;
	}
	
	/**
	 * @param object $entityObj
	 * @throws IllegalStateException
	 * @return ValueHashCol
	 */
	public function getValueHashColByEntityObj($entityObj) {
		$objectHash = spl_object_hash($entityObj);
		
		if (isset($this->entityValueHashCols[$objectHash])) {
			return $this->entityValueHashCols[$objectHash];
		}
		
		throw new IllegalStateException();
	}
}

class ValueHashColFactory {
	private $entityPropertyCollection;
	private $valueHashes = array();
	private $values = array();
	private $em;	
	
	/**
	 * @param EntityPropertyCollection $entityPropertyCollection
	 * @param EntityManager $em
	 */
	public function __construct(EntityPropertyCollection $entityPropertyCollection, EntityManager $em) {
		$this->entityPropertyCollection = $entityPropertyCollection;
		$this->em = $em;
	}
	
	/**
	 * @param ValueHash[] $valueHashes
	 */
	public function setValueHashes(array $valueHashes) {
		ArgUtils::valArray($valueHashes, ValueHash::class);
		$this->valueHashes = $valueHashes;
	}
	
	/**
	 * @return ValueHash[]
	 */
	public function getValueHashes() {
		return $this->valueHashes;
	}

	/**
	 * @param array $values
	 */
	public function setValues(array $values) {
		$this->values = $values;
	}
	
	/**
	 * @return array
	 */
	public function getValues() {
		return $this->values;
	}
	
	/**
	 * @param object $object
	 * @param array $values
	 * @return \n2n\persistence\orm\store\ValueHashCol
	 */
	public function create($object, &$values = array()) {
		$valueHashCol = new ValueHashCol();
		
		foreach ($this->entityPropertyCollection->getEntityProperties() as $entityProperty) {
			$propertyString = $entityProperty->toPropertyString();
			
			if (array_key_exists($propertyString, $this->valueHashes)) {
				$valueHashCol->putValueHash($propertyString, $this->valueHashes[$propertyString]);
				continue;
			}
			
			if (array_key_exists($propertyString, $this->values)) {
				$valueHashCol->putValueHash($propertyString, $entityProperty->createValueHash(
						$values[$propertyString] = $this->values[$propertyString], $this->em));
				continue;
			}
			
			$valueHashCol->putValueHash($propertyString, $entityProperty->createValueHash(
					$values[$propertyString] = $entityProperty->readValue($object), $this->em));
		}
		
		return $valueHashCol;
	}
	
	static function updateId(BasicEntityProperty $idEntityProperty, $idValue, ValueHashCol $valueHashCol, 
			EntityManager $em) {
		$valueHashCol->putValueHash($idEntityProperty->toPropertyString(), 
				$idEntityProperty->createValueHash($idValue, $em));
	}
}

class ValueHashCol {
	private $valueHashes = array();
	
	public function putValueHash($propertyString, ValueHash $valueHash) {
		$this->valueHashes[$propertyString] = $valueHash;	
	}
	
	public function getValueHashes() {
		return $this->valueHashes;
	}
	
	public function containsPropertyString($propertyString) {
		return isset($this->valueHashes[$propertyString]);
	}
	
	public function getValueHash(string $propertyString) {
		if (isset($this->valueHashes[$propertyString])) {
			return $this->valueHashes[$propertyString];
		}
		
		throw new \InvalidArgumentException('No ValueHash for property \'' . $propertyString . '\' available.');
	}
	
	public function getSize() {
		return count($this->valueHashes);
	}
	
	public function matches(ValueHashCol $otherValueHashCol) {
		if ($this->getSize() !== $otherValueHashCol->getSize()) {
			throw new \InvalidArgumentException('Number of ValueHashes are diffrent.');
		}
		
		$otherValueHashCol = $otherValueHashCol->getValueHashes();
		foreach ($this->valueHashes as $propertyString => $valueHash) {
			if (!isset($otherValueHashCol[$propertyString])) {
				throw new \InvalidArgumentException('No ValueHash for property \'' . $propertyString . '\' found.');
			}
			
			if (!$valueHash->matches($otherValueHashCol[$propertyString])) {
				return false;
			}
		}
		
		return true;
	}
}

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

use n2n\persistence\orm\model\EntityPropertyCollection;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use n2n\persistence\orm\proxy\EntityProxy;
use n2n\impl\persistence\orm\property\relation\selection\ArrayObjectProxy;

class OrmUtils {	
// 	public static function extractId($entity, EntityModel $entityModel = null) {
// 		if (is_null($entityModel)) {
// 			$entityModel = EntityModelManager::getInstance()->getEntityModelByObject($entity);
// 		}
		
// 		return $entityModel->getIdProperty()->getAccessProxy()->getValue($entity);
// 	}
// 	/**
// 	 * 
// 	 * @param EntityManager $em
// 	 * @param \ReflectionClass $class
// 	 * @param array $matches
// 	 * @return \n2n\persistence\orm\criteria\Criteria
// 	 */
// 	public static function createCountCriteria(EntityManager $em, \ReflectionClass $class, array $matches = null) {
// 		$criteria = $em->createSimpleCriteria($class, $matches);
// 		$criteria->select(CrIt::f(CriteriaFunction::COUNT, CrIt::c(1)));
// 		return $criteria;
// 	}
		
// 	public static function copy($entity, EntityModel $entityModel = null, $resetId = false) {
// 		if (null === $entityModel) {
// 			$entityModel = EntityModelManager::getInstance()->getEntityModelByObject($entity);
// 		}
		
// 		$copy = $entityModel->copy($entity);
// 		if ($resetId) {
// 			$entityModel->getIdProperty()->getAccessProxy()->setValue($copy, null);
// 		}
// 		return $copy;
// 	}
// 	/**
// 	 * @param EntityModel $entityModel
// 	 * @param EntityModel $entityModel2
// 	 * @return EntityModel
// 	 */
// 	public static function findLowestCommonEntityModel(EntityModel $entityModel, EntityModel $entityModel2) {
// 		$entityModels2 = $entityModel2->getAllSuperEntityModels(true);
		
// 		foreach ($entityModel->getAllSuperEntityModels(true) as $entityModel) {				
// 			foreach ($entityModels2 as $entityModel2) {
// 				if ($entityModel->equals($entityModel2)) {
// 					return $entityModel;
// 				}
// 			}
// 		}
// 	}
	
	public static function initializeProxy(EntityManager $em, $obj) {
		if ($obj instanceof EntityProxy) {
			$em->getPersistenceContext()->getEntityProxyManager()
					->initializeProxy($obj);
			return;
		}
		
		if ($obj instanceof ArrayObjectProxy) {
			$obj->initialize();
		}
	}
	
// 	public static function isUninitializedProxy($obj) {
// 		if ($obj instanceof EntityProxy) {
// 			return !EntityProxyManager::getInstance()->isProxyInitialized($obj);
// 		}
		
// 		if ($obj instanceof ArrayObjectProxy) {
// 			return !$obj->isInitialized();
// 		}
		
// 		return false;
// 	}
	
// 	public static function areObjectsEqual(Entity $obj = null, Entity $obj2 = null, EntityModel $entityModel = null) {
// 		if (!isset($obj) || !isset($obj2)) return false;
		
// 		if ($entityModel === null) {
// 			$entityModelManager = EntityModelManager::getInstance();
// 			$entityModel = $entityModelManager->getEntityModelByObject($obj);
// 			$entityModel2 = $entityModelManager->getEntityModelByObject($obj2);
// 		} else {
// 			$entityModel2 = $entityModel;
// 		}
		
// 		if (!$entityModel->equals($entityModel2)) return false; 
		
// 		$id = self::extractId($obj, $entityModel);
// 		$id2 = self::extractId($obj2, $entityModel2);
// 		if (isset($id)) {
// 			return $id === $id2;
// 		}
		
// 		return spl_object_hash($obj) == spl_object_hash($obj2);
// 	}

	public static function determineValue(EntityPropertyCollection $entityPropertyCollection, $value, CriteriaProperty $criteriaProperty) {
		$propertyNames = $criteriaProperty->getPropertyNames();
		
		$entityProperty = null;
		foreach ($criteriaProperty->getPropertyNames() as $propertyName) {
			$entityProperty = $entityPropertyCollection->getEntityPropertyByName($propertyName);
			
			if ($value === null) return null;
			
			if (!is_object($value)) {
				throw new \InvalidArgumentException();
			}
			
			$value = $entityProperty->readValue($value);
			
			if ($entityProperty->hasEmbeddedEntityPropertyCollection()) {
				$entityPropertyCollection = $entityProperty->getEmbeddedEntityPropertyCollection();
			} else if ($entityProperty->hasTargetEntityModel()) {
				$entityPropertyCollection = $entityProperty->getTargetEntityModel();
			}
		}
		return $value;
	}
	
	public static function determineEntityProperty(EntityPropertyCollection $entityPropertyCollection, CriteriaProperty $criteriaProperty) {
		$entityProperty = null;
		foreach ($criteriaProperty->getPropertyNames() as $propertyName) {
			$entityProperty = $entityPropertyCollection->getEntityPropertyByName($propertyName);
			if ($entityProperty->hasEmbeddedEntityPropertyCollection()) {
				$entityPropertyCollection = $entityProperty->getEmbeddedEntityPropertyCollection();
			} else if ($entityProperty->hasTargetEntityModel()) {
				$entityPropertyCollection = $entityProperty->getTargetEntityModel();
			}
		}
		return $entityProperty;
	}
}

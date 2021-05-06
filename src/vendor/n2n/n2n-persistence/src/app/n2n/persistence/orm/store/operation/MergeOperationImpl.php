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
namespace n2n\persistence\orm\store\operation;

use n2n\persistence\orm\store\action\ActionQueue;
use n2n\util\type\ArgUtils;
use n2n\persistence\orm\proxy\EntityProxy;
use n2n\reflection\ReflectionUtils;
use n2n\persistence\orm\model\EntityModel;
use n2n\util\type\ValueIncompatibleWithConstraintsException;
use n2n\persistence\orm\store\EntityInfo;
use n2n\persistence\orm\store\PersistenceOperationException;

class MergeOperationImpl implements MergeOperation {
	private $actionQueue;
	
	public function __construct(ActionQueue $actionQueue) {
		$this->actionQueue = $actionQueue;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\operation\MergeOperation::getEntityManager()
	 */
	public function getEntityManager() {
		return $this->actionQueue->getEntityManager();
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\operation\MergeOperation::mergeEntity()
	 */
	public function mergeEntity($entity) {
		ArgUtils::assertTrue(is_object($entity));
		
		$objHash = spl_object_hash($entity);
		if (isset($this->mergedEntity[$objHash])) {
			return $this->mergedEntity[$objHash];
		}
		
		$em = $this->actionQueue->getEntityManager();
		$persistenceContext = $em->getPersistenceContext();
		
		if ($entity instanceof EntityProxy 
				&& !$persistenceContext->getEntityProxyManager()->isProxyInitialized($entity)) {
			return $this->mergedEntity[$objHash] = $entity;
		}
		
		$entityInfo = $em->getPersistenceContext()->getEntityInfo(
				$entity, $em->getEntityModelManager());
		
		$this->mergedEntity[$objHash] = $mergedEntity = $this->createMergedEntity($entityInfo, $entity);
		
		$this->mergeProperties($entityInfo->getEntityModel(), $entity, $mergedEntity);
		
		return $mergedEntity;
	}	
	
	private function createMergedEntity(EntityInfo $entityInfo, $entity) {
		$entityModel = $entityInfo->getEntityModel();
		
		switch ($entityInfo->getState()) {
			case EntityInfo::STATE_MANAGED:
				return $entity;
				
			case EntityInfo::STATE_NEW:
			case EntityInfo::STATE_DETACHED:
				$newEntity = null;
				if ($entityInfo->hasId()) {
					$newEntity = $this->actionQueue->getEntityManager()->find(
							$entityInfo->getEntityModel()->getClass(), $entityInfo->getId());
					if ($newEntity !== null) return $newEntity;
				}
				
				$newEntity = ReflectionUtils::createObject($entityModel->getClass());
				$this->actionQueue->getOrCreatePersistAction($newEntity, true);
				return $newEntity;
				
			case EntityInfo::STATE_REMOVED:
			default:
				throw new PersistenceOperationException('Can not merge removed entity: '
						. $entityInfo->toEntityString());
		}
	}
	
	private function mergeProperties(EntityModel $entityModel, $entity, $mergedEntity) {
		$generatedIdProperty = null;
		
		if ($entityModel->getIdDef()->isGenerated()) {
			$generatedIdProperty = $entityModel->getIdDef()->getEntityProperty();
		}
		
		foreach ($entityModel->getEntityProperties() as $property) {
			if ($property === $generatedIdProperty) continue;
			
			$mergedValue = null;
			try {
				$mergedValue = $property->mergeValue($property->readValue($entity),  
						$entity === $mergedEntity, $this);
			} catch (PersistenceOperationException $e) {
				throw new PersistenceOperationException('Failed to merge property: ' 
						. $property->toPropertyString(), 0, $e);	
			}
			
			try {
				$property->writeValue($mergedEntity, $mergedValue);
			} catch (ValueIncompatibleWithConstraintsException $e) {
				throw new \InvalidArgumentException(get_class($property) 
						. '::mergeValue() returned invalid value.', 0, $e);
			}
		}
	}
}

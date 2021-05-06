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

use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\CascadeType;
use n2n\persistence\orm\store\SimpleLoader;
use n2n\persistence\orm\store\EntityInfo;
use n2n\persistence\orm\store\PersistenceOperationException;
use n2n\persistence\orm\EntityNotFoundException;

class RefreshOperation implements CascadeOperation {
	private $em;
	private $valueLoader;
	private $cascader;
	
	public function __construct(EntityManager $em) {
		$this->em = $em;
		$this->valueLoader = new SimpleLoader($em);
		$this->cascader = new OperationCascader(CascadeType::REFRESH, $this);
	}
	
	public function cascade($entity) {
		if (!$this->cascader->markAsCascaded($entity)) return;
	
		$persistenceContext = $this->em->getPersistenceContext();
		$entityInfo = $persistenceContext->getEntityInfo($entity, $this->em->getEntityModelManager());
		
		switch ($entityInfo->getState()) {
			case EntityInfo::STATE_DETACHED:
			case EntityInfo::STATE_REMOVED:
			case EntityInfo::STATE_NEW:
				throw new PersistenceOperationException('Can not refresh ' . $entityInfo->getState()
						. ' entity: ' . $entityInfo->toEntityString(), 0);
		}
		
		$this->em->getLoadingQueue()->registerLoading($this);
		
		$values = $this->valueLoader->loadValues($entityInfo->getEntityModel(), $entityInfo->getId());
		if ($values === null) {
			throw new EntityNotFoundException('Entity no longer exists in the database: ' 
					. $entityInfo->toEntityString());
		}
		
		$this->em->getLoadingQueue()->mapValues($entity, $entityInfo->getId(), $values);
		$this->em->getLoadingQueue()->finalizeLoading($this);
		
// 		$persistenceContext->mapValues($entity, $values);
// 		$persistenceContext->updateValueHashes($entity, $values, array(), $this->em);
// 		$this->em->getActionQueue()->announceLifecycleEvent(new LifecycleEvent(LifecycleEvent::POST_LOAD, $entity,
// 				$entityInfo->getEntityModel(), $entityInfo->getId()));
		
		$entityProperty = null;
		try {
			$this->cascader->cascadeProperties($entityInfo->getEntityModel(), $entity, $entityProperty);
		} catch (EntityNotFoundException $e) {
			throw new EntityNotFoundException('Could not refresh property '
					. $entityProperty->toPropertyString(), 0, $e);
		}
	}
		
}

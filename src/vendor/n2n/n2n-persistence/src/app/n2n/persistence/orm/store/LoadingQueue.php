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

use n2n\persistence\orm\store\action\ActionQueue;
use n2n\persistence\orm\LifecycleEvent;
use n2n\util\ex\IllegalStateException;

class LoadingQueue {
	private $persistenceContext;
	private $actionQueue;
	private $loadingContainerStack = array();
	private $valueHashJobs = array();
	private $postLoadEvents = array();
	
	public function __construct(PersistenceContext $persistenceContext, ActionQueue $actionQueue) {
		$this->persistenceContext = $persistenceContext;
		$this->actionQueue = $actionQueue;
	}
	
	public function mapValues($entity, $id, array $values) {
		$this->persistenceContext->mapValues($entity, $values);
		
		if (empty($this->loadingContainerStack)) {
			throw new IllegalStateException('No LoadingContainer available.');
		}

		$objHash = spl_object_hash($entity);
		
		$this->valueHashJobs[$objHash] = array('entityObj' => $entity, 'values' => $values);		
		
		$this->postLoadEvents[$objHash] = new LifecycleEvent(LifecycleEvent::POST_LOAD, $entity, 
				$this->persistenceContext->getEntityModelByEntityObj($entity), $id); 
	}
		
	public function registerLoading($loadingContainer) {
		$this->loadingContainerStack[spl_object_hash($loadingContainer)] = $loadingContainer;
	}
	
	public function finalizeLoading($loadingContainer) {
		$objHash = spl_object_hash($loadingContainer);
		
		if (!isset($this->loadingContainerStack[$objHash])) {
			throw new IllegalStateException('Unknown LoadingContainer');
		}
		
		unset($this->loadingContainerStack[$objHash]);
		
		if (!empty($this->loadingContainerStack)) return;
		
		$em = $this->actionQueue->getEntityManager();
		
		foreach ($this->valueHashJobs as $entityObjHash => $valueHashJob) {
			unset($this->valueHashJobs[$entityObjHash]);
			
			$entityObj = $valueHashJob['entityObj'];
			
			$hashFactory = new ValueHashColFactory($em->getPersistenceContext()->getEntityModelByEntityObj($entityObj), $em);
			$hashFactory->setValues($valueHashJob['values']);
			
			$this->persistenceContext->updateValueHashes($entityObj, $hashFactory->create($entityObj));
		}
			
		foreach ($this->postLoadEvents as $entityObjHash => $postLoadEvent) {
			unset($this->postLoadEvents[$entityObjHash]);
			$this->actionQueue->announceLifecycleEvent($postLoadEvent);
		}
	}
}

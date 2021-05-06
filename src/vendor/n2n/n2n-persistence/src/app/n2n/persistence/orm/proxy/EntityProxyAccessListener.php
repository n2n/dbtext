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

use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\store\SimpleLoader;
use n2n\persistence\orm\EntityNotFoundException;
use n2n\persistence\orm\store\EntityInfo;

class EntityProxyAccessListener {
	private $em;
	private $valueLoader;
	private $entityModel;
	private $id;
	private $disposed = false;

	public function __construct(EntityManager $em, EntityModel $entityModel, $id) {
		$this->em = $em;
		$this->valueLoader = new SimpleLoader($em);
		$this->entityModel = $entityModel;
		$this->id = $id;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getEntityModel() {
		return $this->entityModel;
	}

	public function onAccess($entity) {
		if ($this->disposed) return;
		$this->disposed = true;
		
		$this->em->getLoadingQueue()->registerLoading($this);
		
		$values = $this->valueLoader->loadValues($this->entityModel, $this->id);
		if ($values === null) {
			throw new EntityNotFoundException(
					'Could not lazy initialize entity. Following Entity no longer exists in the database: ' 
							. EntityInfo::buildEntityString($this->entityModel, $this->id));
		}
		
		$this->em->getLoadingQueue()->mapValues($entity, $this->id, $values);
		$this->em->getLoadingQueue()->finalizeLoading($this);
// 		$persistenceContext = $this->em->getPersistenceContext();
// 		$persistenceContext->mapValues($entity, $values);
// 		$persistenceContext->updateValueHashes($entity, $values, array(), $this->em);
	}

	public function dispose() {
		$this->disposed = true;
	}
}

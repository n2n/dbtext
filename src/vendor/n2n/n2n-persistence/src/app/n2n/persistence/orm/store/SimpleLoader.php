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

use n2n\persistence\orm\EntityManager;
use n2n\persistence\orm\query\select\EntityValuesSelection;
use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\orm\query\select\EntityObjSelection;
use n2n\persistence\meta\data\QueryPlaceMarker;

class SimpleLoader {
	private $em;
	
	public function __construct(EntityManager $em) {
		$this->em = $em;
	}
	
	public function loadEntity(EntityModel $entityModel, $id) {
		$utils = new SimpleLoaderUtils($this->em, $entityModel);
		$utils->initialize();
		$utils->setSelection(new EntityObjSelection($entityModel, $utils->queryState, $utils->metaTreePoint));
		
		$selectBuilder = $utils->build();
		$idProperty = $entityModel->getIdDef()->getEntityProperty();
		$selectBuilder->getWhereComparator()->match(
				$idProperty->createQueryColumn($utils->metaTreePoint->getMeta()),
				QueryComparator::OPERATOR_EQUAL,
				new QueryPlaceMarker($utils->queryState->registerPlaceholderValue(
						$idProperty->buildRaw($id, $this->em->getPdo()))));
		
		return $utils->createQuery()->fetchSingle();
	}
	
	public function loadValues(EntityModel $entityModel, $id) {
		$utils = new SimpleLoaderUtils($this->em, $entityModel);
		$utils->initialize();
		$utils->setSelection(new EntityValuesSelection($entityModel, $utils->queryState, $utils->metaTreePoint));

		$selectBuilder = $utils->build();
		$idProperty = $entityModel->getIdDef()->getEntityProperty();
		$selectBuilder->getWhereComparator()->match(
				$idProperty->createQueryColumn($utils->metaTreePoint->getMeta()),
				QueryComparator::OPERATOR_EQUAL, 
				new QueryPlaceMarker($utils->queryState->registerPlaceholderValue(
						$idProperty->buildRaw($id, $this->em->getPdo()))));
		
		return $utils->createQuery()->fetchSingle();
	}
}

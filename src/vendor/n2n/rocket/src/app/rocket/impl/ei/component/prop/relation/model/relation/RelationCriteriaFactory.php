<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\prop\relation\model\relation;

use rocket\ei\manage\frame\CriteriaFactory;
use n2n\persistence\orm\EntityManager;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use n2n\persistence\orm\criteria\item\CrIt;
	  
class RelationCriteriaFactory implements CriteriaFactory {
	const MOTS_ALIAS_SUFFIX = 'otms';

	private $relationProperty;
	private $entity;

	public function __construct(RelationEntityProperty $relationProperty, $entity) {
		$this->relationProperty = $relationProperty;
		$this->entity = $entity;
	}

	public function create(EntityManager $em, $entityAlias) {
		$mtmsAlias = $entityAlias . self::MOTS_ALIAS_SUFFIX;
		$criteria = $em->createCriteria();
		$criteria->from($this->relationProperty->getEntityModel()->getClass(), $mtmsAlias)
				->joinProperty(CrIt::p(array($mtmsAlias, $this->relationProperty->getName())), $entityAlias)
				->where(array($mtmsAlias => $this->entity));
// 		$criteria->setBaseEntityAlias($entityAlias);
		return $criteria;
	}
}

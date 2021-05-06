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

use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\criteria\item\CriteriaProperty;
use rocket\ei\manage\frame\CriteriaConstraint;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\EntityProperty;

class OneToManySelectCriteriaConstraint implements CriteriaConstraint {
	private $srcEntityObj;
	private $srcEntityProperty;
	
	/**
	 * @param object $srcEntityObj
	 * @param EntityProperty $srcEntityProperty
	 */
	function __construct(object $srcEntityObj, EntityProperty $srcEntityProperty) {
		$this->srcEntityObj = $srcEntityObj;
		$this->srcEntityProperty = $srcEntityProperty;
	}
	
	function applyToCriteria(Criteria $criteria, CriteriaProperty $alias) {
		$srcClass = $this->srcEntityProperty->getEntityModel()->getClass();
		$andGroup = $criteria->where()->andGroup();
		
		$srcAlias = $criteria->uniqueAlias();
		$subCriteria = $criteria->subCriteria();
		$subCriteria->select($srcAlias)
				->from($srcClass, $srcAlias)
				->where()
						->match($srcAlias, '=', $this->srcEntityObj)
						->andMatch(CrIt::p($srcAlias, $this->srcEntityProperty), 'CONTAINS', $alias);
		$andGroup->test('EXISTS', $subCriteria);
		
		$srcAlias = $criteria->uniqueAlias();
		$subCriteria = $criteria->subCriteria();
		$subCriteria->select($srcAlias)
				->from($srcClass, $srcAlias)
				->where()->match(CrIt::p($srcAlias, $this->srcEntityProperty), 'CONTAINS', $alias);
		$andGroup->orTest('NOT EXISTS', $subCriteria);
	}
}
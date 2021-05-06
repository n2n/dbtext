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
namespace n2n\persistence\orm\criteria;

// use n2n\persistence\orm\criteria\compare\OnCriteriaComparator;
// use n2n\persistence\meta\data\QueryComparator;

// class JoinedSubCriteria extends SubCriteria {
// 	private $onCriteriaComparator;
	
// 	public function __construct(QueryState $queryState, Criteria $parentCriteria, 
// 			QueryComparator $onQueryComparator) {
// 		parent::__construct($queryState);
// 		$this->parentCriteria = $parentCriteria;
// 		$this->onCriteriaComparator = new OnCriteriaComparator($queryState, $onQueryComparator, $this);
// 	}
	
// 	public function on() {
// 		return $this->onCriteriaComparator;
// 	}
// }

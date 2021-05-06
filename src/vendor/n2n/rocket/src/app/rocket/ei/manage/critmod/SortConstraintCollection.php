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
namespace rocket\ei;

use rocket\ei\component\prop\SortableEiProp;
use n2n\util\type\ArgUtils;
use n2n\util\col\HashMap;

class SortModificatorCollection {
	private $fields = array();
	private $directions = array();
	private $sortConstraints;
	
	public function addSortableEiProp(SortableEiProp $field, $direction) {
		$this->fields[] = $field;
		$this->directions[] = $direction;
		$this->sortConstraints = null;
	}
	
	public function getSortableEiProps() {
		return $this->fields;
	}
	
	public function removeSortableEiProps() {
		$this->fields = array();
		$this->directions = array();
		$this->sortConstraints = array();
	}
	
	public function getSortConstraints() {
		if ($this->sortConstraints === null) {
			$this->sortConstraints = array();
			foreach ($this->fields as $key => $field) {
				$sortConstraint = $field->createSortCriteriaConstraint($this->directions[$key]);
				ArgUtils::valTypeReturn($sortConstraint, 'rocket\ei\manage\frame\CriteriaConstraint',
						$field, 'createSortCriteriaConstraint');
				$this->sortConstraints[] = $sortConstraint;
			}
		}
		
		return $this->sortConstraints;
	}
	
	public function toFieldMap() {
		$hashMap = new HashMap();
		foreach ($this->fields as $key => $field) {
			$hashMap[$field] = $this->directions[$key]; 
		}
		return $hashMap;
	}
}

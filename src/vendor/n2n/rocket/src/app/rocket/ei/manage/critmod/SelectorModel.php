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
// namespace rocket\ei\manage\critmod;

// use rocket\ei\util\filter\prop\SelectorItem;
// use rocket\ei\manage\critmod\filter\data\FilterData;
// use rocket\ei\manage\critmod\filter\data\FilterDataElement;
// use rocket\ei\manage\critmod\filter\data\FilterDataUsage;
// use rocket\ei\manage\critmod\filter\data\FilterDataGroup;
// use n2n\util\type\ArgUtils;
// use n2n\reflection\IllegalArgumentException;
// use n2n\l10n\Message;

// class SelectorModel {
// 	private $selectorItems = array();
	
// 	public function putSelectorItem($id, SelectorItem $filterItem) {
// 		$this->selectorItems[$id] = $filterItem;
// 	}
	
// 	public function getSelectorItems() {
// 		return $this->selectorItems;
// 	}
	
// 	public static function createFromSelectorItems(array $selectorItems) {
// 		$selectorModel = new SelectorModel();
// 		$selectorModel->setSelectorItems($selectorItems);
// 		return $selectorModel;
// 	}
	
// 	public function setSelectorItems(array $selectorItems) {
// 		ArgUtils::valArray($selectorItems, 'rocket\ei\util\filter\prop\SelectorItem');
// 		$this->selectorItems = $selectorItems;
// 	}
	
// 	public function createSelector(FilterData $filterData) {
// 		$selector = new Selector();
// 		foreach ($filterData->getElements() as $element) {
// 			$this->applySelectorConstraint($selector, $element);
// 		}
		
// 		if ($selector->isEmpty()) return null;
// 		return $selector;
// 	}

// 	private function applySelectorConstraint(Selector $selector, FilterDataElement $element) {
// 		if ($element instanceof FilterDataUsage) {
// 			$itemId = $element->getItemId();
// 			if (isset($this->selectorItems[$itemId])) {
// 				$selectorConstraint = $this->selectorItems[$itemId]->createSelectorConstraint($element->getDataSet());
// 				ArgUtils::valTypeReturn($selectorConstraint,
// 						'rocket\ei\manage\critmod\SelectorConstraint',
// 						$this->selectorItems[$itemId], 'createSelectorConstraint');
// 				$selector->addSelectorConstraint($itemId, $selectorConstraint);
// 				return $selectorConstraint;
// 			}
// 		} else if ($element instanceof FilterDataGroup) {
// 			$groupSelector = new Selector();
// 			$groupSelector->setUseAnd($element->isAndUsed());
// 			foreach ($element->getAll() as $childElement) {
// 				$this->applySelectorConstraint($groupSelector, $childElement);
// 			}
			
// 			$selector->addGroupSelector($groupSelector);
// 		}
		
// 		return null;
// 	}
// }

// class Selector {
// 	private $useAnd = true;
// 	private $selectorConstraintGroups = array();
// 	private $groupSelectors = array();
	
// 	public function setUseAnd($useAnd) {
// 		$this->useAnd = $useAnd;
// 	}
	
// 	public function addSelectorConstraint($id, SelectorConstraint $selectorConstraint) {
// 		if (!isset($this->selectorConstraintGroups[$id])) {
// 			$this->selectorConstraintGroups[$id] = array();
// 		}
// 		$this->selectorConstraintGroups[$id][] = $selectorConstraint;
// 	}
	
// 	public function addGroupSelector(Selector $groupSelector) {
// 		$this->groupSelectors[] = $groupSelector;
// 	}
	
// 	public function isEmpty() {
// 		return empty($this->selectorConstraintGroups) && empty($this->groupSelectors);
// 	}
	
// 	public function validateValues(\ArrayAccess $values, SelectorValidationResult $validationResult) {
// 		if ($this->useAnd) {
// 			return $this->andValidateValues($values, $validationResult);
// 		} else {
// 			return $this->orValidateValues($values, $validationResult);
// 		}
// 	}
	
// 	private function andValidateValues(\ArrayAccess $values, SelectorValidationResult $validationResult) {
// 		$matches = true;
// 		foreach ($this->selectorConstraintGroups as $id => $selectorConstraintGroup) {
// 			foreach ($selectorConstraintGroup as $selectorConstraint) {
// 				if (!$values->offsetExists($id)) {
// 					throw new IllegalArgumentException('No value for id ' . $id);
// 				}
		
// 				$errorMessage = $selectorConstraint->validate($values[$id]);
// 				if (null !== $errorMessage) {
// 					$validationResult->addError($id, $errorMessage);
// 					$matches = false;
// 				}
// 			}
// 		}
		
// 		foreach ($this->groupSelectors as $groupSelector) {
// 			if (!$groupSelector->validateValues($values, $validationResult)) {
// 				$matches = false;
// 			}
// 		}
		
// 		return $matches;
// 	}
	
// 	private function orValidateValues(\ArrayAccess $values, SelectorValidationResult $validationResult) {
// 		$matches = true;
// 		$selectorValidationResult = new SelectorValidationResult();
// 		foreach ($this->selectorConstraintGroups as $id => $selectorConstraintGroup) {
// 			foreach ($selectorConstraintGroup as $selectorConstraint) {
// 				if (!$values->offsetExists($id)) {
// 					throw new IllegalArgumentException('No value for id ' . $id);
// 				}
		
// 				$errorMessage = $selectorConstraint->validate($values[$id]);
// 				if (null === $errorMessage) return true;
				
// 				$selectorValidationResult->addError($id, $errorMessage);
// 				$matches = false;
// 			}
// 		}
		
// 		foreach ($this->groupSelectors as $groupSelector) {
// 			if ($groupSelector->validateValues($values, $validationResult)) {
// 				return true;
// 			}
// 			$matches = false;
// 		}
		
// 		$validationResult->addError(null, new OrSelectorMessage($selectorValidationResult->getMessages()));
		
// 		return $matches;
// 	}
	
// 	public function acceptsValues(\ArrayAccess $values) {
// 		if ($this->useAnd) {
// 			return $this->andAcceptsValues($values);
// 		} else {
// 			return $this->orAcceptValues($values);
// 		}
// 	}
	
// 	private function andAcceptsValues(\ArrayAccess $values) {
// 		foreach ($this->selectorConstraintGroups as $id => $selectorConstraintGroup) {
// 			foreach ($selectorConstraintGroup as $selectorConstraint) {
// 				if (!$values->offsetExists($id)) {
// 					throw new IllegalArgumentException('No value for id ' . $id);
// 				}
				
// 				if (!$selectorConstraint->matches($values[$id])) return false;
// 			}
// 		}

// 		foreach ($this->groupSelectors as $groupSelector) {
// 			if (!$groupSelector->acceptsValues($values)) return false;
// 		}
		
// 		return true;
		
// 	}
	
// 	private function orAcceptValues(\ArrayAccess $values) {
// 		if ($this->isEmpty()) return true;
		
// 		foreach ($this->selectorConstraintGroups as $id => $selectorConstraintGroup) {
// 			foreach ($selectorConstraintGroup as $selectorConstraint) {
// 				if (!$values->offsetExists($id)) {
// 					throw new IllegalArgumentException('No value for id: ' . $id);
// 				}
		
// 				if ($selectorConstraint->matches($values[$id])) return true;
// 			}
// 		}
		
// 		foreach ($this->groupSelectors as $groupSelector) {
// 			if ($groupSelector->acceptsValues($values)) return true;
// 		}
		
// 		return false;
// 	}
	
// 	public function acceptsValue($id, $value) {
// 		if ($this->useAnd) {
// 			return $this->andAcceptsValue($id, $value);
// 		} else {
// 			return $this->orAcceptsValue($id, $value);
// 		}
// 	}
	
// 	private function andAcceptsValue($id, $value) {
// 		if (isset($this->selectorConstraintGroups[$id])) {
// 			foreach ($this->selectorConstraintGroups[$id] as $selectorConstraint) {
// 				if (!$selectorConstraint->matches($value)) return false;
// 			}
// 		}
		
// 		foreach ($this->groupSelectors as $groupSelector) {
// 			if (!$groupSelector->acceptsValue($id, $value)) return false;
// 		}
		
// 		return true;
// 	}
	
// 	private function orAcceptsValue($id, $value) {
// 		if ($this->isEmpty()) return true;
		
// 		foreach ($this->groupSelectors as $groupSelector) {
// 			if ($groupSelector->acceptsValue($id, $value)) return true;
// 		}
		
// 		if (isset($this->selectorConstraintGroups[$id])) {
// 			foreach ($this->selectorConstraintGroups[$id] as $selectorConstraint) {
// 				if ($selectorConstraint->matches($value)) return true;
// 			}
			
// 			if (1 == sizeof($this->selectorConstraintGroups)) {
// 				return false;
// 			}
// 		}
		
// 		return true;
// 	}
// }

// class OrSelectorMessage extends Message {
// 	public function __construct() {
// 		parent::__construct('No access to values');
// 	}
// }

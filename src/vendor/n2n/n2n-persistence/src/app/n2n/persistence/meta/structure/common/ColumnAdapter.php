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
namespace n2n\persistence\meta\structure\common;

use n2n\persistence\meta\structure\Table;

use n2n\persistence\meta\structure\Column;

abstract class ColumnAdapter implements Column, CommonColumn {
	private $name;
	
	/**
	 * @var Table
	 */
	private $table;
	private $nullAllowed;
	private $defaultValue;
	private $defaultValueAvailable;
	private $valueGenerated;
	private $attrs;
	private $indexes;
	
	/**
	 * @var array
	 */
	private $changeListeners;
	
	public function __construct($name) {
		$this->name = $name;
		$this->changeListeners = array();
		$this->attrs = array();
		$this->indexes = array();
		$this->nullAllowed = true;
		$this->defaultValueAvailable = false;
		$this->valueGenerated = false;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->triggerChangeListeners();
		$this->name = $name;	
	}
	
	/**
	 * @return Table
	 */
	public function getTable() {
		return $this->table;
	}
	
	public function setTable(Table $table) {

		$this->table = $table;
	}
	
	public function isNullAllowed() {
		return $this->nullAllowed;
	}
	
	public function setNullAllowed($nullAllowed) {
		$this->triggerChangeListeners();
		$this->nullAllowed = (bool) $nullAllowed;
	}
	
	public function getDefaultValue() {
		return $this->defaultValue;
	}
	
	public function setDefaultValue($defaultValue) {
		$this->triggerChangeListeners();
		$this->defaultValueAvailable = true;
		$this->defaultValue = $defaultValue;
	}
	
	public function isDefaultValueAvailable() {
		return $this->defaultValueAvailable;
	}
	
	public function setDefaultValueAvailable($defaultValueAvailable) {
		$this->triggerChangeListeners();
		$this->defaultValueAvailable = (bool) $defaultValueAvailable;
	}
	
	public function isValueGenerated() {
		return $this->valueGenerated;
	}
	
	public function setValueGenerated($valueGenerated) {
		$this->triggerChangeListeners();
		$this->valueGenerated = (bool) $valueGenerated;
	}
	
	public function getAttrs() {
		return $this->attrs;
	}
	
	public function setAttrs(array $attrs) {
		$this->triggerChangeListeners();
		$this->attrs = $attrs;
	}
	
	public function getIndexes() {
		$indexes = array();
		if (null !== ($table = $this->getTable())) {
			$tableIndexes = $table->getIndexes();
			foreach ($tableIndexes as $index) {
				if ($index->containsColumnName($this->getName())) {
					$indexes[$index->getName()] = $index;
				}
			}
		}
		return $indexes;
	}
	
	public function equals(Column $column) {
		return ($column->getName() === $this->getName())
				&& $this->equalsType($column);
	}
		
	public function equalsType(Column $column, $ignoreNull = false) {
		//check the type
		if (get_class($column) !== get_class($this)
				|| $column->isValueGenerated() !== $this->isValueGenerated()) return false;
		if (!$ignoreNull && (($column->isNullAllowed() !== $this->isNullAllowed())
				|| ($column->getDefaultValue() !== $this->getDefaultValue()))) {
			return false;
		}
		return true;
	}
	
	public function registerChangeListener(ColumnChangeListener $changeListener) {
		$this->changeListeners[spl_object_hash($changeListener)] = $changeListener;
	}
	
	public function unregisterChangeListener(ColumnChangeListener $changeListener) {
		unset($this->changeListeners[spl_object_hash($changeListener)]);
	}
	
	protected function triggerChangeListeners() {
		foreach($this->changeListeners as $changeListener) {
			$changeListener->onColumnChange($this);
		}
	}

	protected function applyCommonAttributes(Column $fromColumn) {
		$this->setDefaultValue($fromColumn->getDefaultValue());
		$this->setDefaultValueAvailable($fromColumn->isDefaultValueAvailable());
		$this->setNullAllowed($fromColumn->isNullAllowed());
		$this->setAttrs($fromColumn->getAttrs());
		$this->setValueGenerated($fromColumn->isValueGenerated());
	}
}

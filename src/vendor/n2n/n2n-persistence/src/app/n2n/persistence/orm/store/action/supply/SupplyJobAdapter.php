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
namespace n2n\persistence\orm\store\action\supply;

use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\store\action\EntityAction;
use n2n\persistence\orm\store\ValueHashCol;

abstract class SupplyJobAdapter implements SupplyJob {
	protected $entityAction;
	protected $onResetClosures = array();
	protected $whenInitializedClosures = array();
	protected $oldValueHashCol;
	protected $values;
	protected $init = false;
	
	public function __construct(EntityAction $entityAction, ValueHashCol $oldValueHashCol = null){
		$this->entityAction = $entityAction;
		$this->oldValueHashCol = $oldValueHashCol;
		
		$that = $this;
		$entityAction->executeOnDisable(function () use ($that) {
			$that->reset();
		});
	}
	
	public function getActionQueue() {
		return $this->entityAction->getActionQueue();	
	}
	
	public function isInsert() {
		return $this->oldValueHashCol === null;
	}
	
	public function executeOnReset(\Closure $closure) {
		IllegalStateException::assertTrue(!$this->init);
		
		$this->onResetClosures[] = $closure;
	}
	
	public function executeWhenInitialized(\Closure $closure) {
		IllegalStateException::assertTrue(!$this->init);
		
		$this->whenInitializedClosures[] = $closure;
	}
	
	public function isDisabled() {
		return $this->entityAction->isDisabled();
	}
	
	public function init() {
		IllegalStateException::assertTrue(!$this->init);
		
		$this->init = true;
		while (null !== ($closure = array_shift($this->whenInitializedClosures))) {
			$closure();
		}
	}

// 	public function prepare() {
// 		$this->reset();
// 	}
	
	protected function reset() {
		IllegalStateException::assertTrue(!$this->init);
		
		$this->whenInitializedClosures = array();
		while (null !== ($closure = array_shift($this->onResetClosures))) {
			$closure();
		}
		$this->init = false;
	}
	
	public function getOldValueHashCol() {
		return $this->oldValueHashCol;
	}
	
	public function setValues(array $values) {
		$this->values = $values;
	}

	public function getValues() {
		return $this->values;
	}
	
	protected function getOldValueHash($propertyString) {
		IllegalStateException::assertTrue($this->oldValueHashCol !== null 
				&& $this->oldValueHashCol->containsPropertyString($propertyString));
		return $this->oldValueHashCol->getValueHash($propertyString);
	}

	protected function getValue($propertyString) {
		IllegalStateException::assertTrue(array_key_exists($propertyString, $this->values));
		return $this->values[$propertyString];
	}
}

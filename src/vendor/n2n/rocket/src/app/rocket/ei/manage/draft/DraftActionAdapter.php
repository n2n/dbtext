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
namespace rocket\ei\manage\draft;

use n2n\util\ex\IllegalStateException;

abstract class DraftActionAdapter implements DraftAction {
	
	protected function ensureNotExecuted() {
		if (!$this->executed) return;
		
		throw new IllegalStateException('DraftAction already executed.');
	}
	
	protected $executed = false;
	protected $onDisableClosures = array();
	protected $atStartClosures = array();
	protected $atEndClosures = array();
	protected $dependents = array();
	

// 	protected $disabled = false;
	
// 	public function disable() {
// 		$this->ensureNotExecuted();
// 		$this->triggerOnDisableClosures();
		
// 		$this->disabled = true;
// 	}
	
// 	public function executeOnDisable(\Closure $closure) {
// 		$this->onDisableClosures[] = $closure;
// 	}
	
	public function executeOnStart(\Closure $closure) {
		$this->atStartClosures[] = $closure;
	}
	
	public function executeAtStart(\Closure $closure) {
		$this->atStartClosures[] = $closure;
	}
	
	public function executeAtEnd(\Closure $closure) {
		$this->atEndClosures[] = $closure;
	}
	/**
	 * @param DraftAction $draftAction
	 */
	public function addDependent(DraftAction $draftAction) {
		$this->dependents[] = $draftAction;
	}
	
	public function getDependents() {
		return $this->dependents;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\draft\DraftAction::setDependents()
	 */
	public function setDependents(array $actionJobs) {
		$this->dependents = $actionJobs;
	}
	
	protected function executeDependents() {
		foreach ($this->dependents as $actionJob) {
			$actionJob->execute();
		}
	}
	
	protected function triggerOnDisableClosures() {
		while (null !== ($closure = array_shift($this->onDisableClosures))) {
			$closure($this);
		}
	}
	
	protected function triggerAtStartClosures() {
		while (null !== ($closure = array_shift($this->atStartClosures))) {
			$closure($this);
		}
	}
	
	protected function triggerAtEndClosures() {
		while (null !== ($closure = array_shift($this->atEndClosures))) {
			$closure($this);
		}
	}
	
	public function isExecuted() {
		return $this->executed;
	}
	
	private $executingDependends = false;
	
	public function execute() {
		if ($this->executingDependends) {
			throw new DraftingException('Draft dependend conflict.');
		}
	
// 		if ($this->disabled) {
// 			throw new DraftingException('DraftAction disabled.');
// 		}
		
		if ($this->executed) return;
		$this->executed = true;
	
		$this->executingDependends = true;
		$this->executeDependents();
		$this->executingDependends = false;
	
		$this->triggerAtStartClosures();
	
		$this->exec();
	
		$this->triggerAtEndClosures();
	}

	protected abstract function exec();
}

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
namespace n2n\persistence\orm\store\action;

use n2n\persistence\orm\OrmException;

abstract class ActionAdapter implements Action {
	protected $executed = false;
	protected $atStartClosures = array();
	protected $atEndClosures = array();
	protected $dependents = array();
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\Action::executeAtStart()
	 */
	public function executeAtStart(\Closure $closure) {
		$this->atStartClosures[] = $closure;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\Action::executeAtEnd()
	 */
	public function executeAtEnd(\Closure $closure) {
		$this->atEndClosures[] = $closure;
	}
	/**
	 * @param Action $actionJob
	 * @return Action[]
	 */
	public function addDependent(Action $actionJob) {
		$this->dependents[] = $actionJob;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\Action::getDependents()
	 */
	public function getDependents() {
		return $this->dependents;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\orm\store\Action::setDependents()
	 */
	public function setDependents(array $actionJobs) {
		$this->dependents = $actionJobs;
	}
	
	protected function executeDependents() {
		foreach ($this->dependents as $actionJob) {
			$actionJob->execute();
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
			throw new OrmException('Multiple relations conflicts. This exception will be improved soon. :-)');
		}
		
		if ($this->executed) return;
		$this->executed = true;
		
		$this->executingDependends = true;
		$this->executeDependents();
		$this->executingDependends = false;
		
		$this->triggerAtStartClosures();
		
		$this->exec();	
		
		$this->triggerAtEndClosures();
	}
	
// 	protected abstract function prepareExec();
	
	protected abstract function exec();
}

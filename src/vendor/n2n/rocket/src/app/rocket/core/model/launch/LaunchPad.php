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
namespace rocket\core\model\launch;

use n2n\core\container\N2nContext;
use n2n\web\http\controller\Controller;
use n2n\web\http\controller\ControllerContext;

interface LaunchPad {
	
	public function getId(): string;
	
	public function getLabel(): string;
	
	public function isAccessible(N2nContext $n2nContext): bool;
	
	public function determinePathExt(N2nContext $n2nContext);
	
	public function lookupController(N2nContext $n2nContext, ControllerContext $delegateControllerContext): Controller;
	
	public function approveTransaction(N2nContext $n2nContext): TransactionApproveAttempt; 
}

class TransactionApproveAttempt {
	private $reasonMessages;
	
	public function __construct(array $reasonMessages) {
		$this->reasonMessages = $reasonMessages;
	}
	/**
	 * @return boolean
	 */
	public function isSuccessful() {
		return empty($this->reasonMessages);
	}
	
	public function getReasonMessages() {
		return $this->reasonMessages;
	}
}

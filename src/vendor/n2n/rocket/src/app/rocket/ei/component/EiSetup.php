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
namespace rocket\ei\component;

use n2n\core\container\N2nContext;
use rocket\ei\util\Eiu;

class EiSetup {
	private $n2nContext;
	private $eiComponent;
	private $eiu;
	
	public function __construct(N2nContext $n2nContext, EiComponent $eiComponent) {
		$this->n2nContext = $n2nContext;
		$this->eiComponent = $eiComponent;
	}
	
	/**
	 * @return \rocket\ei\util\Eiu
	 */
	public function eiu() {
		if ($this->eiu === null) {
			$this->eiu = new Eiu($this->n2nContext, $this->eiComponent);
		}
		return $this->eiu;
	}
	
	/**
	 * @return \n2n\core\container\N2nContext
	 */
	public function getN2nContext() {
		return $this->n2nContext;
	}
	
	/**
	 * @param string $reason
	 * @param \Exception $previous
	 * @return \rocket\ei\component\InvalidEiComponentConfigurationException
	 */
	public function createException(string $reason = null, \Exception $previous = null) {
		$message = $this->eiComponent . ' invalid configured.';
		
		return new InvalidEiComponentConfigurationException($message
				. ($reason !== null ? ' Reason: ' . $reason : ''), 0, $previous);
	}
	
}
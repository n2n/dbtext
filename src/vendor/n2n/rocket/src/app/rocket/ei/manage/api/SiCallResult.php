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
namespace rocket\ei\manage\api;

use rocket\si\input\SiInputError;
use rocket\si\control\SiCallResponse;

class SiCallResult implements \JsonSerializable {
	private $inputError;
	private $callResponse;
	
	/**
	 * @param SiInputError $inputError
	 * @param SiCallResponse $callResponse
	 */
	private function __construct(?SiInputError $inputError, ?SiCallResponse $callResponse) {
		$this->inputError = $inputError;
		$this->callResponse = $callResponse;
	}
	
	/**
	 * @param SiInputError $inputError
	 * @return \rocket\ei\manage\api\SiCallResult
	 */
	static function fromInputError(?SiInputError $inputError) {
		return new SiCallResult($inputError, null);
	}
	
	/**
	 * @param SiCallResponse $callResponse
	 * @return \rocket\ei\manage\api\SiCallResult
	 */
	static function fromCallResponse(?SiCallResponse $callResponse) {
		return new SiCallResult(null, $callResponse);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'inputError' => $this->inputError,
			'callResponse' => $this->callResponse
		];
	}
}

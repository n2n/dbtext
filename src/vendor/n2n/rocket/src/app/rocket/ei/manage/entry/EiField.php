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
namespace rocket\ei\manage\entry;

use rocket\ei\manage\EiObject;
use rocket\ei\util\Eiu;

interface EiField {
	
	
	/**
	 * @return mixed 
	 */
	public function getValue();
	
	/**
	 * @param mixed $value
	 * @throws \InvalidArgumentException
	 */
	public function setValue($value);
	
	function hasChanges(): bool;
	
	/**
	 * @param mixed $value
	 * @return bool
	 */
	public function acceptsValue($value): bool;
	
	/**
	 * @return bool
	 */
	public function isValid(): bool;
	
	/**
	 * @param EiFieldValidationResult $eiEiFieldValidationResult
	 */
	public function validate(EiFieldValidationResult $eiEiFieldValidationResult);
	
	function read();
	
	/**
	 * Security can be ignored
	 * @return boolean
	 */
	public function isWritable(): bool;
	
	
	/**
	 * 
	 */
	public function write();	
	
	/**
	 * @return bool
	 */
	public function isCopyable(): bool;
	
	/**
	 * Security can be ignored
	 * @param EiObject $eiObject
	 * @return mixed
	 */
	public function copyValue(Eiu $copyEiu);
	
	/**
	 * @return bool
	 */
	public function hasForkedEiFieldMap(): bool;
	
	/**
	 * @return EiFieldMap
	 */
	public function getForkedEiFieldMap(): EiFieldMap;
}
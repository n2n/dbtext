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
namespace n2n\util\crypt\hash\algorithm;

class BlowfishAlgorithm implements HashAlgorithm {
	
	private $cost;
	
	public function __construct($cost = 7) {
		$this->cost = $cost;
	}
	
	public function getEffectiveSaltSize() {
		//It is just possible to compare the Blowfish hash with its raw-Password with the same Result
		//if the Salt is smaller than 20 followed by an $ (the doc says 21)
		return 22;
	}
	
	public function generateSaltPatternForSalt($salt) {
		return '$2y$' . sprintf('%02d', $this->cost) . '$' . $salt . '$';
	}
}

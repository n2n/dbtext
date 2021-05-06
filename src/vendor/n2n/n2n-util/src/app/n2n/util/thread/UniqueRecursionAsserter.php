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
namespace n2n\util\thread;

use n2n\util\StringUtils;

class UniqueRecursionAsserter {
	
	private $refs = [];
	
	/**
	 * @param mixed $ref
	 * @return boolean
	 */
	function contains($ref) {
		return in_array($ref, $this->refs, true);
	}
	
	/**
	 * @param mixed $ref
	 * @return boolean
	 */
	function tryPush($ref) {
		if ($this->contains($ref)) {
			return false;
		}
		
		$this->refs[] = $ref;
		return true;
	}
	
	/**
	 * @param mixed $ref
	 * @throws RecursionAssertionFailedException
	 */
	function push($ref) {
		if ($this->tryPush($ref)) {
			return;
		}
		
		throw new RecursionAssertionFailedException('Stack already contains ref: ' 
				. StringUtils::strOf($ref, true));
	}
	
	function tryPop($ref) {
		if (empty($this->refs) || end($this->refs) !== $ref) {
			return false;
		}
		
		array_pop($this->refs);
		return true;
	}
	
	function pop($ref) {
		if ($this->tryPop($ref)) {
			return;
		}
		
		if (empty($this->refs)) {
			throw new RecursionAssertionFailedException('Stack is empty.');
		}
		
		throw new RecursionAssertionFailedException('Passed ref: ' . StringUtils::strOf($ref, true) . ' !== Last stack ref: ' 
				. StringUtils::strOf(end($this->refs), true));
	}
}
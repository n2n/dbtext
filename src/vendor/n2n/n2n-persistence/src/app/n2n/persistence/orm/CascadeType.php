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
namespace n2n\persistence\orm;

class CascadeType {
  	const ALL = 31;
  	const PERSIST = 1;
  	const MERGE = 2;
  	const REMOVE = 4;
  	const REFRESH = 8;
  	const DETACH = 16;
  	const NONE = 0;
  	
  	public static function buildString($cascadeType) {
  		if ($cascadeType & self::ALL) {
  			return 'all';
  		}
  		
  		$cascadeStrings = array();

  		if ($cascadeType & self::PERSIST) {
  			$cascadeStrings[] = 'persist';
  		}
  		
  		if ($cascadeType & self::MERGE) {
  			$cascadeStrings[] = 'merge';
  		}
  		
  		if ($cascadeType & self::REMOVE) {
  			$cascadeStrings[] = 'remove';
  		}
  		
  		if ($cascadeType & self::REFRESH) {
  			$cascadeStrings[] = 'refresh';
  		}
  		
  		if ($cascadeType & self::DETACH) {
  			$cascadeStrings[] = 'detach';
  		}
  		
  		if (empty($cascadeStrings)) {
  			return 'none';
  		}
  		
  		return implode(', ', $cascadeStrings);
  	}
}

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
namespace rocket\ei;

use rocket\ei\component\prop\EiProp;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use rocket\ei\util\spec\EiuProp;

class EiPropPath extends IdPath {

	/**
	 * @param string $id
	 * @return \rocket\ei\EiPropPath
	 */
	public function pushed($id) {
		$ids = $this->ids;
		$ids[] = $id;
		return new EiPropPath($ids);
	}	
	
	/**
	 * @return EiPropPath
	 */
	public function poped() {
		$ids = $this->ids;
		array_pop($ids);
		return new EiPropPath($ids);
	}
	
	/**
	 * @param EiProp $eiProp
	 * @return \rocket\ei\EiPropPath
	 */
	public static function from(EiProp $eiProp) {
		return $eiProp->getWrapper()->getEiPropPath();
	}
	
	/**
	 * @param mixed $expression
	 * @return \rocket\ei\EiPropPath
	 */
	public static function create($expression) {
		if ($expression instanceof EiPropPath) {
			return $expression;
		}
		
		if ($expression instanceof EiProp) {
			return self::from($expression);
		}
		
		if ($expression instanceof EiuProp) {
			return self::from($expression->getEiProp());
		}
	
		if (is_array($expression)) {
			return new EiPropPath($expression);
		}
	
		if (is_scalar($expression)) {
			return new EiPropPath(explode(self::ID_SEPARATOR, $expression));
		}
		
		ArgUtils::valType($expression, ['string', EiPropPath::class, EiProp::class, EiuProp::class]);
		throw new IllegalStateException();
	}
	
	/**
	 * @param mixed|null $expression
	 * @return NULL|\rocket\ei\EiPropPath
	 */
	public static function build($expression) {
		if ($expression === null) {
			return null;
		}
		
		return self::create($expression);
	}
	
	/**
	 * @param mixed ...$args
	 * @return \rocket\ei\EiPropPath
	 */
	public function ext(...$args) {
		return new EiPropPath(array_merge($this->ids, $this->argsToIds($args)));
	}
	
// 	public function startsWith(EiPropPath $eiPropPath) {
// 		$size = $this->size();
		
// 		if ($eiPropPath->size() > $size) {
// 			return false;
// 		}
		
// 		foreach ($eiPropPath->ids as $key => $id) {
// 			if (!isset($this->ids[$key]) || $id !== $this->ids[$key]) {
// 				return false;
// 			}
// 		}
		
// 		return true;
// 	}
}

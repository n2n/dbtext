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
namespace rocket\ei\manage\gui\control;

use rocket\ei\IdPath;
use rocket\ei\EiCommandPath;

class GuiControlPath extends IdPath {
	
	/**
	 * @return \rocket\ei\EiCommandPath
	 */
	function getEiCommandPath() {
		return EiCommandPath::create($this->getFirstId());
	}
	
	public function ext(...$args): GuiControlPath {
		return new GuiControlPath(array_merge($this->ids, $this->argsToIds($args)));
	}
	
	public static function from(EiCommandPath $eiCommandPath, array $controlIds) {
		return new GuiControlPath([$eiCommandPath->__toString(), ...$controlIds]);
	}
	
	public static function create($expression): GuiControlPath {
		if ($expression instanceof GuiControlPath) {
			return $expression;
		}
		
		if (is_array($expression)) {
			return new GuiControlPath($expression);
		}
		
		return new GuiControlPath(explode(self::ID_SEPARATOR, $expression));
	}
}
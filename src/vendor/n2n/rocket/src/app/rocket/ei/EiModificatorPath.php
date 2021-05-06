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

use rocket\ei\component\command\EiCommand;
use rocket\ei\component\modificator\EiModificator;
use n2n\util\type\ArgUtils;

class EiModificatorPath extends IdPath {

	public function __construct(string $id) {
		parent::__construct([$id]);
	}
	
	/**
	 * @param EiCommand $eiCommand
	 * @return EiModificatorPath
	 */
	public static function from(EiModificator $eiModificator) {
		return $eiModificator->getWrapper()->getEiModificatorPath();
	}
	
	/**
	 * @param mixed $expression
	 * @return \rocket\ei\EiModificatorPath
	 */
	public static function create($expression) {
		if ($expression instanceof EiModificatorPath) {
			return $expression;
		}
	
		if ($expression instanceof EiModificator) {
			return self::from($expression);
		}
	
		if (is_string($expression)) {
			return new EiModificatorPath($expression);
		}
		
		ArgUtils::valType($expression, ['string', EiModificator::class, EiModificatorPath::class], false, 
				'expression');
	}
}

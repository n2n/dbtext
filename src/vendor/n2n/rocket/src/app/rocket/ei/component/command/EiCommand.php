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
namespace rocket\ei\component\command;

use rocket\ei\component\EiComponent;
use rocket\ei\util\Eiu;
use n2n\util\ex\IllegalStateException;
use n2n\web\http\controller\Controller;
use n2n\l10n\Lstr;

interface EiCommand extends EiComponent {
	
	/**
	 * @return Lstr
	 */
	function getLabelLstr(): Lstr;
	
	/**
	 * @return bool
	 */
	function isPrivileged(): bool;
	
	/**
	 * Will be the first called method by rocket
	 * @param EiCommandWrapper $wrapper
	 */
	function setWrapper(EiCommandWrapper $wrapper);
	
	/**
	 * @return EiCommandWrapper
	 * @throws IllegalStateException if {@self::setWrapper()} hasn't been called yet.
	 */
	function getWrapper(): EiCommandWrapper;
	
	/**
	 * @param Eiu $eiu
	 * @return Controller|null
	 */
	function lookupController(Eiu $eiu): ?Controller;
	
	/**
	 * @param mixed $obj
	 * @return boolean
	 */
	function equals($obj);
}

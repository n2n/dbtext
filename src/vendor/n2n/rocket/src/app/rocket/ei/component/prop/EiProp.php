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
namespace rocket\ei\component\prop;

use rocket\ei\component\EiComponent;
use n2n\l10n\Lstr;
use n2n\util\ex\IllegalStateException;
use n2n\reflection\property\AccessProxy;

interface EiProp extends EiComponent {
	
	/**
	 * @return Lstr
	 */
	public function getLabelLstr(): Lstr;
	
	/**
	 * @return Lstr|NULL
	 */
	public function getHelpTextLstr(): ?Lstr;
	
	/**
	 * @return bool
	 */
	public function isPrivileged(): bool;
	
	/**
	 * Will be the first called method by rocket
	 * @param EiPropWrapper $eiPropWrapper
	 */
	public function setWrapper(EiPropWrapper $eiPropWrapper);
	
	/**
	 * @return EiPropWrapper
	 * @throws IllegalStateException if {@self::setWrapper()} hasn't been called yet.
	 */
	public function getWrapper(): EiPropWrapper;
	
	/**
	 * @return AccessProxy|NULL
	 */
	public function getObjectPropertyAccessProxy(): ?AccessProxy;
	
	/**
	 * @return bool
	 */
	public function isPropFork(): bool;
	
	/**
	 * @param object $object
	 * @return object
	 * @throws IllegalStateException if {@see self::isPropFork()} returns false
	 */
	public function getPropForkObject(object $object): object;
}

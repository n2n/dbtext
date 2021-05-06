// <?php
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\ei\manage\gui;

// use rocket\ei\manage\EiObject;
// use rocket\ei\util\Eiu;
// use n2n\util\ex\IllegalStateException;
// use rocket\ei\manage\entry\UnknownEiFieldExcpetion;
// use rocket\ei\manage\DefPropPath;
// use rocket\ei\manage\gui\field\GuiFieldFork;

// interface GuiPropFork {

// 	/**
// 	 * @return GuiDefinition
// 	 */
// 	public function getForkedGuiDefinition(): GuiDefinition;
	
// 	/**
// 	 * @param Eiu $eiu
// 	 * @return GuiFieldFork
// 	 */
// 	public function createGuiFieldFork(Eiu $eiu): GuiFieldFork;
		
// 	/**
// 	 * @param Eiu $eiu
// 	 * @return EiObject|null null if not available
// 	 * @throws IllegalStateException if {@see self::getForkedGuiDefinition()}
// 	 */
// 	public function determineForkedEiObject(Eiu $eiu): ?EiObject;
	
// 	/**
// 	 * @param Eiu $eiu
// 	 * @param DefPropPath $defPropPath
// 	 * @return EiFieldAbstraction
// 	 * @throws IllegalStateException if {@see self::getForkedGuiDefinition()}
// 	 * @throws UnknownEiFieldExcpetion if EiFieldAbstraction is not resovable.
// 	 */
// 	public function determineEiFieldAbstraction(Eiu $eiu, DefPropPath $defPropPath): EiFieldAbstraction;
// }
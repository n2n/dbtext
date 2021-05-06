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
// namespace rocket\impl\ei\component\prop\adapter\gui;

// use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
// use rocket\ei\manage\gui\GuiProp;
// use rocket\ei\manage\gui\GuiFieldAssembler;

// class GuiProps {
    
//     /**
//      * @param GuiProp $guiProp
//      * @return \rocket\ei\manage\gui\GuiProp
//      */
//     static function statless(GuiProp $guiProp)  {
//         return $guiProp;
//     }
    
//     /**
//      * @param DisplayConfig $displayConfig
//      * @param GuiFieldFactory $guiFieldFactory
//      * @return \rocket\ei\manage\gui\GuiProp
//      */
//     static function configAndAssembler(DisplayConfig $displayConfig, GuiFieldAssembler $guiFieldAssembler) {
//     	return new GuiPropProxy($displayConfig, $guiFieldAssembler, null);
//     }
    
//     /**
//      * @param DisplayConfig $displayConfig
//      * @param \Closure $closure
//      * @return \rocket\ei\manage\gui\GuiProp
//      */
//     static function configAndCallback(DisplayConfig $displayConfig, \Closure $closure) {
//     	return new GuiPropProxy($displayConfig, null, $closure);
//     }
// }
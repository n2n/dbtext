// <?php
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
// namespace rocket\impl\ei\component\command\tree;

// use rocket\impl\ei\component\command\tree\field\TreeRightEiProp;
// use rocket\impl\ei\component\command\tree\field\TreeLeftEiProp;
// use rocket\impl\ei\component\command\tree\controller\TreeController;
// use rocket\ei\component\command\EiCommand;
// use rocket\spec\core\IncompatibleSpecException;
// use rocket\ei\EiType;
// use rocket\ei\manage\frame\EiFrame;

// class TreeUtils {
// 	public static function findTreeField(EiType $eiType, &$treeLeftEiProp = null, 
// 				&$treeRightEiProp = null, &$treeRootIdEiProp = null) {

// 		foreach ($eiType->getEiMask()->getEiPropCollection()->toArray() as  $eiProp) {
// 			if ($eiProp instanceof TreeLeftEiProp) {
// 				$treeLeftEiProp = $eiProp;
// 			} else if ($eiProp instanceof TreeRightEiProp) {
// 				$treeRightEiProp = $eiProp;
// 			}
// 		}
// 	}
	
// 	public static function initializeController(EiCommand $eiCommand, TreeController $treeController, EiFrame $eiFrame) {
// 		$eiType = $eiCommand->getEiType();
// 		$treeLeftEiProp = null;
// 		$treeRightEiProp = null;
// 		$treeRootIdEiProp = null;
// 		foreach ($eiFrame->getContextEiEngine()->getEiMask()->getEiDef()->getEiPropCollection() as  $eiProp) {
// 			if ($eiProp instanceof TreeLeftEiProp) {
// 				$treeLeftEiProp = $eiProp;
// 			} else if ($eiProp instanceof TreeRightEiProp) {
// 				$treeRightEiProp = $eiProp;
// 			}
// 		}

// 		if (null === $treeLeftEiProp) {
// 			throw self::createIncompatibleSpecException($eiCommand, 'rocket\impl\ei\component\command\tree\field\TreeLeftEiProp');
// 		}

// 		if (null === $treeRightEiProp) {
// 			throw self::createIncompatibleSpecException($eiCommand, 'rocket\impl\ei\component\command\tree\field\TreeRightEiProp');
// 		}

// 		$treeController->initialize($treeLeftEiProp, $treeRightEiProp);
		
// 		$eiFrame->getN2nContext()->magicInit($treeController);
// 	}

// 	private static function createIncompatibleSpecException(EiCommand $eiCommand, $missingEiProp) {
// 		return new IncompatibleSpecException('Command \'' . get_class($eiCommand) . '\' requires a field of type \''
// 				. $missingEiProp . '\'.');
// 	}
// }

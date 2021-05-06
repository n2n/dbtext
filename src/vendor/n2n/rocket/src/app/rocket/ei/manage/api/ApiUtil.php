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
namespace rocket\ei\manage\api;

use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\gui\EiEntryGui;
use n2n\util\type\ArgUtils;
use rocket\si\meta\SiDeclaration;
use rocket\ei\EiEngineUtil;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\si\content\SiPartialContent;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\manage\gui\EiGui;

class ApiUtil {
	private $eiFrame;
	private $eiEngineUtil;
	
	function __construct(EiFrame $eiFrame) {
		$this->eiFrame = $eiFrame;
		$this->eiEngineUtil = new EiEngineUtil($eiFrame->getContextEiEngine(), $eiFrame->getManageState());
	}
	
// 	/**
// 	 * @param EiEntryGui[]
// 	 * @return \rocket\ei\manage\gui\EiGuiFrame
// 	 */
// 	function createMultiBuildupSiDeclaration(array $eiEntryGuis) {
// 		$declaration = new SiDeclaration();
		
// 		foreach ($eiEntryGuis as $eiEntryGui) {
// 			ArgUtils::assertTrue($eiEntryGui instanceof EiEntryGui);
			
// 			$declaration->addTypeDeclaration($eiEntryGui->getEiGuiFrame()->createSiTypDeclaration());
// 		}
		
// 		return $declaration;
// 	}
	
	
// 	/**
// 	 * @param EiGuiFrame $eiGuiFrame
// 	 * @return SiDeclaration
// 	 */
// 	function createSiDeclaration(EiGuiFrame $eiGuiFrame) {
// 		$typeId = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType()->getId();
		
// 		$declaration = new SiDeclaration();
// 		$declaration->putFieldDeclarations($typeId, $eiGuiFrame->getEiGuiSiFactory()->getSiProps());
// 		$declaration->putFieldStructureDeclarations($typeId, $eiGuiFrame->getEiGuiSiFactory()->getSiStructureDeclarations());
		
// 		return $declaration;
// 	}

	
	/**
	 * @param EiObject $eiObject
	 * @return \rocket\si\content\SiEntryQualifier
	 */
	function createSiEntryQualifier(EiObject $eiObject) {
		return $eiObject->createSiEntryQualifier($this->eiEngineUtil->createIdName(), true);
	}
	
// 	/**
// 	 * @param EiObject $eiObject
// 	 * @param array $eiEntryGuis
// 	 * @return \rocket\si\content\SiEntry
// 	 */
// 	function createSiEntry(EiObject $eiObject, array $eiEntryGuis) {
// 		$siEntry = new SiEntry($eiObject->createSiEntryQualifier($eiObject), 
// 				ViewMode::isReadOnly($this->eiGuiFrame->getViewMode()));
		
// 		foreach ($eiEntryGuis as $eiEntryGui) {
// 			ArgUtils::assertTrue($eiEntryGui instanceof EiEntryGui);
// 			$declaration->putFieldStructureDeclarations(
// 					$eiEntryGui->getEiEntry()->getEiType()->getId(),
// 					$eiEntryGui->getEiGuiFrame()->getEiGuiSiFactory()->getSiStructureDeclarations());
// 		}
		
// 		return $siEntry;
// 	}
	
	/**
	 * @param int $offset
	 * @param int $count
	 * @param EiGui $eiGui
	 * @return \rocket\si\content\SiPartialContent
	 */
	function createSiPartialContent(int $offset, int $count, EiGui $eiGui) {
		$content = new SiPartialContent($count);
		$content->setOffset($offset);
		$content->setEntries($eiGui->createSiEntries($this->eiFrame));
		return $content;
	}
	
}
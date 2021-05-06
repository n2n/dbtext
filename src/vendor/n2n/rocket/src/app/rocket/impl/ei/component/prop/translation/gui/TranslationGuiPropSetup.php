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
namespace rocket\impl\ei\component\prop\translation\gui;

use rocket\ei\manage\gui\GuiPropSetup;
use rocket\ei\manage\gui\GuiFieldAssembler;
use rocket\ei\util\gui\EiuGuiFrame;
use rocket\impl\ei\component\prop\translation\conf\TranslationConfig;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\manage\DefPropPath;
use rocket\ei\EiCommandPath;

class TranslationGuiPropSetup implements GuiPropSetup, GuiFieldAssembler {
	private $targetEiuGuiFrame;
	private $eiCommandPath;
	private $translationConfig;
	
	function __construct(EiuGuiFrame $targetEiuGuiFrame, EiCommandPath $eiCommandPath, 
			TranslationConfig $translationConfig) {
		$this->targetEiuGuiFrame = $targetEiuGuiFrame;
		$this->eiCommandPath = $eiCommandPath;
		$this->translationConfig = $translationConfig;
	}
	
	function getDisplayDefinition(): ?DisplayDefinition {
		return null;
	}
	
	function getGuiFieldAssembler(): GuiFieldAssembler {
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\GuiPropSetup::getForkedDisplayDefinition()
	 */
	function getForkedDisplayDefinition(DefPropPath $defPropPath): ?DisplayDefinition {
		return $this->targetEiuGuiFrame->getDisplayDefinition($defPropPath);
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		$targetEiu = $eiu->frame()->forkDiscover($eiu->prop()->getPath(), $eiu->object(), $this->targetEiuGuiFrame);
		$targetEiu->frame()->exec($this->eiCommandPath);
		
		$lted = new LazyTranslationEssentialsDeterminer($eiu, $targetEiu, $this->translationConfig);
		$tgff = new SplitGuiFieldFactory($lted, $readOnly);
		
		return $tgff->createGuiField();
		
// 		$guiFieldMap = new GuiFieldMap();
// 		foreach ($this->targetEiuGuiFrame->getEiPropPaths() as $eiPropPath) {
// 			$guiFieldMap->putGuiField($eiPropPath, $tgff->createGuiField($eiPropPath));
// 		}
		
// 		return new TranslationGuiField($lted, $guiFieldMap);
		
		// 		if ($this->copyCommand !== null) {
		// 			$translationGuiField->setCopyUrl($targetEiuFrame->getUrlToCommand($this->copyCommand)
		// 					->extR(null, array('bulky' => $eiu->guiFrame()->isBulky())));
		// 		}
	}
	
}
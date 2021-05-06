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

use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\translation\conf\TranslationConfig;
use rocket\ei\util\entry\EiuEntry;
use n2n\util\type\CastUtils;
use n2n\l10n\N2nLocale;
use n2n\util\type\ArgUtils;
use rocket\ei\util\gui\EiuEntryGui;

class LazyTranslationEssentialsDeterminer {
	private $eiu;
	private $targetEiuFrame;
	private $targetEiuGuiFrame;
	private $translationConfig;
	private $readOnly;
	
	private $targetSiDeclaration = null;
	private $n2nLocales = null;
// 	private $n2nLocaleOptions = null;
	private $activeTargetEiuEntries = null;
	private $targetEiuEntries = [];
	/**
	 * @var EiuEntryGui[]
	 */
	private $targetEiuEntryGuis = [];
	
	function __construct(Eiu $eiu, Eiu $targetEiu, TranslationConfig $translationConfig) {
		$this->eiu = $eiu;
		$this->targetEiuFrame = $targetEiu->frame();
		$this->targetEiuGuiFrame = $targetEiu->guiFrame();
		$this->translationConfig = $translationConfig;
	}
	
	function getDefPropPath() {
		return $this->eiu->guiField()->getPath();
	}
	
	function getMinNum() {
		return $this->translationConfig->getTranslationsMinNum();
	}
	
	/**
	 * @return string|NULL
	 */
	function getViewMenuTooltip() {
		return $this->eiu->dtc('rocket')->t('ei_impl_languages_view_tooltip');
	}
	
	/**
	 * @return string|NULL
	 */
	function getManagerTooltip() {
		return $this->eiu->dtc('rocket')->t('ei_impl_translation_manager_tooltip');
	}
	
	/**
	 * @return string|NULL
	 */
	function getCopyTooltip() {
		return $this->eiu->dtc('rocket')->t('ei_impl_translation_copy_tooltip');
	}
	
	/**
	 * @return \rocket\si\meta\SiDeclaration
	 */
	function getTargetSiDeclaration() {
		if ($this->targetSiDeclaration === null) {
			$this->targetSiDeclaration = $this->targetEiuGuiFrame->createSiDeclaration();
		}
		
		return $this->targetSiDeclaration;
	}
	
	/**
	 * @return N2nLocale[]
	 */
	function getN2nLocales() {
		if ($this->n2nLocales !== null) {
			return $this->n2nLocales;
		}
		
		$this->n2nLocales = [];
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$this->n2nLocales[$n2nLocaleDef->getN2nLocaleId()] = $n2nLocaleDef->getN2nLocale();
		}
		return $this->n2nLocales;
	}
	
// 	/**
// 	 * @return string[]
// 	 */
// 	function getN2nLocaleOptions() {
// 		if ($this->n2nLocaleOptions !== null) {
// 			return $this->n2nLocaleOptions;
// 		}
		
// 		$this->n2nLocaleOptions = [];
// 		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
// 			$this->n2nLocaleOptions[$n2nLocaleDef->getN2nLocaleId()] = $n2nLocaleDef->getN2nLocale()
// 					->getName($this->eiu->getN2nLocale());
// 		}
// 		return $this->n2nLocaleOptions;
// 	}
	
	/**
	 * @return \n2n\l10n\N2nLocale
	 */
	function getDisplayN2nLocale() {
		return $this->eiu->getN2nLocale();
	}
	
	/**
	 * @return \rocket\ei\util\gui\EiuGuiFrame
	 */
	function getTargetEiuGuiFrame() {
		return $this->targetEiuGuiFrame;
	}
	
	/**
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	function getTargetEiuFrame() {
		return $this->targetEiuFrame;
	}
	
	/**
	 * @return string[]
	 */
	function getActiveN2nLocaleIds() {
		$this->ensureActiveTargetEiuEntries();
		return array_keys($this->activeTargetEiuEntries);
	}
	
	/**
	 * @return string[]
	 */
	function getMandatoryN2nLocaleIds() {
		$localeIds = [];
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			if ($n2nLocaleDef->isMandatory()) {
				$localeIds[] = $n2nLocaleDef->getN2nLocaleId();
			}
		}
		return $localeIds;
	}
	
	private function ensureActiveTargetEiuEntries() {
		if ($this->activeTargetEiuEntries !== null) {
			return;
		}
		
		$mappedValues = [];
		foreach ($this->eiu->field()->getValue() as $targetEiuEntry) {
			CastUtils::assertTrue($targetEiuEntry instanceof EiuEntry);
			
			$mappedValues[(string) $targetEiuEntry->getEntityObj()->getN2nLocale()] = $targetEiuEntry;
		}
		
		$this->activeTargetEiuEntries = [];
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			
			if (isset($mappedValues[$n2nLocaleId])) {
				$this->targetEiuEntries[$n2nLocaleId] = $this->activeTargetEiuEntries[$n2nLocaleId] 
						= $mappedValues[$n2nLocaleId];
			}
		}
	}
	
	/**
	 * @param string $n2nLocaleId
	 * @return boolean
	 */
	function isN2nLocaleIdActive(string $n2nLocaleId) {
		$this->ensureActiveTargetEiuEntries();
		
		return isset($this->activeTargetEiuEntries[$n2nLocaleId]);
	}
	
	function save() {
		$this->ensureActiveTargetEiuEntries();
		
		foreach (array_keys($this->activeTargetEiuEntries) as $n2nLocaleId) {
			if (isset($this->targetEiuEntryGuis[$n2nLocaleId])) {
				$this->targetEiuEntryGuis[$n2nLocaleId]->save();
			}
		}
		
		$this->eiu->field()->setValue($this->activeTargetEiuEntries);
	}
	
	function activateTranslations(array $newN2nLocaleIds) {
		$this->ensureActiveTargetEiuEntries();
		
		foreach ($this->translationConfig->getN2nLocaleDefs() as $n2nLocaleDef) {
			$n2nLocaleId = $n2nLocaleDef->getN2nLocaleId();
			
			if (!in_array($n2nLocaleId, $newN2nLocaleIds)) {
				unset($this->activeTargetEiuEntries[$n2nLocaleId]);
				continue;
			}
			
			if (isset($this->activeTargetEiuEntries[$n2nLocaleId])) {
				continue;
			}
			
			$this->activeTargetEiuEntries[$n2nLocaleId] = $this->getTargetEiuEntry($n2nLocaleId);
		}
	}
	
	/**
	 * @param string $n2nLocaleId
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	function getTargetEiuEntryGui(string $n2nLocaleId) {
		$this->ensureActiveTargetEiuEntries();
		
		if (isset($this->targetEiuEntryGuis[$n2nLocaleId])) {
			return $this->targetEiuEntryGuis[$n2nLocaleId];
		}
		
		return $this->targetEiuEntryGuis[$n2nLocaleId] = $this->targetEiuGuiFrame->guiModel()->newEntryGui(
				$this->getTargetEiuEntry($n2nLocaleId));
	}
	
	/**
	 * @return EiuEntry[]
	 */
	function getActiveTargetEiuEntries() {
		$this->ensureActiveTargetEiuEntry();
		return $this->activeTargetEiuEntries;
	}
	
	/**
	 * @param string $n2nLocaleId
	 * @return EiuEntry|null
	 */
	function getActiveTargetEiuEntry(string $n2nLocaleId) {
		$this->ensureActiveTargetEiuEntries();
		
		return $this->activeTargetEiuEntries[$n2nLocaleId] ?? null;
	}
	
	/**
	 * @param string $n2nLocaleId
	 * @return EiuEntry
	 */
	private function getTargetEiuEntry(string $n2nLocaleId) {
		if (isset($this->targetEiuEntries[$n2nLocaleId])) {
			return $this->targetEiuEntries[$n2nLocaleId];
		}
		
		$n2nLocales = $this->getN2nLocales();
		ArgUtils::assertTrue(isset($n2nLocales[$n2nLocaleId]));
		
		return $this->targetEiuEntries[$n2nLocaleId] = $this->createTargetEiuEntry($n2nLocales[$n2nLocaleId]);
	}
// 	/**
// 	 * @return EiuEntryGui[]
// 	 */
// 	function getActiveTargetEiuEntryGuis() {
// 		$this->ensureActiveTargetEiuEntryGuis();
// 		return $this->activeTargetEiuEntryGuis;
// 	}
	
// 	/**
// 	 * @param string $n2nLocaleId
// 	 * @return EiuEntryGui|null
// 	 */
// 	function getActiveTargetEiuEntryGui(string $n2nLocaleId) {
// 		$this->ensureActiveTargetEiuEntryGuis();
		
// 		return $this->activeTargetEiuEntryGuis[$n2nLocaleId] ?? null;
// 	}
	
	
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return EiuEntry
	 */
	private function createTargetEiuEntry($n2nLocale) {
		$targetEiuEntry = $this->targetEiuFrame->newEntry();
		$targetEiuEntry->getEntityObj()->setN2nLocale($n2nLocale);
		return $targetEiuEntry;
	}
}

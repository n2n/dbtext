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
namespace rocket\impl\ei\component\prop\string\cke\conf;

use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\EnumMag;
use n2n\impl\web\dispatch\mag\model\StringArrayMag;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use rocket\ei\component\EiSetup;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\util\StringUtils;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\util\magic\MagicObjectUnavailableException;
use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider;
use rocket\impl\ei\component\prop\string\cke\model\CkeUtils;
use n2n\persistence\meta\structure\Column;
use rocket\impl\ei\component\prop\string\cke\model\CkeState;
use n2n\util\type\CastUtils;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use n2n\util\type\attrs\DataSet;
use rocket\ei\util\Eiu;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\col\GenericArrayObject;
use n2n\util\type\ArgUtils;
use rocket\impl\ei\component\prop\string\cke\ui\CkeConfig;

class CkeEditorConfig extends PropConfigAdaption {
	const ATTR_MODE_KEY = 'mode';
	const ATTR_LINK_PROVIDER_LOOKUP_IDS_KEY = 'linkProviders';
	const ATTR_CSS_CONFIG_LOOKUP_ID_KEY = 'cssConfig';
	const ATTR_TABLES_SUPPORTED_KEY = 'tablesSupported';
	const ATTR_BBCODE_KEY = 'bbcode';

	private $mode = CkeConfig::MODE_SIMPLE;
	private $ckeLinkProviders;
	private $ckeCssConfig = null;
	private $tableSupported = false;
	private $bbcode = false;

	function __construct() {
		$this->ckeLinkProviders = new GenericArrayObject(null, CkeLinkProvider::class);
	}
	
	function getMode() {
		return $this->mode;
	}
	
	function setMode($mode) {
		ArgUtils::valEnum($mode, CkeConfig::getModes());
		$this->mode = $mode;
	}

	/**
	 * @return \ArrayObject
	 */
	function getCkeLinkProviders() {
		return $this->ckeLinkProviders;
	}
	
	function setCkeLinkProviders(array $ckeLinkProviders) {
		$this->ckeLinkProviders->exchangeArray($ckeLinkProviders);
	}
	
	/**
	 * @return CkeCssConfig|null
	 */
	function getCkeCssConfig() {
		return $this->ckeCssConfig;
	}
	
	function setCkeCssConfig(CkeCssConfig $ckeCssConfig = null) {
		$this->ckeCssConfig = $ckeCssConfig;
	}
	
	/**
	 * @return bool
	 */
	function isTableSupported() {
		return $this->tableSupported;
	}
	
	function setTableSupported(bool $tableSupported) {
		$this->tableSupported = $tableSupported;
	}
	
	function isBbcode() {
		return $this->bbcode;
	}
	
	function setBbcode(bool $bbcode) {
		$this->bbcode = $bbcode;
	}
	
	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		$dataSet->set(self::ATTR_DISPLAY_IN_OVERVIEW_KEY, false);	
	}
	
	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$ckeState = $eiu->lookup(CkeState::class);
		CastUtils::assertTrue($ckeState instanceof CkeState);
		
		$lar = new LenientAttributeReader($dataSet);

		$magCollection->addMag(self::ATTR_MODE_KEY, new EnumMag('Mode',
				array_combine(CkeConfig::getModes(), CkeConfig::getModes()),
				$lar->getEnum(self::ATTR_MODE_KEY, CkeConfig::getModes(), $this->getMode())));
		
		$magCollection->addMag(self::ATTR_LINK_PROVIDER_LOOKUP_IDS_KEY, 
				new StringArrayMag('Link Provider Lookup Ids', $lar->getScalarArray(self::ATTR_LINK_PROVIDER_LOOKUP_IDS_KEY), false,
						['class' => 'hangar-autocompletion', 'data-suggestions' => StringUtils::jsonEncode($ckeState->getRegisteredCkeLinkProviderLookupIds())]));
		
		$magCollection->addMag(self::ATTR_CSS_CONFIG_LOOKUP_ID_KEY, new StringMag('Css Config Lookup Id',
				$lar->getString(self::ATTR_CSS_CONFIG_LOOKUP_ID_KEY), false, null, false, null, 
				['class' => 'hangar-autocompletion', 'data-suggestions' => StringUtils::jsonEncode($ckeState->getRegisteredCkeCssConfigLookupIds())]));
		
		$magCollection->addMag(self::ATTR_TABLES_SUPPORTED_KEY, new BoolMag('Table Editing',
				$lar->getBool(self::ATTR_TABLES_SUPPORTED_KEY, $this->isTableSupported())));
		$magCollection->addMag(self::ATTR_BBCODE_KEY, new BoolMag('BBcode',
				$lar->getBool(self::ATTR_BBCODE_KEY, $this->isBbcode())));
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$dataSet->appendAll($magCollection->readValues(array(self::ATTR_MODE_KEY, 
				self::ATTR_LINK_PROVIDER_LOOKUP_IDS_KEY, self::ATTR_CSS_CONFIG_LOOKUP_ID_KEY, 
				self::ATTR_TABLES_SUPPORTED_KEY, self::ATTR_BBCODE_KEY), true), true);
	}
	
	function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$level = parent::testCompatibility($propertyAssignation);
		if (CompatibilityLevel::NOT_COMPATIBLE === $level) {
			return $level;
		}
		
		if (StringUtils::endsWith('Html', $propertyAssignation->getObjectPropertyAccessProxy(true)->getPropertyName())) {
			return CompatibilityLevel::COMMON;
		}
		
		return $level;
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		$this->setMode($dataSet->optEnum(self::ATTR_MODE_KEY, CkeConfig::getModes(), $this->getMode(), false));
		
		$ckeState = $eiu->lookup(CkeState::class);
		CastUtils::assertTrue($ckeState instanceof CkeState);
		
		$ckeLinkProviderLookupIds = $dataSet->getScalarArray(self::ATTR_LINK_PROVIDER_LOOKUP_IDS_KEY, false);
		try {
			$ckeLinkProviders = CkeUtils::lookupCkeLinkProviders($ckeLinkProviderLookupIds, $eiu->getN2nContext());
			foreach ($ckeLinkProviders as $ckeLinkProvider) {
				$ckeState->registerCkeLinkProvider($ckeLinkProvider);
			}
		} catch (\InvalidArgumentException $e) {
			throw $eiSetupProcess->createException('Invalid css config', $e);
		}
		$this->setCkeLinkProviders($ckeLinkProviders);
		
		$ckeCssConfigLookupId = $dataSet->getString(self::ATTR_CSS_CONFIG_LOOKUP_ID_KEY, false, null, true);
		try {
			$ckeCssConfig = CkeUtils::lookupCkeCssConfig($ckeCssConfigLookupId, $eiu->getN2nContext());
			if (null !== $ckeCssConfig) {
				$ckeState->registerCkeCssConfig($ckeCssConfig);
			}
		} catch (\InvalidArgumentException $e) {
			throw $eiSetupProcess->createException('Invalid css config', $e);
		}
		$this->setCkeCssConfig($ckeCssConfig);
		
		$this->setTableSupported($dataSet->getBool(self::ATTR_TABLES_SUPPORTED_KEY, false, 
				$this->isTableSupported()));
		
		$this->setBbcode($dataSet->getBool(self::ATTR_BBCODE_KEY, false, $this->isBbcode()));
	}
	
	private function lookupCssConfig($lookupId, EiSetup $eiSetupProcess) {
		if ($lookupId === null) return null;
		
		$cssConfig = null;
		try {
			$cssConfig = $eiSetupProcess->getN2nContext()->lookup($lookupId);
		} catch (MagicObjectUnavailableException $e) {
			throw $eiSetupProcess->createException('Invalid css config.', $e);
		}
		
		if ($cssConfig instanceof CkeCssConfig) {
			return $cssConfig;
		}
		
		throw $eiSetupProcess->createException('Invalid css config. Reason: ' . get_class($cssConfig) 
				. ' does not implement ' . CkeCssConfig::class);
	}
	
	private function lookupLinkProvider($lookupId, EiSetup $eiSetupProcess) {
		$linkProvider = null;
		try {
			$linkProvider = $eiSetupProcess->getN2nContext()->lookup($lookupId);
		} catch (MagicObjectUnavailableException $e) {
			throw $eiSetupProcess->createException('Invalid link provider defined: ' . $lookupId, $e);
		}
		
		if ($linkProvider instanceof CkeLinkProvider) {
			return $linkProvider;
		}
		
		throw $eiSetupProcess->createException('Invalid link provider defined. Reason: ' . get_class($linkProvider) 
				. ' does not implement ' . CkeLinkProvider::class);
	}
}

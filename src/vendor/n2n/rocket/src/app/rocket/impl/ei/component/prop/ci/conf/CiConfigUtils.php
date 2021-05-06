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
namespace rocket\impl\ei\component\prop\ci\conf;

use rocket\ei\EiType;
use rocket\core\model\Rocket;
use n2n\core\container\N2nContext;
use rocket\impl\ei\component\prop\ci\model\ContentItem;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use n2n\impl\web\dispatch\mag\model\NumericMag;
use n2n\util\type\attrs\LenientAttributeReader;
use n2n\util\type\attrs\DataSet;
use n2n\util\type\TypeConstraint;
use rocket\impl\ei\component\prop\ci\model\PanelDeclaration;
use n2n\util\type\attrs\AttributesException;
use n2n\util\StringUtils;
use n2n\impl\web\dispatch\mag\model\MagCollectionMag;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use rocket\impl\ei\component\prop\ci\model\GridPos;

class CiConfigUtils {
	const ATTR_PANEL_NAME_KEY = 'panelName';
	const ATTR_PANEL_LABEL_KEY = 'panelLabel';
	const ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY = 'allowedContentItemIds';
	const ATTR_MIN_KEY = 'min';
	const ATTR_MAX_KEY = 'max';
	const ATTR_GRID_ENABLED_KEY = 'gridEnabled';
	const ATTR_GRID_KEY = 'grid';
	const ATTR_GRID_COL_START_KEY = 'colStart';
	const ATTR_GRID_COL_END_KEY = 'colEnd';
	const ATTR_GRID_ROW_START_KEY = 'rowStart';
	const ATTR_GRID_ROW_END_KEY = 'rowEnd';
	
	private $ciEiType;
	private $allowedContentItemOptions;

	public function __construct(EiType $ciEiType) {
		$this->ciEiType = $ciEiType;
	}

	public static function createFromN2nContext(N2nContext $n2nContext) {
		return new CiConfigUtils($n2nContext->lookup(Rocket::class)->getSpec()
				->getEiTypeByClass(ContentItem::getClass()));
	}

	public function getAllowedContentItemOptions() {
		if ($this->allowedContentItemOptions !== null) {
			return $this->allowedContentItemOptions;
		}

		$this->allowedContentItemOptions = array();
		foreach ($this->ciEiType->getAllSubEiTypes() as $subEiType) {
			$this->allowedContentItemOptions[$subEiType->getId()] = $subEiType->getEiMask()->getLabelLstr();
		}

		return $this->allowedContentItemOptions;
	}

	public function createPanelDeclarationMagCollection(bool $includePanelName) {
// 		$allowedContentItemOptions = $this->getAllowedContentItemOptions();
		
		$magCollection = new MagCollection();
		if ($includePanelName) {
			$magCollection->addMag(self::ATTR_PANEL_NAME_KEY, new StringMag('Name', null, true));
		}
		$magCollection->addMag(self::ATTR_PANEL_LABEL_KEY, new StringMag('Label', null, false));
		$magCollection->addMag(self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY, 
				new MultiSelectMag('Allowed ContentItems', $this->getAllowedContentItemOptions()));
		$magCollection->addMag(self::ATTR_MIN_KEY, new NumericMag('Min', 0, true));
		$magCollection->addMag(self::ATTR_MAX_KEY, new NumericMag('Max'));
		
		$magCollection->addMag(self::ATTR_GRID_ENABLED_KEY, $gridEnabledMag = new TogglerMag('Use grid'));
		
		$gridMagCollection = new MagCollection();
		$gridEnabledMag->setOnAssociatedMagWrappers(array(
				$gridMagCollection->addMag(self::ATTR_GRID_COL_START_KEY, new NumericMag('Col start', 1, true, 1)),
				$gridMagCollection->addMag(self::ATTR_GRID_COL_END_KEY, (new NumericMag('Col end'))->setMin(1)),
				$gridMagCollection->addMag(self::ATTR_GRID_ROW_START_KEY, new NumericMag('Row start', 1, true)),
				$gridMagCollection->addMag(self::ATTR_GRID_ROW_END_KEY, (new NumericMag('Row end'))->setMin(1))));
		$magCollection->addMag(self::ATTR_GRID_KEY, new MagCollectionMag('Grid position', $gridMagCollection));
		
		return $magCollection;
	}
	
	public function buildPanelDeclarationMagCollectionValues(array $panelDeclarationAttrs) {
		$lar = new LenientAttributeReader(new DataSet($panelDeclarationAttrs));
		$values = array(
				self::ATTR_PANEL_NAME_KEY => $lar->getString(self::ATTR_PANEL_NAME_KEY),
				self::ATTR_PANEL_LABEL_KEY => $lar->getString(self::ATTR_PANEL_LABEL_KEY),
				self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY => $lar->getArray(
						self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY, 
						TypeConstraint::createSimple('string'), null, true),
				self::ATTR_MIN_KEY => $lar->getInt(self::ATTR_MIN_KEY, 0),
				self::ATTR_MAX_KEY => $lar->getInt(self::ATTR_MAX_KEY));
		
		$gridAttrs = $lar->getArray(self::ATTR_GRID_KEY);
		$values[self::ATTR_GRID_ENABLED_KEY] = !empty($gridAttrs);
		
		$gridLar = new LenientAttributeReader(new DataSet($gridAttrs));
		
		$values[self::ATTR_GRID_KEY] = array(
				self::ATTR_GRID_COL_START_KEY => $gridLar->getInt(self::ATTR_GRID_COL_START_KEY),
				self::ATTR_GRID_COL_END_KEY => $gridLar->getInt(self::ATTR_GRID_COL_END_KEY),
				self::ATTR_GRID_ROW_START_KEY => $gridLar->getInt(self::ATTR_GRID_ROW_START_KEY),
				self::ATTR_GRID_ROW_END_KEY => $gridLar->getInt(self::ATTR_GRID_ROW_END_KEY));
		
		return $values;
	}
	
	public static function buildPanelDeclarationAttrs(array $panelDeclarationMagCollectionValues) {
		if (empty($panelDeclarationMagCollectionValues[self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY])) {
			unset($panelDeclarationMagCollectionValues[self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY]);
		}
			
		if (!isset($panelDeclarationMagCollectionValues[self::ATTR_MAX_KEY])) {
			unset($panelDeclarationMagCollectionValues[self::ATTR_MAX_KEY]);
		}
		
		if (!$panelDeclarationMagCollectionValues[self::ATTR_GRID_ENABLED_KEY]) {
			unset($panelDeclarationMagCollectionValues[self::ATTR_GRID_KEY]);
		}
		unset($panelDeclarationMagCollectionValues[self::ATTR_GRID_ENABLED_KEY]);
		
		return $panelDeclarationMagCollectionValues;
	}
	
	/**
	 * @param array $panelAttrs
	 * @throws AttributesException
	 * @return \rocket\impl\ei\component\prop\ci\model\PanelDeclaration
	 */
	public static function createPanelDeclaration(array $panelAttrs, string $panelName = null) {
		$panelDataSet = new DataSet($panelAttrs);
		
		if ($panelName === null) {
			$panelName = $panelDataSet->getString(self::ATTR_PANEL_NAME_KEY);
		}
		
		$panelLabel = null;
		if (null === ($panelLabel = $panelDataSet->getString(self::ATTR_PANEL_LABEL_KEY, false, null, true))) {
			$panelLabel = StringUtils::pretty($panelName);
		}
		
		$allowedCiIds = $panelDataSet->getArray(self::ATTR_ALLOWED_CONTENT_ITEM_IDS_KEY, false, null,
				TypeConstraint::createSimple('string'), true);
		
		$gridPos = null;
		$gridAttrs = $panelDataSet->getArray(self::ATTR_GRID_KEY, false, null);
		if ($gridAttrs !== null) {
			$gridDataSet = new DataSet($gridAttrs);
			$gridPos = new GridPos(
					$gridDataSet->reqInt(self::ATTR_GRID_COL_START_KEY),
					$gridDataSet->optInt(self::ATTR_GRID_COL_END_KEY, null, true),
					$gridDataSet->reqInt(self::ATTR_GRID_ROW_START_KEY),
					$gridDataSet->optInt(self::ATTR_GRID_ROW_END_KEY, null, true));
		}
		
		return new PanelDeclaration($panelName, $panelLabel,
				empty($allowedCiIds) ? null : $allowedCiIds,
				$panelDataSet->optInt(self::ATTR_MIN_KEY, 0, true),
				$panelDataSet->optInt(self::ATTR_MAX_KEY, null, true),
				$gridPos);
	}
}

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
namespace rocket\core\model\launch;

use n2n\util\type\attrs\DataSet;
use n2n\config\source\WritableConfigSource;
use n2n\util\type\TypeConstraint;
use n2n\util\type\ArgUtils;
use n2n\util\type\attrs\AttributesException;
use n2n\config\InvalidConfigurationException;

class LayoutExtractionManager {
	const START_MENU_ITEM_ID_KEY = 'startLaunchPadId';
	const MENU_GROUPS_KEY = 'menuGroups';
	
	private $writableConfigSource;
	private $dataSet;
	
	public function __construct(WritableConfigSource $writableConfigSource) {
		$this->writableConfigSource = $writableConfigSource;
		$this->dataSet = new DataSet();
	}
	
	public function load() {
		$this->dataSet = new DataSet($this->writableConfigSource->readArray());
	}
	
	public function flush() {
		$this->writableConfigSource->writeArray($this->dataSet->toArray());
	}
	
	public function clear() {
		$this->dataSet = new DataSet();
	}
	
	public function extractStartLaunchPadId() {
		try {
			return $this->dataSet->optString(self::START_MENU_ITEM_ID_KEY);
		} catch (AttributesException $e) {
			throw $this->createDataSourceException($e);
		}
	}
	
	private function createDataSourceException(\Exception $previous): InvalidConfigurationException {
		return new InvalidConfigurationException('Configruation error in data source: ' . $this->writableConfigSource, 
				0, $previous);
	}
	
	public function rawStartLaunchPadId(string $startLaunchPadId = null) {
		$this->dataSet->set(self::START_MENU_ITEM_ID_KEY, $startLaunchPadId);
	}
	
	/**
	 * @return MenuGroupExtraction []
	 */
	public function extractMenuGroups(): array {
		$menuGroupsRawData = null;
		try {
			$menuGroupsRawData = $this->dataSet->getArray(self::MENU_GROUPS_KEY, false, array(), 
					TypeConstraint::createArrayLike('array', false, TypeConstraint::createSimple('string')));
		} catch (AttributesException $e) {
			throw $this->createDataSourceException($e);
		}
	
		$menuGroupExtractions = array();
		foreach ($menuGroupsRawData as $label => $menuGroupRawData) {
			$menuGroupExtraction = new MenuGroupExtraction($label);
			foreach ($menuGroupRawData as $launchPadId => $label) {
				$menuGroupExtraction->addLaunchPad($launchPadId, $label);
			}
			$menuGroupExtractions[] = $menuGroupExtraction;
		}
	
		return $menuGroupExtractions;
	}
	
	public function rawMenuGroups(array $menuGroupExtractions) {
		ArgUtils::valArray($menuGroupExtractions, MenuGroupExtraction::class);
		
		$menuGroupsRawData = array();
		foreach ($menuGroupExtractions as $menuGroupExtraction) {
			$label = $menuGroupExtraction->getLabel();
			$menuGroupsRawData[$label] = array();
			foreach ($menuGroupExtraction->getLaunchPadLabels() as $launchPadId => $launchPadLabel) {
				$menuGroupsRawData[$label][$launchPadId] = $launchPadLabel;
			}
		}
		
		$this->dataSet->set(self::MENU_GROUPS_KEY, $menuGroupsRawData);
	}
}

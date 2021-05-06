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
namespace rocket\ei\component;

use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use n2n\core\container\N2nContext;
use n2n\web\dispatch\mag\MagDispatchable;

interface EiConfigurator {

	/**
	 * @return DataSet 
	 */
	public function getDataSet(): DataSet;
	
	/**
	 * @param DataSet $dataSet
	 */
	public function setDataSet(DataSet $dataSet);
	
	/**
	 * @return string 
	 */
	public function getTypeName(): string;
	
	/**
	 * @return EiComponent 
	 */
	public function getEiComponent(): EiComponent;
	
	/**
	 * No Exception should be thrown if DataSet are invalid. Use of {@link \n2n\util\type\attrs\LenientAttributeReader}
	 * recommended. {@link EiConfigurator::setup()} has already been called when invoked. It will be called whether 
	 * {@link EiConfigurator::setup()} threw an exception or not.
	 * @return MagDispatchable 
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable;
	
	/**
	 * @param MagCollection $magCollection
	 * @param N2nContext $n2nContext
	 */
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext);
	
	/**
	 * @param EiSetup $eiSetup
	 * @throws InvalidEiComponentConfigurationException can be created with {@link EiSetup::createExcpetion()}
	 * @throws \n2n\util\type\attrs\AttributesException will be converted to InvalidEiComponentConfigurationException
	 * @throws \InvalidArgumentException will be converted to InvalidEiComponentConfigurationException
	 */
	public function setup(EiSetup $eiSetup);
}

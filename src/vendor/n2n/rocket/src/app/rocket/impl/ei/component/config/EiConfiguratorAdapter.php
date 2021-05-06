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
namespace rocket\impl\ei\component\config;

use rocket\ei\component\EiConfigurator;
use n2n\util\type\attrs\DataSet;
use n2n\web\dispatch\mag\MagCollection;
use n2n\core\container\N2nContext;
use rocket\ei\component\EiSetup;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\impl\web\dispatch\mag\model\MagForm;
use rocket\ei\component\EiComponent;
use rocket\ei\util\Eiu;
use n2n\util\type\TypeUtils;

abstract class EiConfiguratorAdapter implements EiConfigurator {
	protected $eiComponent;
	protected $dataSet;
	protected $reseted = false;
	
	public function __construct(EiComponent $eiComponent) {
		$this->eiComponent = $eiComponent;
		$this->dataSet = new DataSet();
	}
	
// 	/* (non-PHPdoc)
// 	 * @see \rocket\ei\component\EiConfigurator::getComponentClass()
// 	 */
// 	public function getComponentClass() {
// 		return new \ReflectionClass($this->eiComponent);
// 	}
	
	public function getEiComponent(): EiComponent {
		return $this->eiComponent;
	}
	
	/* (non-PHPdoc)
	 * @see \rocket\ei\component\EiConfigurator::getDataSet()
	 */
	public function getDataSet(): DataSet {
		return $this->dataSet;
	}
	
	public function setDataSet(DataSet $dataSet) {
		$this->dataSet = $dataSet;
	}

	/* (non-PHPdoc)
	 * @see \rocket\ei\component\EiConfigurator::getTypeName()
	 */
	public function getTypeName(): string {
        return TypeUtils::prettyName((new \ReflectionClass($this->getEiComponent()))->getShortName());
	}
	
	/**
	 * @param EiComponent $eiComponent
	 * @param array $suffixes
	 * @return string
	 */
	public static function createAutoTypeName(EiComponent $eiComponent, array $suffixes) { 
		return self::shortenTypeName(TypeUtils::prettyName((new \ReflectionClass($eiComponent))->getShortName()),
				$suffixes);
	}
	
	/**
	 * @param string $typeName
	 * @param array $suffixes
	 * @return string
	 */
	public static function shortenTypeName(string $typeName, array $suffixes) {
		$nameParts = explode(' ', $typeName);
		while (null !== ($suffix = array_pop($suffixes))) {
			if (end($nameParts) != $suffix) break;
				
			array_pop($nameParts);
		}
	
		return implode(' ', $nameParts);
	}
	
	public function setup(EiSetup $eiSetupProcess) {
	}
	
	/**
	 * @param N2nContext $n2nContext
	 * @return \rocket\ei\util\Eiu
	 */
	protected function eiu(N2nContext $n2nContext) {
		return new Eiu($this->eiComponent, $n2nContext);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\component\EiConfigurator::createMagDispatchable($n2nContext)
	 */
	public function createMagDispatchable(N2nContext $n2nContext): MagDispatchable {
		return new MagForm(new MagCollection());
	}
	
	/**
	 * {@inheritDoc}
	 * 
	 * <p>Overwrite this method if you have custom dataSet to save. If you call this method it will overwrite 
	 * the current dataSet Properties with a new empty {@see DataSet} object</p
	 * 
	 * @see \rocket\ei\component\EiConfigurator::saveMagDispatchable()
	 */
	public function saveMagDispatchable(MagDispatchable $magDispatchable, N2nContext $n2nContext) {
		$this->dataSet = new DataSet();
	}
}

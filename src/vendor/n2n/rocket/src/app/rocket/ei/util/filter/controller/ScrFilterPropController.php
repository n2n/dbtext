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
namespace rocket\ei\util\filter\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\impl\ScrController;
use n2n\web\http\controller\ParamQuery;
use rocket\core\model\Rocket;
use rocket\user\model\LoginContext;
use rocket\spec\UnknownTypeException;
use n2n\web\http\PageNotFoundException;
use rocket\ei\UnknownEiTypeExtensionException;
use n2n\web\dispatch\map\InvalidPropertyExpressionException;
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\component\CritmodFactory;
use rocket\ei\util\filter\form\FilterPropItemForm;
use rocket\ei\manage\critmod\filter\data\FilterSetting;
use n2n\util\type\attrs\DataSet;
use rocket\ei\util\filter\form\FilterGroupForm;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\ei\manage\critmod\filter\FilterDefinition;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\critmod\filter\UnknownFilterPropException;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;
use rocket\spec\TypePath;
use rocket\ei\util\Eiu;

class ScrFilterPropController extends ControllerAdapter implements ScrController {
	private $spec;
	private $loginContext;
	
	private function _init(Rocket $rocket, LoginContext $loginContext) {
		$this->spec = $rocket->getSpec();
		$this->loginContext = $loginContext;
	}
	/**
	 * {@inheritDoc}
	 * @see \n2n\web\http\controller\impl\ScrController::isValid()
	 */
	public function isValid(): bool {
		return $this->loginContext->hasCurrentUser()
				&& $this->loginContext->getCurrentUser()->isAdmin();
	}
	
	private function lookupEiThing(string $eiTypeId, string $eiMaskId = null) {
		try {
			$eiType = $this->spec->getEiTypeById($eiTypeId);
			if ($eiMaskId !== null) {
				return $eiType->getEiTypeExtensionCollection()->getById($eiMaskId);
			} 
			
			return $eiType;
		} catch (UnknownTypeException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (UnknownEiTypeExtensionException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	private function buildPropertyPath(string $str): PropertyPath {
		try {
			return PropertyPath::createFromPropertyExpression($str);
		} catch (InvalidPropertyExpressionException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	public function doSimple(string $eiTypeId, string $eiMaskId = null, ParamQuery $filterPropId, ParamQuery $propertyPath) {
		$eiThing = $this->lookupEiThing($eiTypeId, $eiMaskId);
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		$filterPropId = (string) $filterPropId;
		$filterDefinition = (new CritmodFactory($eiThing->getEiMask()->getEiPropCollection(), 
						$eiThing->getEiMask()->getEiModificatorCollection()))
				->createFilterDefinition($this->getN2nContext());
	
		$filterPropItemForm = null;
		try {
			$filterPropItemForm = new FilterPropItemForm(new FilterSetting($filterPropId, new DataSet()),
					$filterDefinition);
		} catch (UnknownFilterPropException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	
		$this->send(JhtmlResponse::view($this->createView('..\view\pseudoFilterPropItemForm.html', array(
				'filterPropItemForm' => $filterPropItemForm, 'propertyPath' => $propertyPath))));
	}
	
	public function doSecurity(string $eiTypePath, ParamQuery $filterPropId, ParamQuery $propertyPath, Eiu $eiu) {
		$eiuEngine = null;
		
		try {
			$eiuEngine = $eiu->context()->engine($eiTypePath);
		} catch (UnknownTypeException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		$filterPropId = (string) $filterPropId;
		$securityFilterDefinition = $eiuEngine->getSecurityFilterDefinition();
		$filterPropItemForm = null;
		try {
			$filterPropItemForm = new FilterPropItemForm(new FilterSetting($filterPropId, new DataSet()), 
					$securityFilterDefinition->toFilterDefinition());
		} catch (UnknownFilterPropException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$this->send(JhtmlResponse::view($this->createView('..\view\pseudoFilterPropItemForm.html', array(
				'filterPropItemForm' => $filterPropItemForm, 'propertyPath' => $propertyPath))));
	}
	
	public function doGroup(string $eiTypeId, string $eiMaskId = null, ParamQuery $propertyPath) {
		$eiThing = $this->lookupEiThing($eiTypeId, $eiMaskId);
		$propertyPath = $this->buildPropertyPath((string) $propertyPath);
		
		$filterGroupForm = new FilterGroupForm(new FilterSettingGroup(), new FilterDefinition());
		
		$this->send(JhtmlResponse::view($this->createView(
				'..\view\pseudoFilterGroupForm.html', 
				array('filterGroupForm' => $filterGroupForm, 'propertyPath' => $propertyPath))));
	}
	
	public static function buildFilterJhtmlHook(ScrRegistry $scrRegistry, TypePath $eiTypePath): FilterJhtmlHook {
		$baseUrl = $scrRegistry->registerSessionScrController(ScrFilterPropController::class);
		$eiTypePathStr = (string) $eiTypePath;
		
		return new FilterJhtmlHook(
				$baseUrl->extR(array('simple', $eiTypePathStr)),
				$baseUrl->extR(array('group', $eiTypePathStr)));
	}
	
	/**
	 * @param ScrRegistry $scrRegistry
	 * @param EiMask $eiMask
	 * @return FilterJhtmlHook
	 */
	public static function buildSecurityFilterJhtmlHook(ScrRegistry $scrRegistry, TypePath $eiTypePath): FilterJhtmlHook {
		$baseUrl = $scrRegistry->registerSessionScrController(ScrFilterPropController::class);
		$eiTypePathStr = (string) $eiTypePath;
		
		return new FilterJhtmlHook(
				$baseUrl->extR(array('security', $eiTypePathStr)),
				$baseUrl->extR(array('group', $eiTypePathStr)));
	}
}

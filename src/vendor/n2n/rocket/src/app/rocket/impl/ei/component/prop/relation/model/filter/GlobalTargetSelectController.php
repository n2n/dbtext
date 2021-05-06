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
namespace rocket\impl\ei\component\prop\relation\model\filter;

use rocket\core\model\Rocket;
use rocket\user\model\LoginContext;
use n2n\web\http\controller\impl\ScrController;
use n2n\web\http\controller\ControllerAdapter;

class GlobalRelationJhtmlController extends ControllerAdapter implements ScrController {
	private $spec;
	private $loginContext;
	
	/**
	 * @param Rocket $rocket
	 * @param LoginContext $loginContext
	 */
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
	
// 	/**
// 	 * @param string $eiTypeId
// 	 * @param string $eiMaskId
// 	 * @throws PageNotFoundException
// 	 * @return \rocket\ei\EiThing
// 	 */
// 	private function lookupEiThing(string $eiTypeId, string $eiMaskId) {
// 		try {
// 			return $this->spec->getEiTypeById($eiTypeId)->getEiTypeExtensionCollection()->getById($eiMaskId);
// 		} catch (UnknownTypeException $e) {
// 			throw new PageNotFoundException(null, 0, $e);
// 		} catch (UnknownEiTypeExtensionException $e) {
// 			throw new PageNotFoundException(null, 0, $e);
// 		}
// 	}
	
	/**
	 * @param string $eiTypeId
	 * @param string $eiMaskId
	 */
	public function index(string $eiTypeId, string $eiMaskId) {
		test($eiTypeId . ' ' . $eiMaskId);
	}
}

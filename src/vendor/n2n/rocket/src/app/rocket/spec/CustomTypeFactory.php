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
namespace rocket\spec;

use rocket\spec\extr\CustomTypeExtraction;
use n2n\core\TypeNotFoundException;
use n2n\reflection\ReflectionUtils;
use n2n\web\http\controller\Controller;
use rocket\custom\CustomType;

class CustomTypeFactory {
	/**
	 * @param CustomTypeExtraction $customTypeExtraction
	 * @return CustomType
	 * @throws InvalidSpecConfigurationException
	 */
	public static function create(CustomTypeExtraction $customTypeExtraction): CustomType {
		$constrollerClass = null;
		try {
			$controllerClass = ReflectionUtils::createReflectionClass($customTypeExtraction->getControllerLookupId());
		} catch (TypeNotFoundException $e) {
			throw $this->createControllerException($customTypeExtraction, null, $e);
		}
		
		if (!$controllerClass->implementsInterface(Controller::class)) {
			throw self::createControllerException($customTypeExtraction, $constrollerClass->getName()
					. ' must implement ' . Controller::class);
		}
		
		return new CustomType($customTypeExtraction->getId(), $customTypeExtraction->getModuleNamespace(), $controllerClass->getName());
	}
	
	private static function createControllerException(CustomTypeExtraction $customTypeExtraction, string $reason = null, 
			\Exception $e = null): \Exception {
		return new InvalidSpecConfigurationException('Invalid Controller defined for ' 
				. $customTypeExtraction->toSpecString() . ($reason !== null ? ' Reason: ' . $reason : ''), 0, $e);
	}
}

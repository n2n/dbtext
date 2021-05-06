<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\reflection;

use n2n\core\module\ModuleManager;
use n2n\util\StringUtils;
use n2n\core\module\UnknownModuleException;

class TypeExpressionResolver {
	const MODULE_ALIAS = '~';
	
	private $currentNamespace;
	private $moduleManager;
	
	public function __construct(string $currentNamespace, ModuleManager $moduleManager) {
		$this->currentNamespace = $currentNamespace;
		$this->moduleManager = $moduleManager;
	}
	
	/**
	 * @param string $typeExpression
	 * @return string
	 */
	public function resolve(string $typeExpression): string {
		if (StringUtils::startsWith(self::MODULE_ALIAS . '\\', $typeExpression)) {
			try {
				$typeExpression = $this->moduleManager->getModuleOfTypeName($this->currentNamespace)->__toString()
						. mb_substr($typeExpression, mb_strlen(self::MODULE_ALIAS));
			} catch (UnknownModuleException $e) {
				throw $this->createException($typeExpression, self::MODULE_ALIAS . ' not available.', $e);
			}
		} else if (!StringUtils::startsWith('\\', $typeExpression)) {
			$typeExpression = $this->currentNamespace . '\\' . $typeExpression;
		}
		
		$resolvedPathParts = array();
		foreach (explode('\\', $typeExpression) as $pathPart) {
			if (0 === mb_strlen($pathPart)) {
				continue;
			}
			
			switch ($pathPart) {
				case '..':
					if (empty($resolvedPathParts)) {
						throw $this->createException('Illegal occurrence of \'..\'.');
					}
					
					array_pop($resolvedPathParts);
				case '.';
					break;
				default:
					$resolvedPathParts[] = $pathPart;
			}
		}
		return implode('\\', $resolvedPathParts);
	}
	
	private function createException(string $typeExpression, string $reason, \Throwable $previous = null) {
		return new UnresolvableTypeExpressionException('Could not resolve type expression: ' . $typeExpression
				. ' Reason: ' . $reason, 0, $previous);
	}
}

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
namespace n2n\persistence\orm\nql;

use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\model\EntityModelManager;
use n2n\util\StringUtils;

class ParsingState {
	
	private $entityModelManager;
	private $expressionParser;
	private $queryString;
	private $params;
	
	private $entityClasses = array();
	private $tokenizerStack = array();
	
	public function __construct(EntityModelManager $entityModelManager, $rootQueryString, array $params) {
		$this->entityModelManager = $entityModelManager;
		$this->expressionParser = new ExpressionParser($this);
		$this->queryString = $rootQueryString;
		$this->params = $params;
	}
	
	public function getParams() {
		return $this->params;
	}
	
	public function getParam($name) {
		if (!NqlUtils::isPlaceholder($name)) return null;
		
		if (array_key_exists(mb_substr($name, 1), $this->params)) {
			return $this->params[mb_substr($name, 1)];
		}
		
		if (array_key_exists($name, $this->params)) {
			return $this->params[$name];
		}
		
		return null;
	}
	
	public function hasParam($name) {
		return NqlUtils::isPlaceholder($name) && (array_key_exists(mb_substr($name, 1), $this->params) || array_key_exists($name, $this->params));
	}
	
	/**
	 *  
	 * @param string $nql
	 * @return NqlTokenizer
	 */
	public function createTokenizer($nql) {
		$this->tokenizerStack[] = new NqlTokenizer($nql);
		return end($this->tokenizerStack);
	}
	
	public function popTokenizer() {
		if (empty($this->tokenizerStack)) {
			throw new IllegalStateException('Tokenizer stack empty');
		}
		array_pop($this->tokenizerStack);
	}
	
	public function createNqlParseException($message, $donePart = null, \Exception $previous = null) {
		$positionString = $donePart . implode('', array_reverse($this->tokenizerStack));
		if (!empty($positionString)) {
			$positionString = '. Position: \'' . $positionString . '\'';
		}
		
		return new NqlParseException($message . $positionString, 0, $previous, $this->queryString, $this->params);
	}
	
	public function parse($expression, $nextPart = null) {
		try {
			return $this->expressionParser->parse($expression);
		} catch (\InvalidArgumentException $e) {
			throw $this->createNqlParseException('Invalid expression ' . $expression, $nextPart, $e);
		}
	}
	
	public function getClassForEntityName($entityName, $strict = true) {
		$comparableEntityName = NqlUtils::removeQuotationMarks($entityName);
		
		if (!StringUtils::startsWith('\\', $comparableEntityName)) {
			$comparableEntityName = '\\' . $comparableEntityName;
		}
		
		if (isset($this->entityClasses[$comparableEntityName])) return $this->entityClasses[$comparableEntityName];
	
		$class = null;
		$registeredClassNames = array();
		foreach ($this->entityModelManager->getEntityClasses() as $entityClass) {
			if (!StringUtils::endsWith($comparableEntityName, '\\' . $entityClass->getName())) continue;

			$registeredClassNames[] = $entityClass->getName();
			$class = $entityClass;
		}
		
		
		if (null === $class) {
			if ($strict) {
				throw $this->createNqlParseException('No registered Entity with name: ' . $entityName);
			}
			
			return null;
		} 
		
		if (count($registeredClassNames) > 1) {
			throw $this->createNqlParseException($entityName . ' is ambiguos as entity name. Multiple entities are defined with this name: ' 
					. implode(', ', $registeredClassNames));
		}
	
		return $this->entityClasses[$comparableEntityName] = $class;
	}
}

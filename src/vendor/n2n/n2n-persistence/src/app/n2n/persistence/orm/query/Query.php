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
namespace n2n\persistence\orm\query;

use n2n\persistence\PdoStatement;
use n2n\persistence\Pdo;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\store\LoadingQueue;

class Query {
	private $loadingQueue;
	private $stmt;
	private $placeholders;
	private $unboundParameterNames = array();
	private $resultSelections;
	private $hiddenSelections;
	
	public function __construct(LoadingQueue $loadingQueue, PdoStatement $pdoStatement, array $placeholders, 
			array $resultSelections, array $hiddenSelections) {
		$this->loadingQueue = $loadingQueue;
		$this->stmt = $pdoStatement;
		$this->placeholders = $placeholders;
		$placeholderNames = array_keys($placeholders);
		$this->unboundParameterNames = array_combine($placeholderNames, $placeholderNames);
		$this->resultSelections = $resultSelections;
		$this->hiddenSelections = $hiddenSelections;
	}

	public function setParameter($name, $value) {
		if (!isset($this->placeholders[$name])) {
			throw new \InvalidArgumentException('Unknown parameter name: ' . $name);
		}
		
		try {
			$this->placeholders[$name]->apply($this->stmt, $value);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException('Invalid value for parameter: ' . $name, 0, $e);
		}
		
		unset($this->unboundParameterNames[$name]);
	}
	
	public function fetchSingle(array $parameters = array()) {
		$resultRows = $this->fetchArray($parameters);
		
		switch (count($resultRows)) {
			case 0: return null;
			case 1: return current($resultRows);
			default: 
				throw new QueryConflictException('Query returns multiple rows: '
						. count($resultRows));
		}
	}
	
	public function fetchArray(array $parameters = array()) {
		foreach ($parameters as $name => $value) {
			$this->setParameter($name, $value);
		}
		
		if (0 < count($this->unboundParameterNames)) {
			throw new IllegalStateException('Query contains unbound parameters: ' 
					. implode(', ', $this->unboundParameterNames));
		}
		
		$this->stmt->execute();
		
		$this->loadingQueue->registerLoading($this);
		
		$resultBuilder = new ResultBuilder($this->resultSelections, $this->hiddenSelections);
		while ($this->stmt->fetch(Pdo::FETCH_BOUND)) {
			$resultBuilder->buildRow();
		}
		$result = $resultBuilder->buildResult();
		
		$this->loadingQueue->finalizeLoading($this);
		
		return $result;
	}	
}

class ResultBuilder {
	private $resultSelections;
	private $hiddenSelections;
	
	private $hiddenValueBuilders = array();
	private $resultValueBuilderRows = array();
	
	public function __construct(array $resultSelections, array $hiddenSelections) {
		$this->resultSelections = $resultSelections;
		$this->hiddenSelections = $hiddenSelections;
	}
	
	public function buildRow() {
		foreach ($this->hiddenSelections as $selection) {
			$this->hiddenValueBuilders[] = $selection->createValueBuilder();
		}
		
		$resultValueBuilders = array();
		foreach ($this->resultSelections as $alias => $resultSelection) {
			$resultValueBuilders[$alias] = $resultSelection->createValueBuilder();
		}
		$this->resultValueBuilderRows[] = $resultValueBuilders;
	}
	
	public function buildResult() {
		foreach ($this->hiddenValueBuilders as $hiddenValueBuilder) {
			$hiddenValueBuilder->buildValue();
		}
		
		$resultRows = array();
		foreach ($this->resultValueBuilderRows as $resultValueBuilderRow) {
			$resultRow = array();
			foreach ($resultValueBuilderRow as $alias => $resultValueBuilder) {
				$resultRow[$alias] = $resultValueBuilder->buildValue();
			}
			
			if (1 == count($resultRow) && key($resultRow) === 0) {
				$resultRows[] = current($resultRow);
			} else {
				$resultRows[] = $resultRow;
			}			
		}
		
		return $resultRows;
	}
}

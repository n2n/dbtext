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

use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\util\StringUtils;
use n2n\persistence\orm\query\from\TreePath;

class Comparison {
	
	const OPERATOR_SPLIT_PATTERN = '/([!=><])+/';
	const SEPARATOR = TreePath::PROPERTY_NAME_SEPARATOR;
	
	private $parsingState;
	private $comparator;
	
	private $connectionType;
	private $leftItemExpression = '';
	private $operator;
	private $rightItemExpression = '';
	
	private $currentOperatorParts = array();
	private $groupStack = array();
	
	private $expectKeywordTest = false;
	private $testOperator;
	private $inTest = false;
	private $testExpression;
	private $processedTokens = array();
	
	public function __construct(ParsingState $parsingState) {
		$this->parsingState = $parsingState;
	}

	public function initialize(CriteriaComparator $comparator, $connectionType) {
		$this->comparator = $comparator;
		$this->connectionType = $connectionType;
	}
	
	public function groupStart() {
		if ($this->inTest) {
			$this->testExpression .= Nql::GROUP_START;
		} else {
			if	(empty($this->currentOperatorParts)) {
				$this->leftItemExpression .= Nql::GROUP_START;
			} else {
				$this->rightItemExpression .= Nql::GROUP_START;
			}
		}
		
		$this->groupStack[] = NQL::GROUP_START;
	}
	
	public function space() {
		if (empty($this->groupStack)) return;
		
		if	(empty($this->currentOperatorParts)) {
			$this->leftItemExpression .= ' ';
		} else {
			$this->rightItemExpression .= ' ';
		}
	}
	
	public function groupEnd() {
		if ($this->inTest) {
			$this->testExpression .= Nql::GROUP_END;
		} else {
			if	(empty($this->currentOperatorParts)) {
				$this->leftItemExpression .= Nql::GROUP_END;
			} else {
				$this->rightItemExpression .= Nql::GROUP_END;
			}
			
			if (empty($this->groupStack)) {
				throw $this->createNqlParseException('No group open for group close');	
			}
		}
		
		array_pop($this->groupStack);
	}
	
	public function processToken($token) {
		if (empty($token)) return;
		
		$this->processedTokens[] = $token;
		if (null === $this->connectionType || null === $this->comparator) {
			throw $this->createNqlParseException('Missing \'' . Nql::KEYWORD_AND .'\' or \'' 
					. Nql::KEYWORD_OR . '\' in comparison statement');
		}
		
		if (!empty($this->groupStack)) {
			$this->appendToken($token);
			return;
		}
		
		if ($this->expectKeywordTest) {
			if (!$this->isTestKeyWord($token)) {
				throw $this->createNqlParseException('Missing \'' . Nql::KEYWORD_AND .'\' or \''
						. Nql::KEYWORD_OR . '\' in comparison statement');
			}
			
			$this->inTest = true;
			$this->expectKeywordTest = false;
			$this->testOperator .= $token;
			return;
		}
		
		if ($this->inTest) {
			if (empty($this->testExpression)) {
				$this->testExpression = $token;
				return;
			}
			
			throw $this->createNqlParseException('Invalid comaparison statement', $token);
		}
		
		if (empty($this->leftItemExpression)
				|| (empty($this->currentOperatorParts)
						&& (StringUtils::endsWith(self::SEPARATOR, $this->leftItemExpression)
								|| StringUtils::startsWith(self::SEPARATOR, $token)))) {
			$this->processLeftItem($token);
			return;
		}

		if (null === $this->operator) {
			$this->processOperator($token);
			return;
		}

		if (empty($this->rightItemExpression) || StringUtils::endsWith(self::SEPARATOR, $this->leftItemExpression)
				|| StringUtils::startsWith(self::SEPARATOR, $token)) {
			if (empty($this->rightItemExpression) 
					&& mb_strtoupper($this->operator) === CriteriaComparator::OPERATOR_CONTAINS
					&& Nql::isKeywordNot($token)) {
				$this->operator .= ' ' . $token;
				return;
			}
			$this->rightItemExpression .= $token;
			return;
		}
		
		throw $this->createNqlParseException('Invalid comaparison statement', $token);
	}
	
	private function appendToken($token) {
		if ($this->inTest) {
			$this->testExpression .= ' ' . $token;
			return;
		}
		
		if	(empty($this->currentOperatorParts)) {
			$this->leftItemExpression .= $token;
			return;
		} 
			
		$this->rightItemExpression .= $token;
	}
	
	private function isTestKeyWord($token) {
		return mb_strtoupper($token) === Nql::KEYWORD_EXISTS;
	}
	
	private function processLeftItem($token) {
		$tokens = $this->splitByOperator($token);
		$tokenCount = count($tokens);
		
		if ($tokenCount > 3) {
			throw $this->createNqlParseException('Invalid expression in comparison statement', $token);
		}
		
		if ($tokenCount === 3) {
			$this->rightItemExpression = $tokens[2];
			return;
		}
		
		if ($tokenCount === 2) {
			$this->operator = $tokens[1];
			$this->currentOperatorParts[] = $this->operator;
			$this->leftItemExpression .= $tokens[0];
			return;
		}
		
		if ($this->isTestKeyWord($token)) {
			$this->inTest = true;
			$this->testOperator = $token;
			return;
		}
		
		if (Nql::isKeywordNot($token)) {
			$this->expectKeywordTest = true;
			$this->testOperator = $token . ' ';
			return;
		}
			
		$this->leftItemExpression .= $token;
	}
	
	private function processOperator($token) {
		if (empty($this->currentOperatorParts)) {
			$tokens = $this->splitByOperator($token);
			$tokenCount = count($tokens);
			if ($tokenCount > 2) {
				throw $this->createNqlParseException('Invalid expression in comparison statement', $token);
			}
		
			if ($tokenCount > 1) {
				$this->operator = $tokens[0];
				$this->rightItemExpression = $tokens[1];
				return;
			}
		}
		
		if (Nql::isKeywordNull($token)) {
			$this->applyCurrentOperatorParts();
			$this->rightItemExpression = Nql::KEYWORD_NULL;
			return;
		}
		
		$this->currentOperatorParts[] = mb_strtoupper($token);
		if (!in_array(implode(' ', $this->currentOperatorParts), CriteriaComparator::getOperators())) {
			return;
		}
		
		$this->applyCurrentOperatorParts();
	}
	
	public function isEmpty() {
		return null === $this->comparator;
	}
	
	public function isReadyToCompare() {
		return (!empty($this->rightItemExpression) || (!empty($this->testExpression))) 
				&& empty($this->groupStack);
	}
	
	public function doCompare() {
		if (!empty($this->groupStack)) {
			throw $this->createNqlParseException('\'' . Nql::GROUP_END . '\' expected');
		}
		
		try {
			if ($this->inTest) {
				$this->comparator->test($this->testOperator, $this->parseExpression($this->testExpression), 
						$this->connectionType == ConditionParser::CONNECTION_TYPE_AND);
			} else {
				$this->comparator->match($this->parseExpression($this->leftItemExpression, 
								' ' . $this->operator . ' ' . $this->rightItemExpression), 
						$this->operator, $this->parseExpression($this->rightItemExpression), 
						$this->connectionType == ConditionParser::CONNECTION_TYPE_AND);
			}
		} catch (\InvalidArgumentException $e) {
			throw $this->createNqlParseException('Invalid comparison statement: ' . implode(' ', $this->processedTokens), 
					null, $e);
		}
		
		$this->reset();
	}
	
	private function applyCurrentOperatorParts() {
		$this->operator = strtoupper(implode(' ', $this->currentOperatorParts));
		
		if ($this->operator === Nql::KEYWORD_IS) {
			$this->operator = CriteriaComparator::OPERATOR_EQUAL;
		}
		
		if ($this->operator === Nql::KEYWORD_IS . ' ' . Nql::KEYWORD_NOT) {
			$this->operator = CriteriaComparator::OPERATOR_NOT_EQUAL;
		}
	}	
	
	private function parseExpression($expression, $nextPart = null) {
		if ($expression === Nql::KEYWORD_NULL) {
			return null;
		}
		return $this->parsingState->parse($expression, $nextPart);
	}
	
	private function reset() {
		$this->connectionType = null;
		$this->comparator = null;
		$this->leftItemExpression = '';
		$this->operator = null;
		$this->rightItemExpression = '';
		$this->currentOperatorParts = array();
		$this->groupStack = array();
		$this->testExpression = null;
		$this->testOperator = null;
		$this->inTest = false;
		$this->processedTokens = array();
	}

	private function createNqlParseException($message, $donePart = null, \Exception $previous = null) {
		return $this->parsingState->createNqlParseException($message, $donePart, $previous);
	}
	
	private function splitByOperator($string) {
		return array_values(array_filter(preg_split(self::OPERATOR_SPLIT_PATTERN, 
				$string, null, PREG_SPLIT_DELIM_CAPTURE)));
	}
}

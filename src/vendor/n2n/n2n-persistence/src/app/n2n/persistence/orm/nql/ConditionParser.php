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

class ConditionParser {
	
	const CONNECTION_TYPE_AND = Nql::KEYWORD_AND;
	const CONNECTION_TYPE_OR = Nql::KEYWORD_OR;
	
	private $comparator;
	private $parsingState;
	private $comparison;
	
	private $currentToken;
	private $groupStack = array();
	
	private $connectionType = self::CONNECTION_TYPE_AND;
	
	public function __construct(ParsingState $parsingState, CriteriaComparator $comparator) {
		$this->parsingState = $parsingState;
		$this->comparator = $comparator;
		$this->comparison = new Comparison($parsingState);
	}
	
	public function parse($nql) {
		$tokenizer = $this->parsingState->createTokenizer($nql);
		
		while (null !== ($char = $tokenizer->getNext())) {
			if ($char == Nql::GROUP_START || $char == NQL::GROUP_END) {
				$this->processGroup($char);
				continue;
			}
			
			if (!StringUtils::isEmpty($char)) {
				$this->currentToken .= $char;
				continue;
			}
			
			$this->processCurrentToken();
			
			if (!$this->comparison->isEmpty()) {
				$this->comparison->space();	
			}
			
			$this->currentToken = '';
		}
		
		$this->finalize();
	}
	
	private function processGroup($char) {
		$this->processCurrentToken();
		$this->currentToken = '';
		
		if (!$this->comparison->isEmpty() && !$this->comparison->isReadyToCompare()) {
			if ($char == Nql::GROUP_START) {
				$this->comparison->groupStart();
			} else {
				$this->comparison->groupEnd();
			}
			return;
		}
		
		if ($char == Nql::GROUP_START) {
			$this->groupStack[] = $this->determineCurrentComparator()->group($this->connectionType == self::CONNECTION_TYPE_AND);
			$this->connectionType = self::CONNECTION_TYPE_AND;
		} else {
			if (empty($this->groupStack)) {
				throw $this->createNqlParseException('No group open for group close');	
			}
			if (!$this->comparison->isEmpty()) {
				$this->comparison->doCompare();
			}
			$this->connectionType = null;
			
			array_pop($this->groupStack);
		}
	}
	
	private function processCurrentToken() {
		if (empty($this->currentToken)) return;
		
		if (null === $this->connectionType) {
			if (!in_array($this->currentToken, array(self::CONNECTION_TYPE_AND, self::CONNECTION_TYPE_OR))) {
				if (null === $this->connectionType) {
					throw $this->createNqlParseException('Missing \'' . Nql::KEYWORD_AND .'\' or \''
							. Nql::KEYWORD_OR . '\' in comparison statement');
				}
			}
			$this->connectionType = $this->currentToken;
			return;
		}
		
		if ($this->comparison->isReadyToCompare() && $this->isConnectionType($this->currentToken)) {
			$this->comparison->doCompare();
			$this->connectionType = $this->currentToken;
			return;
		}
		
		if ($this->comparison->isEmpty()) {
			$this->comparison->initialize($this->determineCurrentComparator(), $this->connectionType);
		}
		
		$this->comparison->processToken($this->currentToken);
	}
	
	private function isConnectionType($token) {
		return in_array(mb_strtoupper($token), 
				array(self::CONNECTION_TYPE_AND, self::CONNECTION_TYPE_OR));
	}
	
	private function determineCurrentComparator() {
		if (empty($this->groupStack)) {
			return $this->comparator;
		}
		
		return end($this->groupStack);
	}

	private function createNqlParseException($message) {
		return $this->parsingState->createNqlParseException($message);
	}
	
	private function finalize() {
		if (NqlUtils::isNoticeableKeyword($this->currentToken)) {
			$this->currentToken = null;
		}
		
		$this->processCurrentToken();
		if (!$this->comparison->isEmpty()) {
			$this->comparison->doCompare();
		}
		
		$this->parsingState->popTokenizer();
	}
}

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
namespace n2n\persistence\orm\nql\processor;

use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\nql\Nql;
use n2n\persistence\orm\nql\ParsingState;
use n2n\persistence\orm\nql\NqlUtils;
use n2n\util\StringUtils;

abstract class KeywordProcesserAdapter implements KeywordProcessor {
	
	protected $parsingState;
	/**
	 * @var Criteria
	 */
	protected $criteria;
	
	protected $groupStack = array();
	protected $currentToken = '';
	
	protected $processGroups = true;
	protected $processedString;
	
	public function initialize(ParsingState $parsingState, Criteria $criteria) {
		$this->parsingState = $parsingState;
		$this->criteria = $criteria;
	}
	
	protected function parseExpr($expr) {
		return $this->parsingState->parse($expr);
	}
	
	/**
	 * @return ParsingState
	 */
	protected function getParsingState() {
		return $this->parsingState;
	}
	
	public function process($currentChar) {
		$this->processedString .= $currentChar;
		if (StringUtils::isEmpty($currentChar) && empty($this->currentToken)) return;
		
		if (!$this->processGroups) {
			$this->processChar($currentChar);
			return;
		}
		
		if ($currentChar === Nql::GROUP_START) {
			$this->groupStack[] = $currentChar;
		} else if ($currentChar === Nql::GROUP_END) {
			array_pop($this->groupStack);
		}
		
		if (empty($this->groupStack)) {
			$this->processChar($currentChar);
		} else {
			$this->currentToken .= $currentChar;
		}
	}

	public function finalize() {
		if (NqlUtils::isNoticeableKeyword($this->currentToken)) {
			$this->currentToken = '';
		}
		
		if (!empty($this->groupStack)) {
			throw $this->createNqlParseException('Group end expected');		
		}
	}
	
	public function isReadyToFinalize() {
		return true;
	}
	
	protected function createNqlParseException($message, $donePart = null, \Exception $previous = null) {
		return $this->parsingState->createNqlParseException($message, $donePart, $previous);
	}
	
	public abstract function processChar($char);
}

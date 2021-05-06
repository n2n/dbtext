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

use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\nql\processor\KeywordProcessor;
use n2n\persistence\orm\nql\processor\SelectProcessor;
use n2n\persistence\orm\nql\processor\FromProcessor;
use n2n\persistence\orm\nql\processor\ComparatorProcessor;
use n2n\persistence\orm\nql\processor\GroupProcessor;
use n2n\persistence\orm\nql\processor\OrderProcessor;
use n2n\persistence\orm\criteria\BaseCriteria;
use n2n\util\StringUtils;
use n2n\persistence\orm\nql\processor\LimitProcessor;

class CriteriaParser {
	
	private $parsingState;
	private $criteria;
	/**
	 * @var KeywordProcessor
	 */
	private $currentProcessor;
	private $currentToken;
	private $firstToken;

	private $root;
	private $groupStack = array();
	
	public function __construct(ParsingState $parsingState, Criteria $criteria) {
		$this->parsingState = $parsingState;
		$this->criteria = $criteria;
		$this->root = $criteria instanceof BaseCriteria;
	}
	
	public function getCriteria() {
		return $this->criteria;
	}
	
	public function parse($nql) {
		$this->currentToken = true;
		$this->firstToken = true;
		
		$this->setProcessorForKeyWord(Nql::KEYWORD_SELECT);
		$tokenizer = $this->parsingState->createTokenizer($this->cleanNql($nql));
		
		while (null !== ($char = $tokenizer->getNext())) {
			if ($this->processChar($char) === false) return;
			$this->currentProcessor->process($char);
		}
		
		$this->finalize();
	}
	
	private function cleanNql($nql) {
		$nql = trim($nql);

		while (StringUtils::startsWith(Nql::GROUP_START, $nql) 
				&& StringUtils::endsWith(Nql::GROUP_END, $nql)) {
			$nql = mb_substr($nql, 1, -1);
		};
		
		return $nql;
	}
	
	private function processChar($char) {
		if ($char == Nql::GROUP_START) {
			$this->groupStack[] = $char;
			$this->currentToken = '';
			return true;
		} 
		
		if ($char == Nql::GROUP_END) {
			if (empty($this->groupStack)) {
				if ($this->root) {
					throw $this->parsingState->createNqlParseException('Missing group open for group close');
				}
					
				$this->finalize();
				return false;
			}
			
			array_pop($this->groupStack);
			
			$this->currentToken = '';
			return true;
		}
		
		if (!StringUtils::isEmpty($char)) {
			$this->currentToken .= $char;
			return true;
		}
		
		//Emtpy
		if (empty($this->groupStack) && NqlUtils::isNoticeableKeyword($this->currentToken)) {
			
			if (!($this->firstToken && (mb_strtoupper($this->currentToken) == Nql::KEYWORD_SELECT)) ) {
				if ($this->currentProcessor->isReadyToFinalize()) {
					$this->setProcessorForKeyWord($this->currentToken);
				}
				$this->currentToken = '';
				return true;
			}
		}
	
		$this->currentToken = '';
		$firstToken = false;
		return true;
	}
	
	private function setProcessorForKeyWord($keyWord) {
		if (null !== $this->currentProcessor) {
			$this->currentProcessor->finalize();
		}
		
		$this->currentProcessor = $this->getProcessorForKeyword(mb_strtoupper($keyWord));
		if (null === $this->currentProcessor) {
			throw $this->parsingState->createNqlParseException('Invalid keyword ' . $keyWord);
		}
		
		$this->currentProcessor->initialize($this->parsingState, $this->criteria);
	}
	
	private function finalize() {
		if (!empty($this->groupStack)) {
			throw $this->parsingState->createNqlParseException(count($this->groupStack) . ' group close missing in statement');
		}
		
		$this->parsingState->popTokenizer();
		$this->currentProcessor->finalize();
	}
	/**
	 * @param string $keyword
	 * @return \n2n\persistence\orm\nql\processor\KeywordProcessor
	 */
	private function getProcessorForKeyword($keyword) {
		switch ($keyword) {
			case Nql::KEYWORD_SELECT:
				return new SelectProcessor();
			case Nql::KEYWORD_FROM:
				return new FromProcessor();
			case Nql::KEYWORD_WHERE:
			case Nql::KEYWORD_HAVING:
				return new ComparatorProcessor($keyword);
			case Nql::KEYWORD_GROUP:
				return new GroupProcessor();
			case Nql::KEYWORD_ORDER:
				return new OrderProcessor();
			case Nql::KEYWORD_LIMIT:
				return new LimitProcessor();
		}
		return null;
	}
}

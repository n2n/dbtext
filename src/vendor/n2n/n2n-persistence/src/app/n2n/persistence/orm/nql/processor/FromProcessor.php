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

use n2n\persistence\orm\nql\Nql;
use n2n\persistence\orm\nql\NqlUtils;
use n2n\util\StringUtils;

class FromProcessor extends KeywordProcesserAdapter {

	private $fromCriteria;
	private $fromEntityClass;
	
	private $fromEmpty = true;
	
	private $alias;
	
	private $joinProcessor;
	private $inJoin = false;
	
	public function processChar($char) {
		if ($this->inJoin) {
			$this->processJoin($char);
			return;
		}
		
		if (!(StringUtils::isEmpty($char) || $char === Nql::EXPRESSION_SEPERATOR)) {
			$this->currentToken .= $char;
			return;
		}
		
		if ($char === Nql::EXPRESSION_SEPERATOR) {
			$this->processCurrentToken();
			$this->doFrom();
			return;
		}
		
		if (JoinProcessor::isJoinStart($this->currentToken)) {
			$this->joinStart();
			$this->inJoin = true;
			$this->processGroups = false;
			$this->currentToken = '';
			return;
		}
		
		$this->processCurrentToken();
	}
	
	private function processCurrentToken() {
		if (empty($this->currentToken)) return;
		
		if (null !== $this->fromCriteria || null !== $this->fromEntityClass) {
			$this->alias = NqlUtils::removeQuotationMarks($this->currentToken);
			$this->currentToken = '';
			return;
		}
		
		if (NqlUtils::isCriteria($this->currentToken)) {
			$this->fromCriteria = $this->parseExpr($this->currentToken);
		} else {
			$this->fromEntityClass = $this->getParsingState()->getClassForEntityName($this->currentToken);
		}
		
		$this->currentToken = '';
	}
	
	private function doFrom() {
		if (null === $this->alias) {
			throw $this->createNqlParseException('Alias expected');
		}
		
		if (null !== $this->fromCriteria) {
			$this->criteria->fromCriteria($this->fromCriteria, $this->alias);
			$this->reset();
			return;
		} 
		
		if (null !== $this->fromEntityClass) {
			$this->criteria->from($this->fromEntityClass, $this->alias);
			$this->reset();
			return;
		}
		
		throw $this->createNqlParseException('Entity expression expected');
	}
	
	private function reset() {
		$this->fromCriteria = null;
		$this->fromEntityClass = null;
		$this->alias = null;
		$this->fromEmpty = false;
	}
	
	private function processJoin($char) {
		if ($char === Nql::EXPRESSION_SEPERATOR) {
			$this->joinProcessor->finalize();
			$this->currentToken = '';
			$this->inJoin = false;
			return;
		}
		
		if (!StringUtils::isEmpty($char)) {
			$this->currentToken .= $char;
		
			$this->joinProcessor->process($char);
			return;
		}
		
		
		if (JoinProcessor::isJoinStart($this->currentToken) && 
				!(mb_strtoupper($this->currentToken) == Nql::KEYWORD_JOIN 
						&& $this->joinProcessor->isExpectingKeywordJoin())) {
							
			$this->joinStart();
			$this->currentToken = '';
			return;
		}
		
		$this->currentToken = '';
		
		$this->joinProcessor->process($char);
		
	}
	
	private function joinStart() {
		if (null !== $this->joinProcessor) {
			$this->joinProcessor->finalize();
			$this->joinProcessor->reset();
		} else {
			$this->doFrom();
			$this->joinProcessor = new JoinProcessor();
		}
		
		$this->joinProcessor->initialize($this->getParsingState(), $this->criteria);
		$this->joinProcessor->setJoinType($this->currentToken);
	}
	
	public function isReadyToFinalize() {
		return $this->inJoin || null !== $this->fromCriteria || null !== $this->fromEntityClass;
	}
	
	public function finalize() {
		parent::finalize();
		
		if ($this->inJoin) {
			$this->joinProcessor->finalize();
			return;
		} 

		$this->processCurrentToken();
		
		if ($this->fromEmpty || null !== $this->fromCriteria || null !== $this->fromEntityClass) {
			$this->doFrom();
		}
	}
}

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
use n2n\persistence\orm\criteria\Criteria;
use n2n\util\StringUtils;

class OrderProcessor extends KeywordProcesserAdapter {
	
	private $firstToken = true;
	
	private $direction;
	private $currentItem;
	
	public function processChar($char) {
		if (!(StringUtils::isEmpty($char) || $char == Nql::EXPRESSION_SEPERATOR)) {
			$this->currentToken .= $char;
			return;
		}
		
		if ($this->firstToken) {
			$this->firstToken = false;
			if (mb_strtoupper($this->currentToken) == Nql::KEYWORD_BY) {
				$this->currentToken = '';
				return;
			}
		}
		
		$this->processCurrentToken();
		$this->currentToken = '';
		
		if ($char == Nql::EXPRESSION_SEPERATOR) {
			$this->doOrder();
		}
	}

	private function processCurrentToken() {
		if (empty($this->currentToken)) return;
		if (null === $this->currentItem) {
			$this->currentItem = $this->parseExpr($this->currentToken);
			return;
		} 
		
		if (null === $this->direction) {
			$this->direction = $this->currentToken;
			return;
		}
		
		throw $this->createNqlParseException('Expression seperator (,) expected ' . $this->currentToken . ' given', 
				$this->processedString);
	}
	
	private function doOrder() {
		try {
			$this->criteria->order($this->currentItem, (null !== $this->direction) ? 
					$this->direction : Criteria::ORDER_DIRECTION_ASC);
		} catch (\InvalidArgumentException $e) {
			throw $this->createNqlParseException('Invalid ORDER statement: ', Nql::KEYWORD_ORDER . ' ' . $this->processedString);
		}
		
		$this->currentItem = null;
		$this->direction = null;
	}
	
	public function finalize() {
		parent::finalize();
		
		$this->processCurrentToken();
		
		if (null !== $this->currentItem) {
			$this->doOrder();
		}
	}
}

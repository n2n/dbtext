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
use n2n\persistence\orm\criteria\CriteriaConflictException;
use n2n\persistence\orm\query\from\TreePath;
use n2n\util\StringUtils;
use n2n\persistence\orm\nql\NqlUtils;

class SelectProcessor extends KeywordProcesserAdapter {
	
	const SEPARATOR = TreePath::PROPERTY_NAME_SEPARATOR;
	
	private $distinct = false;

	private $currentItemExpression;
	private $alias;

	private $expectAlias = false;
	private $inString = false;

	private $waitForDistinct = false;
	private $firstToken = true;
	
	public function processChar($char) {
		if ($this->inString) {
			$this->currentToken .= $char;
			if ($char === Nql::QUOTATION_MARK) {
				$this->inString = false;
			}
			return;
		}
		
		if (!StringUtils::isEmpty($char) && $char != Nql::EXPRESSION_SEPERATOR) {
			$this->currentToken .= $char; 
			if ($char === Nql::QUOTATION_MARK) {
				$this->inString = true;
			}
			return;
		}
		
		if (StringUtils::isEmpty($char)) {
			$this->processCurrentToken();
			$this->reset();
			return;
		}
		
		$this->processCurrentToken();
		//Expression Seperator
		$this->doSelect();
	}
	
	private function processCurrentToken() {
		if (empty($this->currentToken)) return;
		
		if ($this->firstToken) {
			if (mb_strtoupper($this->currentToken) == Nql::KEYWORD_SELECT) {
				return;
			}
			throw $this->createNqlParseException('Keyword ' . Nql::KEYWORD_SELECT . ' expected. ' . $this->currentToken . ' given');
		}
		
		if ($this->expectAlias) {
			$this->expectAlias = false;
			$this->alias = NqlUtils::removeLiteralIndicator(NqlUtils::removeQuotationMarks($this->currentToken));
			return;
		}
		
		if (mb_strtoupper($this->currentToken) == Nql::KEYWORD_ALIAS) {
			if (empty($this->currentItemExpression) || $this->alias !== null) {
				throw $this->createNqlParseException('Invalid position of keyword ALIAS');
			}
			
			$this->expectAlias = true;
			return;
		}
		
		if ($this->waitForDistinct && mb_strtoupper($this->currentToken) == Nql::KEYWORD_DISTINCT) {
			$this->distinct = true;
			return;
		}
		
		if (empty($this->currentItemExpression)) {
			$this->currentItemExpression = $this->currentToken;
			return;
		}
		
		if (null !== $this->alias) {
			throw $this->createNqlParseException('\'' . Nql::EXPRESSION_SEPERATOR . '\' expected', $this->currentToken);
		}
		
		if (!(StringUtils::startsWith(self::SEPARATOR, $this->currentToken) 
				|| StringUtils::endsWith(self::SEPARATOR, $this->currentItemExpression))) {
			throw $this->createNqlParseException('Invalid select Statement', $this->currentToken);
		}
		
		$this->currentItemExpression .= $this->currentToken;
	}
	
	private function doSelect() {
		if ($this->expectAlias) {
			throw $this->createNqlParseException('Alias expected in SELECT'); 
		}
		
		if (empty($this->currentItemExpression)) {
			throw $this->createNqlParseException('Empty expression');
		}
		
		$item = $this->parseExpr($this->currentItemExpression);
		try {
			$this->criteria->select($item, $this->alias);
			$this->criteria->distinct($this->distinct);
			
		} catch (CriteriaConflictException $e) {
			throw $this->createNqlParseException('Invalid select Statement');
		}
		
		$this->alias = null;
		$this->currentItemExpression = '';
		$this->currentToken = '';
	}
	
	public function finalize() {
		parent::finalize();
		
		$this->processCurrentToken();
	
		$this->doSelect();
	}
	
	private function reset($resetCurrentItem = true) {
		$this->currentToken = '';
		
		if ($this->firstToken) {
			$this->firstToken = false;
			$this->waitForDistinct = true;
		
		} elseif ($this->waitForDistinct) {
			$this->waitForDistinct = false;
		}
	}
}

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

use n2n\persistence\orm\query\from\TreePath;
use n2n\persistence\orm\criteria\item\CrIt;

class PropertyExpressionParser {
	
	private $quoted;
	private $cleanExpressionParts;
	private $currentToken;
	
	/**
	 * @var bool - indicates if the next token should be ". or end of tokens 
	 */
	private $expectSpecialChar;
	private $inQuotation;
	
	public function parse($expression) {
		$tokenizer = new NqlTokenizer($expression);
		$this->cleanExpressionParts = array();
		$this->expectSpecialChar = false;
		$this->inQuotation = false;
		$this->currentToken = null;
		
		while (null !== ($token = $tokenizer->getNext())) {
			if ($this->expectSpecialChar) {
				if (!NqlUtils::isQuotationMark($token) && $token !== TreePath::PROPERTY_NAME_SEPARATOR) {
					$this->cleanExpressionParts = array();
					return;
				}
			}
			
			if (NqlUtils::isQuotationMark($token)) {
				$this->quoted = true;
				if ($this->expectSpecialChar) {
					//escaped quotation mark
					$this->currentToken .= $token;
					$this->inQuotation = true;
					continue;
				}
				
				if ($this->inQuotation) {
					$this->expectSpecialChar = true;
					$this->inQuotation = false;
					continue;
				} 
			
				$this->inQuotation = true;
				continue;
			}
			
			if ($token === TreePath::PROPERTY_NAME_SEPARATOR) {
				if ($this->inQuotation) {
					$this->currentToken .= $token;
					continue;
				}
				
				$this->cleanExpressionParts[] = $this->currentToken;
				$this->currentToken = '';
				$this->expectSpecialChar = false;
				continue;
			}
			
			$this->currentToken .= $token;
		}
		
		$this->cleanExpressionParts[] = $this->currentToken;
	}
	
	public function getProperty() {
		if (!$this->quoted) return null;
		$cleanExpression = implode(TreePath::PROPERTY_NAME_SEPARATOR, $this->cleanExpressionParts);
		if (null === CrIt::testExpressionForProperty($cleanExpression)) return null;
		
		return CrIt::p($this->cleanExpressionParts);
	}
	
}

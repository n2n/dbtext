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
use n2n\util\StringUtils;
class LimitProcessor extends KeywordProcesserAdapter {
	
	private $numExpected = false;
	
	private $limit;
	private $num;
	
	public function processChar($char) {
		if ($char == Nql::EXPRESSION_SEPERATOR) {
			$this->processCurrentToken();
			$this->currentToken = '';
			if (null === $this->limit) {
				throw $this->createNqlParseException('No Limit given.');
			}
				
			$this->numExpected = true;
			return;
		}
		
		if (!StringUtils::isEmpty($char)) {
			$this->currentToken .= $char;
			return;
		}
		
		$this->processCurrentToken();
		$this->currentToken = '';
	}
	
	public function processCurrentToken() {
		if (empty($this->currentToken)) return;
		
		if ($this->num !== null) {
			throw $this->createNqlParseException('End of Statement Expected. \'' . $this->currentToken
					.'\' given.');
		}
		
		if (null === $this->limit) {
			if ($this->parsingState->hasParam($this->currentToken)) {
				$this->limit = $this->parsingState->getParam($this->currentToken);
				return;
			} 
				
			$this->limit = $this->currentToken;
			return;
		} 
		
		if ($this->numExpected) {
			if ($this->parsingState->hasParam($this->currentToken)) {
				$this->num = $this->parsingState->getParam($this->currentToken);
				return;
			} 
			
			$this->num = $this->currentToken;
			return;
		}
		
		throw $this->createNqlParseException('Invalid LIMIT Statement.');
	}
	
	public function finalize() {
		parent::finalize();
		
		$this->processCurrentToken();
		
		$this->criteria->limit($this->limit, $this->num);
	}
}

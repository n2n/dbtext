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

class GroupProcessor extends KeywordProcesserAdapter {
	
	private $firstToken = true;
	
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
	}

	private function processCurrentToken() {
		if (empty($this->currentToken)) return;
		
		try {
			$this->criteria->group($this->parseExpr($this->currentToken));
		} catch (\InvalidArgumentException $e) {
			throw $this->createNqlParseException('Invalid expression in GROUP statement', $this->currentToken);
		}
		
		$this->currentToken = '';
	}
	
	public function finalize() {
		parent::finalize();
		$this->processCurrentToken();
	}
}

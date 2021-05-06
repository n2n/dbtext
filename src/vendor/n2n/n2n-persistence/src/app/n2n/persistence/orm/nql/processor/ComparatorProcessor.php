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

use n2n\persistence\orm\nql\ConditionParser;
use n2n\util\ex\IllegalStateException;
use n2n\persistence\orm\nql\Nql;

class ComparatorProcessor extends KeywordProcesserAdapter {

	const TYPE_HAVING = Nql::KEYWORD_HAVING;
	const TYPE_WHERE = Nql::KEYWORD_WHERE;
	
	private $type;
	
	public function __construct($type) {
		$this->type = $type;
	}
	
	public function processChar($char) {
		$this->currentToken .= $char;
	}
	
	private function getComparator() {
		if ($this->type == self::TYPE_HAVING) {
			return $this->criteria->having();
		}
		
		if ($this->type == self::TYPE_WHERE) {
			return $this->criteria->where();
		}
		
		throw new IllegalStateException('Invalid type');
	}
	
	public function finalize() {
		parent::finalize();
		
		$conditionParser = new ConditionParser($this->getParsingState(), $this->getComparator());
		$conditionParser->parse($this->currentToken);
	}
}

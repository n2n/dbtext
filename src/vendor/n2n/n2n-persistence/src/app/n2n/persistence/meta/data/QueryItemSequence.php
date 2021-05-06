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
namespace n2n\persistence\meta\data;

use n2n\util\type\ArgUtils;

class QueryItemSequence implements QueryItem {
	const OPERATOR_SEQ = ',';
	const OPERATOR_ADD = '+';
	const OPERATOR_SUB = '-';
	const OPERATOR_MUL = '*';
	const OPERATOR_DIV = '/';
	
	private $sequenceItem;
	private $lastSequenceItem;
	
	public function __construct(QueryItem $firstQueryItem) {
		$this->sequenceItem = new QueryItemSequenceItem($firstQueryItem);
		$this->lastSequenceItem = $this->sequenceItem;
	}
	
	public function getSequenceItem() {
		return $this->sequenceItem;
	}
	
	public function add($operator, QueryItem $queryItem) {
		ArgUtils::valEnum($operator, self::getOperators());
		$sequenceItem = new QueryItemSequenceItem($queryItem);
		$this->lastSequenceItem->setSquenceOperator(new SequenceOperator($operator, $sequenceItem));
		$this->lastSequenceItem = $sequenceItem;
	}
	
	public function buildItem(QueryFragmentBuilder $itemBuilder) {
		$this->buildSequenceItem($this->sequenceItem, $itemBuilder);
	}
	
	private function buildSequenceItem(QueryItemSequenceItem $sequenceItem, QueryFragmentBuilder $itemBuilder) {
		$sequenceItem->getQueryItem()->buildItem($itemBuilder);
		$sequenceOperator = $sequenceItem->getSequenceOperator();
		if (is_null($sequenceOperator)) return;
		$itemBuilder->addOperator($sequenceOperator->getOperator());
		$this->buildSequenceItem($sequenceOperator->getNext(), $itemBuilder);
	}
	
	public static function getOperators() {
		return array(self::OPERATOR_SEQ, self::OPERATOR_ADD, 
				self::OPERATOR_SUB, self::OPERATOR_MUL, self::OPERATOR_DIV);
	}
	
	public function equals($obj) {
		if (!($obj instanceof QueryItemSequence)) return false;
		
		return $this->sequenceItem->equals($obj->getSequenceItem());
	}
}

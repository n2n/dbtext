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

class SelectStatementSequence {
	const OPERATOR_UNION = 'UNION';
	const OPERATOR_UNION_ALL = 'UNION ALL';
	const OPERATOR_UNION_DISTINCT = 'UNION DISTINCT';
	
	private $firstSequenceItem;
	private $lastSequenceItem;
	
	public function __construct(SelectStatementBuilder $firstSelectStatement) {
		$this->firstSequenceItem = new SelectStatementSequenceItem($firstSelectStatement);
		$this->lastSequenceItem = $this->firstSequenceItem;
	}
	
	public function add($operator, SelectStatementBuilder $selectStatement) {
		ArgUtils::valEnum($operator, self::getOperators());
		$sequenceItem = new SelectStatementSequenceItem($selectStatement);
		$this->lastSequenceItem->setSequenceOperator(new SequenceOperator($operator, $sequenceItem));
		$this->lastSequenceItem = $sequenceItem;
	}
	
	public function toQueryResult() {
		return new StatementQueryResult($this->toSqlString());
	}
	
	public function toSqlString() {
		return $this->buildSequenceItem($this->firstSequenceItem);
	}
	
	private function buildSequenceItem(SelectStatementSequenceItem $sequenceItem) {
		$sql = $sequenceItem->getSelectStatement()->toSqlString();
		$sequenceOperator = $sequenceItem->getSequenceOperator();
		if (is_null($sequenceOperator)) return $sql;
		$sql .= ' ' . $sequenceOperator->getOperator() . ' '
			. $this->buildSequenceItem($sequenceOperator->getNext());
		return $sql;
	}
	
	public static function getOperators() {
		return array(self::OPERATOR_UNION, self::OPERATOR_UNION_ALL, self::OPERATOR_UNION_DISTINCT);
	}
}

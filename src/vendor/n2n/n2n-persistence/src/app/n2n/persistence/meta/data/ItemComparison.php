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

class ItemComparison extends Comparison {
	private $queryItem1;
	private $operator;
	private $queryItem2;

	public function __construct(QueryItem $queryItem1, $operator, QueryItem $queryItem2) {
		ArgUtils::valEnum($operator, QueryComparator::getOperators());
		$this->queryItem1 = $queryItem1;
		$this->operator = $operator;
		$this->queryItem2 = $queryItem2;
	}
	
	public function isToSkip() {
		return false;
	}
	/**
	 *
	 * @return QueryItem
	 */
	public function getQueryItem1() {
		return $this->queryItem1;
	}
	/**
	 *
	 * @return string
	 */
	public function getOperator() {
		return $this->operator;
	}
	/**
	 *
	 * @return QueryItem
	 */
	public function getQueryItem2() {
		return $this->queryItem2;
	}
	
	public function buildQueryComparison(QueryFragmentBuilder $fragmentBuilder) {
		$this->queryItem1->buildItem($fragmentBuilder);
		$fragmentBuilder->addOperator($this->operator);
		$this->queryItem2->buildItem($fragmentBuilder);		
	}
}

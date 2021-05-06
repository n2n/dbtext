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

class TestComparison extends Comparison {
	private $operator;
	private $queryResult;

	public function __construct($operator, QueryResult $queryResult) {
		ArgUtils::valEnum($operator, QueryComparator::getTestOperators());
		$this->operator = $operator;
		$this->queryResult = $queryResult;
	}
	/* (non-PHPdoc)
	 * @see \n2n\persistence\meta\data\Comparison::isToSkip()
	 */
	public function isToSkip() {
		return false;
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
	public function getQueryResult() {
		return $this->queryResult;
	}
	
	public function buildQueryComparison(QueryFragmentBuilder $fragmentBuilder) {
		$fragmentBuilder->addOperator($this->operator);
		$this->queryResult->buildItem($fragmentBuilder);	
	}
}

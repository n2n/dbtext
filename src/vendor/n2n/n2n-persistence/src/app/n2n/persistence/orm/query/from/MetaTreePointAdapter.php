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
namespace n2n\persistence\orm\query\from;

use n2n\persistence\orm\query\from\meta\TreePointMeta;

abstract class MetaTreePointAdapter implements MetaTreePoint {
	protected $treePointMeta;
	
	public function __construct(TreePointMeta $queryPoint) {
		$this->treePointMeta = $queryPoint;
	}
	/**
	 * @return TreePointMeta
	 */
	public function getMeta() {
		return $this->treePointMeta;
	}
}

// namespace n2n\persistence\orm\query\from\meta;

// use n2n\persistence\meta\data\SelectStatementBuilder;

// abstract class TreePoint {
// 	private $queryPoint;
// 	private $requestedTreePoints = array();
// 	private $customTreePoints = array();

// 	public function __construct(TreePointMeta $queryPoint) {
// 		$this->queryPoint = $queryPoint;
// 	}

// 	public function getMeta() {
// 		return $this->queryPoint;
// 	}

// 	public function setRequestedTreePoint($namePart, PropertyJoinedTreePoint $requestedTreePoint) {
// 		$this->requestedTreePoints[$namePart] = $requestedTreePoint;
// 	}

// 	public function getRequestedTreePoint($namePart) {
// 		if (isset($this->requestedTreePoints[$namePart])) {
// 			return $this->requestedTreePoints[$namePart];
// 		}

// 		return null;
// 	}

// 	public function addCustomTreePoint(PropertyJoinedTreePoint $customTreePoint) {
// 		$this->customTreePoints[] = $customTreePoint;
// 	}

// 	protected function applyChildren(SelectStatementBuilder $selectBuilder) {
// 		foreach ($this->requestedTreePoints as $requestedTreePoint) {
// 			$requestedTreePoint->apply($selectBuilder);
// 		}
// 		foreach ($this->customTreePoints as $customTreePoint) {
// 			$customTreePoint->apply($selectBuilder);
// 		}
// 	}
// }

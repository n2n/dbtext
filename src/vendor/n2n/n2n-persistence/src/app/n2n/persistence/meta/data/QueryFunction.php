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

class QueryFunction implements QueryItem {
	const COUNT = 'COUNT';
	const SUM = 'SUM';
	const MAX = 'MAX';
	const MIN = 'MIN';
	const RAND = 'RAND';
	const AVG = 'AVG';
	
	const ABS = 'ABS';
	const COALESCE = 'COALESCE';
	const LOWER = 'LOWER';
	const LTRIM = 'LTRIM';
	const NULLIF = 'NULLIF';
	const REPLACE = 'REPLACE';
	const ROUND = 'ROUND';
	const RTRIM = 'RTRIM';
	const SOUNDEX = 'SOUNDEX';
	const TRIM = 'TRIM';
	const UPPER = 'UPPER';
	
	protected $name;
	protected $parameterQueryItem;
	
	public function __construct($name, QueryItem $parameterQueryItem = null) {
		ArgUtils::valEnum($name, self::getNames());
		$this->name = $name;
		$this->parameterQueryItem = $parameterQueryItem;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getParameterQueryItem() {
		return $this->parameterQueryItem;
	}
	
	public function buildItem(QueryFragmentBuilder $itemBuilder) {
		$itemBuilder->openFunction($this->name);
		if (isset($this->parameterQueryItem)) {
			$this->parameterQueryItem->buildItem($itemBuilder);
		}
		$itemBuilder->closeFunction();
	}
	
	public static function getNames() {
		return array(self::COUNT, self::SUM, self::MAX, self::MIN, self::RAND, self::AVG,
				self::ABS, self::COALESCE, self::LOWER, self::LTRIM, self::NULLIF, self::REPLACE, 
				self::ROUND, self::RTRIM, self::SOUNDEX, self::TRIM, self::UPPER);
	}
	
	public function equals($obj) {
		return $obj instanceof QueryFunction && $this->name === $obj->name
				&& ($this->parameterQueryItem === $obj->parameterQueryItem || 
						($this->parameterQueryItem !== null 
								&& $this->parameterQueryItem->equals($obj->parameterQueryItem)));
	}
}

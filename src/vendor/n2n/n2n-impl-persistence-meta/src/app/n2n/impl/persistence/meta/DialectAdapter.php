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
namespace n2n\impl\persistence\meta;

use n2n\persistence\meta\data\QueryComparator;
use n2n\persistence\meta\Dialect;

abstract class DialectAdapter implements Dialect {
	/**
	 * Quotes the like wildcard chars
	 * @param string $pattern
	 */
	public function escapeLikePattern(string $pattern): string {
		$esc = $this->getLikeEscapeCharacter();
		return str_replace(array($esc, QueryComparator::LIKE_WILDCARD_MANY_CHARS,
				QueryComparator::LIKE_WILDCARD_ONE_CHAR),
				array($esc . $esc,  $esc . QueryComparator::LIKE_WILDCARD_MANY_CHARS, $esc .
						QueryComparator::LIKE_WILDCARD_ONE_CHAR), $pattern);
	}
	/**
	 * Returns the escape character used in {@link Dialect::escapeLikePattern()}.
	 * @return string
	 */
	public function getLikeEscapeCharacter(): string {
		return self::DEFAULT_ESCAPING_CHARACTER;
	}
}

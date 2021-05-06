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
namespace n2n\persistence\orm\nql;

use n2n\util\StringUtils;
use n2n\persistence\orm\criteria\item\CriteriaFunction;

class NqlUtils {
	
	public static function isNoticeableKeyword(string $str) {
		return in_array(mb_strtoupper($str), Nql::getNoticeableKeyWords());
	}
	
	public static function isPlaceholder(string $str) {
		return StringUtils::startsWith(Nql::PLACHOLDER_PREFIX, $str);
	}
	
	public static function isFunction(string $str) {
		return in_array(mb_strtoupper($str), CriteriaFunction::getNames());
	}
	
	public static function isConst(string $str) {
		return Nql::isKeywordTrue($str) || Nql::isKeywordFalse($str) || Nql::isKeywordNull($str) 
				|| is_numeric($str) || Nql::isLiteral($str);
	}
	
	public static function parseConst(string $str) {
		if (Nql::isKeywordTrue($str)) {
			return true;
		}
		
		if (Nql::isKeywordFalse($str)) {
			return false;
		}
		
		if (Nql::isKeywordNull($str)) {
			return null;
		}
		
		if (is_numeric($str)) {
			return $str;
		}
		
		if (Nql::isLiteral($str)) {
			return mb_substr($str, 1, -1);
		}
		
		throw new \InvalidArgumentException($str . ' is not a Const');
	}
	
	public static function isCriteria(string $str) {
		return StringUtils::pregMatch('/^\s*' . Nql::KEYWORD_SELECT . '\s+/', $str) > 0 
				|| StringUtils::pregMatch('/^\s*' . Nql::KEYWORD_FROM . '\s+/', $str) > 0;
	}
	
	public static function isQuoted(string $str) {
		return StringUtils::startsWith('"', $str) && StringUtils::endsWith('"', $str);
	}
	
	public static function isQuotationMark(string $token) {
		return $token === Nql::QUOTATION_MARK;
	}
	
	public static function removeQuotationMarks(string $expression) {
		if ((StringUtils::startsWith('"', $expression) && StringUtils::endsWith('"', $expression))
				/* || (StringUtils::startsWith('`', $entityName) && StringUtils::endsWith('`', $entityName)) */) {
			$expression = mb_substr($expression, 1, -1);
		}
		return $expression;
	}
	
	public static function removeLiteralIndicator(string $expression) {
		if (Nql::isLiteral($expression)) {
			return mb_substr($expression, 1, -1);
		}
		return $expression;
	}
}
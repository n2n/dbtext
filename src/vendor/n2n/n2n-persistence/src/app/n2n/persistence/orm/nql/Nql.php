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

use n2n\persistence\orm\criteria\compare\CriteriaComparator;
use n2n\util\StringUtils;
class Nql {
	const GROUP_START = '(';
	const GROUP_END = ')';
	const EXPRESSION_SEPERATOR = ',';
	const PLACHOLDER_PREFIX = ':';
	const QUOTATION_MARK = '"';
	const LITERAL_INDICATOR = '\'';

	const KEYWORD_SELECT = 'SELECT';
	const KEYWORD_FROM = 'FROM';
	const KEYWORD_WHERE = 'WHERE';
	const KEYWORD_GROUP = 'GROUP';
	const KEYWORD_HAVING = 'HAVING';
	const KEYWORD_ORDER = 'ORDER';
	const KEYWORD_BY = 'BY';
	const KEYWORD_ALIAS = 'AS';
	const KEYWORD_JOIN = 'JOIN';
	const KEYWORD_ON = 'ON';
	const KEYWORD_FETCH = 'FETCH';
	const KEYWORD_AND = 'AND';
	const KEYWORD_OR = 'OR';
	const KEYWORD_DISTINCT = 'DISTINCT';
	const KEYWORD_LIMIT = 'LIMIT';
	const KEYWORD_NOT = 'NOT';
	const KEYWORD_EXISTS = 'EXISTS';
	const KEYWORD_NULL = 'NULL';
	const KEYWORD_IS = 'IS';
	const KEYWORD_CONTAINS = CriteriaComparator::OPERATOR_CONTAINS;
	const KEYWORD_TRUE = 'TRUE';
	const KEYWORD_FALSE = 'FALSE';
	
	public static function getNoticeableKeyWords() {
		return array(self::KEYWORD_SELECT, self::KEYWORD_FROM, self::KEYWORD_WHERE, 
				self::KEYWORD_GROUP, self::KEYWORD_HAVING, self::KEYWORD_ORDER, self::KEYWORD_LIMIT);
	}
	
	public static function isKeywordNot($token) {
		return self::compare($token, Nql::KEYWORD_NOT);
	}
	
	public static function isKeywordNull($token) {
		return self::compare($token, Nql::KEYWORD_NULL);
	}
	
	public static function isKeywordTrue($token) {
		return self::compare($token, Nql::KEYWORD_TRUE);
	}
	
	public static function isKeywordFalse($token) {
		return self::compare($token, Nql::KEYWORD_FALSE);
	}
	
	public static function isKeywordIs($token) {
		return self::compare($token, Nql::KEYWORD_IS);
	}
	
	public static function isKeywordContains($token) {
		return self::compare($token, Nql::KEYWORD_CONTAINS);
	}
	
	public static function isLiteral($token) {
		return StringUtils::startsWith(self::LITERAL_INDICATOR, $token) 
				&& StringUtils::endsWith(self::LITERAL_INDICATOR, $token);
	}
	
	private static function compare($token, $keyword) {
		return mb_strtoupper($token) === $keyword;
	}
}

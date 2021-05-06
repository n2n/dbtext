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
namespace n2n\util\formatter;

use n2n\util\StringUtils;

class SqlFormatter {
	private $queryString;

	protected static $reserved = [
			'CREATE', 'DROP', 'ALTER', 'ORDER', 'GROUP', 'UNION', 'DELETE', 'UNIQUE',
			'INSERT', 'TRUNCATE', 'CROSS', 'INNER', 'OUTER', 'RIGHT', 'LEFT'];

	protected static $newLineCommandNames = [
			'CREATE DATABASE', 'DROP DATABASE', 'CREATE TABLE', 'DROP TABLE', 'ALTER TABLE',
			'SELECT', 'FROM', 'WHERE', 'SET', 'ORDER BY', 'GROUP BY', 'LIMIT', 'DROP', 'JOIN',
			'VALUES', 'UPDATE', 'HAVING', 'ADD', 'AFTER', 'ALTER TABLE', 'DELETE FROM', 'UNION ALL',
			'UNION', 'EXCEPT', 'INTERSECT', 'CREATE INDEX', 'CREATE UNIQUE INDEX', 'DROP INDEX', 'INSERT INTO',
			'CROSS JOIN', 'RIGHT JOIN', 'LEFT JOIN', 'INNER JOIN', 'OUTER JOIN', 'TRUNCATE TABLE'];
	protected static $onLineCommandNames = [
			'ON', 'AND', 'OR', 'XOR', 'XAND', 'ADD', 'VALUES', 'SET'];

	public static function formatSql(string $queryString): string {
		$word = '';
		$prettyQueryString = '';
		$inStrChar = '';
		$subQueryCount = 0;
		$inBracketCount = 0;
		$wordIsReserved = false;
		
		$queryString = trim(preg_replace('/\s+/', ' ', $queryString));
		
		$chars = str_split($queryString);
		foreach ($chars as $i => $char) {
			if ($char === '"' || $char === '\'') {
				if ($inStrChar === '') {
					$inStrChar = $char;
				} elseif ($inStrChar === $char) {
					$inStrChar = '';
				}
			}
				
			if ($inStrChar !== '') {
				$prettyQueryString .= $char;
				continue;
			}

			$word .= $char;
			
			if ($char === '(') {
				$prettyQueryString .= $word;
				$word = '';
				$inBracketCount++;
			} elseif ($char === ')') {
				$inBracketCount--;
				if ($inBracketCount != $subQueryCount && $subQueryCount != 0) {
					$subQueryCount = $inBracketCount;
					$word = rtrim($word, ')');
					$prettyQueryString .= $word . "\n" . self::buildTabs($subQueryCount) . ')';
					$word = '';
				}
			}
			
			if (!isset($chars[$i + 1])) {
				$prettyQueryString .= $word;
			}
				
			if ($wordIsReserved) {
				$wordIsReserved = false;
				continue;
			}
				
			if (!StringUtils::endsWith(' ', $word)) {
				continue;
			}
				
			if ($i - strlen('SELECT ') != 0
					&& StringUtils::doEqual(trim($word), 'SELECT')
					&& $inBracketCount > 0) {
				
				$subQueryCount++;
			}
			
			if (in_array(trim($word), self::$newLineCommandNames)) {
				$prettyQueryString .= "\n" . self::buildTabs($subQueryCount) . $word;
				$word = '';
				continue;
			}

			if (in_array(trim($word), self::$onLineCommandNames)) {
				$prettyQueryString .= $word;
				$word = '';
				continue;
			}
				
			if ($char == ' ') {
				if (in_array(trim($word), self::$reserved)) {
					$wordIsReserved = true;
					continue;
				}
				$prettyQueryString .= $word;
				$word = '';
			}
		}

		return $prettyQueryString;
	}

	private static function buildTabs($amount): string {
		$tabs = '';

		for ($i = 0; $i < $amount; $i++) {
			$tabs .= "\t";
		}

		return $tabs;
	}
}

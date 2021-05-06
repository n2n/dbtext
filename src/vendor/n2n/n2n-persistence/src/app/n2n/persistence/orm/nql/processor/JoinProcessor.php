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
namespace n2n\persistence\orm\nql\processor;

use n2n\persistence\meta\data\JoinType;
use n2n\persistence\orm\nql\NqlUtils;
use n2n\persistence\orm\nql\ConditionParser;
use n2n\persistence\orm\nql\Nql;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\util\StringUtils;

class JoinProcessor extends KeywordProcesserAdapter {

	private $joined;
	
	private $joinCriteria;
	private $joinEntityClass;
	private $joinProperty;
	private $alias = null;
	private $fetch = false;
	
	private $joinNql = '';
	private $joinComparator;
	
	private $joinType = null;
	private $expectingKeywordJoin = false;
	private $processedTokensString; 
	
	public function isExpectingKeywordJoin() {
		return $this->expectingKeywordJoin;
	}
	
	public function setJoinType($joinType) {
		if (mb_strtoupper($joinType) === Nql::KEYWORD_JOIN) {
			$this->expectingKeywordJoin = false;
			$this->joinType = JoinType::INNER;
			return;
		}
		
		$this->expectingKeywordJoin = true;
		$this->joinType = $joinType;
	}
	
// 	public function process($currentChar) {
// 		$this->processChar($currentChar);
// 	}
	
	public function processChar($char) {
		if (!StringUtils::isEmpty($char)) {
			$this->currentToken .= $char;
			return;
		}
		
		if (null !== $this->joinComparator) {
			$this->joinNql .= ' ' . $this->currentToken;
			$this->currentToken = '';
			return;
		}
		
		if ($this->expectingKeywordJoin) {
			if (mb_strtoupper($this->currentToken) != Nql::KEYWORD_JOIN) {
				throw $this->createNqlParseException(Nql::KEYWORD_JOIN . ' expected. ' . $this->currentToken . ' given.');
			}
			
			$this->currentToken = '';
			$this->expectingKeywordJoin = false;
			return;
		}
		
		if (strtoupper($this->currentToken) == Nql::KEYWORD_FETCH) {
			if (null !== $this->joinCriteria || null !== $this->joinEntityClass && null !== $this->joinProperty) {
				throw $this->createNqlParseException('Invalid position for: ' . Nql::KEYWORD_FETCH);
			}
			
			$this->fetch = true;
			$this->currentToken = '';
			return;
		}
		
		$this->processCurrentToken();
	}
	
	private function processCurrentToken() {
		$this->processedTokensString .= $this->currentToken;
		if (StringUtils::isEmpty($this->currentToken)) return;
		

		if (null !== $this->joinCriteria || null !== $this->joinEntityClass || null !== $this->joinProperty) {
			if (mb_strtoupper($this->currentToken) == Nql::KEYWORD_ON) {
				if (null !== $this->joinProperty) {
					$propertyName = $this->joinProperty;
					if (null !== $this->alias) {
						$propertyName .= ' ' . $this->alias;
					}
					
					throw $this->createNqlParseException('Invalid join statement for property \'' . $propertyName 
							. '\'. Keyword \'' . Nql::KEYWORD_ON . '\' is not allowed with joined properties' );	
				}
				
				$this->doJoin();
			} elseif (null !== $this->alias) {
				throw $this->createNqlParseException('More than one alias in join');
			}
			
			$this->alias = $this->currentToken;
			$this->currentToken = '';
			return;
		} 
		
		if (NqlUtils::isCriteria($this->currentToken)) {
			$this->joinCriteria = $this->parseExpr($this->currentToken);
		} else {
			$this->joinEntityClass = $this->getParsingState()->getClassForEntityName($this->currentToken, false);
			
			if (null === $this->joinEntityClass) {
				$this->joinProperty = CrIt::testExpressionForProperty($this->currentToken);
				
				if (null === $this->joinProperty) {
					$this->createNqlParseException('Property, critera or Entity class name expected. ' 
							. $this->currentToken . ' given.');
				}
			}
		}
		
		$this->currentToken = '';
	}
	
	public function finalize() {
		parent::finalize();
		
		if (!$this->joined) {
			if (!$this->isJoinStart($this->currentToken)) {
				$this->processCurrentToken();
			}
			
			$this->doJoin();
			return;
		}
		
		if (!$this->isJoinStart($this->currentToken)) {
			$this->joinNql .= $this->currentToken;
		}
		
		if (null !== $this->joinComparator) {
			$conditionParser = new ConditionParser($this->getParsingState(), $this->joinComparator);
			$conditionParser->parse($this->joinNql);
		}
	}
	
	public function reset() {
		$this->joinCriteria = null;
		$this->joinEntityClass = null;
		$this->joinProperty = null;
		$this->joinComparator = null;
		$this->alias = null;
		$this->expectingKeywordJoin = false;
		$this->joined = false;
		$this->fetch = false;
		$this->joinNql = '';
		$this->processGroups = true;
		
		$this->currentToken = '';
		$this->processedTokensString = '';
		$this->processedString = '';
	}
	
	private function doJoin() {
		$this->joined = true;
		$this->processGroups = false;
		
		if (null !== $this->joinProperty) {
			if (null === $this->alias) {
				throw $this->createNqlParseException('Invalid join statement. Joined properties must have an alias: ' 
						. $this->processedTokensString, $this->currentToken);
			}
			$this->criteria->joinProperty($this->joinProperty, $this->alias, $this->joinType, $this->fetch);
			return;
		}
		
		if (null !== $this->joinCriteria) {
			$this->joinComparator = $this->criteria->joinCriteria($this->joinCriteria, $this->alias, $this->joinType);
			return;
		}
		
		if (null !== $this->joinEntityClass) {
			$this->joinComparator = $this->criteria->join($this->joinEntityClass, $this->alias, $this->joinType, $this->fetch);
			return;
		}
		
		throw $this->createNqlParseException('Entity or criteria expression expected');
	}
	
	public static function isJoinStart($token) {
		return self::isJoinTypeIndicator($token) || mb_strtoupper($token) == 'JOIN';
	}
	
	public static function isJoinTypeIndicator($token) {
		return in_array(mb_strtoupper($token), array(JoinType::INNER, JoinType::LEFT, JoinType::CROSS, JoinType::RIGHT));
	}
}

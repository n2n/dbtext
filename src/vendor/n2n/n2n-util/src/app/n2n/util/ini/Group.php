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
namespace n2n\util\ini;

use n2n\util\StringUtils;

class Group extends ContentPartAdapter implements DataContentPart {
	
	private $name;
	private $inlineComment;
	/**
	 * @var \n2n\util\ini\Groupable[]
	 */
	private $groupables = array();
	public function __construct(array $lines) {
		$line = array_pop($lines);
		if (IniRepresentation::isGroup($line)) {
			$maskedName = $line;
			$this->inlineComment = null;
			if (count(($maskedNameArray = preg_split('/\s*' . preg_quote(IniRepresentation::COMMENT_IDENTIFIER) . '/', $maskedName, 2))) > 1) {
				$maskedName = $maskedNameArray[0];
				$this->inlineComment = IniRepresentation::COMMENT_IDENTIFIER . trim($maskedNameArray[1]);
			}
			$this->name = preg_replace('/(.*' . preg_quote(IniRepresentation::GROUP_START_IDENTIFIER) . '|' 
					. preg_quote(IniRepresentation::GROUP_END_IDENTIFIER) . '.*)/', '', $maskedName);
			parent::__construct($lines);
		} else {
			throw new \InvalidArgumentException('Invalid group structure in line "' . $line 
					. '". Expexted structure: [{groupName}] ;{inlineComment}');	
		}
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getComment() {
		$parentComment = parent::getComment();
		if (strlen($parentComment)) {
			$parentComment = parent::getComment();
		}
		return $parentComment . $this->inlineComment;
	}
	
	public function setGroupables(array $groupables) {
		$this->groupables = $groupables;
	}
	/**
	 * @return \n2n\util\ini\Groupable[]
	 */
	public function getGroupables() {
		return $this->groupables;
	}
	/**
	 * @param \n2n\util\ini\Groupable $groupable
	 */
	public function appendGroupable(Groupable $groupable) {
		$this->groupables[] = $groupable;
	}
	/**
	 * @param \n2n\util\ini\Groupable $groupable
	 */
	public function removeGroupable(Groupable $groupable) {
		if (false !== ($key = array_search($groupable, $this->groupables, true))) {
			unset($this->groupables[$key]);
		} 
	}
	/**
	 * @param string $name
	 * @return \n2n\util\ini\Property
	 */
	public function findPropertyByName($name) {
		foreach ((array) $this->groupables as $groupable) {
			if ($groupable instanceof Property && $groupable->getName() == $name) {
				return $groupable;
			}
		}
		return null;
	}
	/**
	 * @param string $prefix
	 * @return \n2n\util\ini\Property[]
	 */
	public function findPropertiesWithPrefix($prefix) {
		$properties = array();
		foreach ((array) $this->groupables as $groupable) {
			if ($groupable instanceof Property && StringUtils::startsWith($prefix, $groupable->getName())) {
				$properties[] = $groupable;
			}
		}
		if (0 === count($properties)) {
			return null;
		}
		return $properties;
	}

	public function __toString(): string {
		return parent::__toString() .  IniRepresentation::GROUP_START_IDENTIFIER . $this->name . 
				IniRepresentation::GROUP_END_IDENTIFIER . $this->inlineComment . PHP_EOL . implode($this->groupables);
	}
	
	public static function createWithName($name) {
		return new Group(array(IniRepresentation::GROUP_START_IDENTIFIER . $name . 
				IniRepresentation::GROUP_END_IDENTIFIER));
	}
}

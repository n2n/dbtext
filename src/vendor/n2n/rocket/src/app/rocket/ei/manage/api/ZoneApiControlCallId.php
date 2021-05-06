<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\ei\manage\api;

use rocket\ei\IdPath;
use n2n\util\type\ArgUtils;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\attrs\DataSet;

class ZoneApiControlCallId extends IdPath implements  \JsonSerializable {
	
	/**
	 * @param string ...$args
	 * @return ZoneApiControlCallId
	 */
	function ext(string ...$args) {
		return new ZoneApiControlCallId(array_merge($this->ids, $this->argsToIds($args)));
	}
	
	/**
	 * {@inheritDoc}
	 * @see \JsonSerializable::jsonSerialize()
	 */
	function jsonSerialize() {
		return [
			'controlIdPath' => (string) $this
		];
	}
	
	/**
	 * @param string|array|ZoneApiControlCallId $expression
	 * @throws IllegalStateException
	 * @return \rocket\ei\manage\api\ZoneApiControlCallId
	 */
	public static function create(/*PHP8 string|array|ZoneApiControlCallId*/ $expression) {
		if ($expression instanceof ZoneApiControlCallId) {
			return $expression;
		}
		
		if (is_array($expression)) {
			return new ZoneApiControlCallId($expression);
		}
		
		if (is_scalar($expression)) {
			return new ZoneApiControlCallId(explode(self::ID_SEPARATOR, $expression));
		}
		
		ArgUtils::valType($expression, ['string', 'array', ZoneApiControlCallId::class]);
		throw new IllegalStateException();
	}
	
	/**
	 * @param array $data
	 * @throws \InvalidArgumentException
	 * @return ZoneApiControlCallId
	 */
	static function parse(array $data) {
		$ds = new DataSet($data);
		
		try {
			return ZoneApiControlCallId::create($ds->reqString('controlIdPath'));
		} catch (\n2n\util\type\attrs\AttributesException $e) {
			throw new \InvalidArgumentException(null, null, $e);
		}
	}
}
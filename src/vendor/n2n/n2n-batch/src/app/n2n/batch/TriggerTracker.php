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
namespace n2n\batch;

use n2n\config\source\WritableConfigSource;
use n2n\util\DateUtils;

class TriggerTracker {	
	const LIMN_SEPARATOR = '::';
	
	private $configSource;
	private $lastTriggeredTimestamps = array();
	
	public function __construct(WritableConfigSource $configSource) {
		$this->configSource = $configSource;
		$this->lastTriggeredTimestamps = $configSource->readArray();
	}
	
	private function buildKey(string $lookupId, string $methodName) {
		return $lookupId . self::LIMN_SEPARATOR . $methodName;
	}
	
	public function getLastTriggered(string $lookupId, string $methodName) {
		$key = $this->buildKey($lookupId, $methodName);
		if (isset($this->lastTriggeredTimestamps[$key]) && is_numeric($this->lastTriggeredTimestamps[$key])) {
			return DateUtils::createDateTimeFromTimestamp($this->lastTriggeredTimestamps[$key]);
		}
		
		return null;
	}
	
	public function setLastTriggered(string $lookupId, string $methodName, \DateTime $lastTriggered) {
		$this->lastTriggeredTimestamps[$this->buildKey($lookupId, $methodName)] = $lastTriggered->getTimestamp();
	}
	
	public function flush() {
		$this->configSource->writeArray($this->lastTriggeredTimestamps);
	}
}

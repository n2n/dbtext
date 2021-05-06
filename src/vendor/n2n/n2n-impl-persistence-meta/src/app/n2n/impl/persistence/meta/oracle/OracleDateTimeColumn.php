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
namespace n2n\impl\persistence\meta\oracle;

use n2n\persistence\meta\structure\common\DateTimeColumnAdapter;

class OracleDateTimeColumn extends DateTimeColumnAdapter {
	const FORMAT_DATE = 'Y-m-d';
	const FORMAT_TIME = 'H:i:s';
	const FORMAT_MICROSECONDS = '.u';
	const FORMAT_TIMEZONE = 'e';
	
	const FORMAT_BUILD_TYPE_RAW_VALUE = 'raw';
	const FORMAT_BUILD_TYPE_PARSE = 'parse';
	
	private $fractionalSeconds;
	private $timeZoneAvailable;
	private $localTimeZoneAvailable;
	
	public function __construct($name, $dateAvailable, $timeAvailable, $fractionalSeconds = 9, $timeZoneAvailable = false, $localTimeZoneAvailable = false) {
		parent::__construct($name, $dateAvailable, $timeAvailable);
		$this->fractionalSeconds = $fractionalSeconds;
		$this->timeZoneAvailable = $timeZoneAvailable;
		$this->localTimeZoneAvailable = $localTimeZoneAvailable;
	}
	
	public function getFractionalSeconds() {
		return $this->fractionalSeconds;
	}
	
	public function isTimeZoneAvailable() {
		return $this->timeZoneAvailable;
	}
	
	public function isLocalTimeZoneAvailable() {
		return $this->localTimeZoneAvailable;
	}
	
	public function copy($newColumnName = null) {
		if (is_null($newColumnName)) {
			$newColumnName = $this->getName();
		}
		$newColumn = new self($newColumnName, $this->isDateAvailable(), $this->isTimeAvailable());
		$newColumn->applyCommonAttributes($this);
		return $newColumn;
	}
	
	protected function getParseFormat() {
		return $this->generateFormatBuildRawValue(self::FORMAT_BUILD_TYPE_PARSE, $this->isDateAvailable(), $this->isTimeAvailable(), 
				$this->fractionalSeconds, $this->timeZoneAvailable);
	}
	
	protected function getBuildFormat() {
	
		return $this->generateFormatBuildRawValue(self::FORMAT_BUILD_TYPE_RAW_VALUE, $this->isDateAvailable(), $this->isTimeAvailable(), 
				$this->fractionalSeconds, $this->timeZoneAvailable);
	}
	
	public static function generateFormatBuildRawValue($formatBuildType, $dateAvailable, $timeAvailable, $fractionalSeconds = 9, $timeZoneAvailable = false) {
		//generate the Format
		$format = '';
		if ($dateAvailable) {
			$format .= self::FORMAT_DATE;
		}
		if ($timeAvailable) {
			if ($dateAvailable) {
				$format .= ' ';
			}
			$format .= self::FORMAT_TIME;
		}
		
		if ($fractionalSeconds > 0) {
			$format .= '.u';
			if ($formatBuildType == self::FORMAT_BUILD_TYPE_PARSE) {
				$format .= '***';
			} else {
				//Thats, that buildRawValue and generateFormatBuildRawValue are compatible to each other
				$format .= '000';
			}
		}
		if ($timeZoneAvailable) {
			$format .= ' ' . self::FORMAT_TIMEZONE;
		}
		return $format;
	}
}

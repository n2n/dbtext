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
namespace n2n\impl\persistence\meta\mssql;

use n2n\persistence\meta\structure\common\DateTimeColumnAdapter;

class MssqlDateTimeColumn extends DateTimeColumnAdapter {
	const FORMAT_DATE = 'Y-m-d';
	const FORMAT_TIME = 'H:i:s';
	const FORMAT_MICROSECONDS = '.u';
	const FORMAT_TIMEZONE = 'P';
	
	const FORMAT_BUILD_TYPE_RAW_VALUE = 'raw';
	const FORMAT_BUILD_TYPE_PARSE = 'parse';
	
	private $formatParse;
	private $formatBuildRawValue;
	private $dateAvailable;
	private $timeAvailable;
	private $dateTimeOffset;
	private $dateTimePrecicion;
	
	public function __construct($name, $dateAvailable, $timeAvailable, $dateTimePrecision = 6, $dateTimeOffset = false) {
		parent::__construct($name, $dateAvailable, $timeAvailable);
		$this->formatParse = self::generateFormatBuildRawValue(self::FORMAT_BUILD_TYPE_PARSE, 
				$dateAvailable, $timeAvailable, $dateTimePrecision, $dateTimeOffset);
		$this->formatBuildRawValue = self::generateFormatBuildRawValue(self::FORMAT_BUILD_TYPE_RAW_VALUE, 
				$dateAvailable, $timeAvailable, $dateTimePrecision, $dateTimeOffset);
		
		$this->dateTimeOffset = $dateTimeOffset;
		$this->dateTimePrecicion = $dateTimePrecision;
	}
	
	public function getDateTimeOffset() {
		return $this->dateTimeOffset;
	}
	
	public function getDateTimePrecision() {
		return $this->dateTimePrecicion;
	}
	
	protected function getParseFormat() {
		return $this->formatParse;
	}
	protected function getBuildFormat() {
		return $this->formatBuildRawValue;
	}
	
	public static function generateFormatBuildRawValue($formatBuildType, $dateAvailable, $timeAvailable, $dateTimePrecision, $dateTimeOffset) {
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
		if ($dateTimePrecision > 0) {
			$format .= self::FORMAT_MICROSECONDS;
		}
		if ($dateTimePrecision >= 7) {
			if ($formatBuildType == self::FORMAT_BUILD_TYPE_PARSE) {
				$format .= '*';
			} else {
				//Thats, that buildRawValue and generateFormatBuildRawValue are compatible to each other
				//MSSQL can handle it
				$format .= '0';
			}
		}
		
		if ($dateTimeOffset) {
			$format .= ' ' . self::FORMAT_TIMEZONE;
		}
		return $format;
	}
	
	public function copy($newColumnName = null) {
		if (is_null($newColumnName)) {
			$newColumnName = $this->getName();
		}
		$newColumn = new self($newColumnName, $this->isDateAvailable(), $this->isTimeAvailable());
		$newColumn->applyCommonAttributes($this);
		return $newColumn;
	}
}

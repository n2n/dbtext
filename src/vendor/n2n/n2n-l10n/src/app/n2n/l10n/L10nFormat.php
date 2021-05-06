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
namespace n2n\l10n;

class L10nFormat {
	private $datePatterns;
	private $timePatterns;
	private $dateInputPattern;
	private $timeInputPattern;
	
	public function __construct(array $datePatterns, array $timePatterns, $dateInputPattern, 
			$timeInputPattern) {
		$this->datePatterns = $datePatterns;
		$this->timePatterns = $timePatterns;
		$this->dateInputPattern = $dateInputPattern;
		$this->timeInputPattern = $timeInputPattern;
	}

	public function getDatePatterns() {
		return $this->datePatterns;
	}

	public function getTimePatterns() {
		return $this->timePatterns;
	}

	public function getDateInputPattern() {
		return $this->dateInputPattern;
	}

	public function getTimeInputPattern() {
		return $this->timeInputPattern;
	}
		
// 	public function getDatePattern($n2nLocale, $style) {
// 		return $this->getPattern((string) $n2nLocale . self::PROP_DATE_PATTERN_STYLE_SUFFIX . $style);
// 	}
	
// 	public function getTimePattern($n2nLocale, $style) {
// 		return $this->getPattern((string) $n2nLocale . self::PROP_TIME_PATTERN_STYLE_SUFFIX . $style);
// 	}
	
// 	public function getDateInputPattern($n2nLocale) {
// 		return $this->getPattern((string) $n2nLocale . self::PROP_DATE_PATTERN_INPUT_SUFFIX);
// 	}
	
// 	public function getTimeInputPattern($n2nLocale) {
// 		return $this->getPattern((string) $n2nLocale . self::PROP_TIME_PATTERN_INPUT_SUFFIX);
// 	}
	
// 	public function getDateTimeFormat($n2nLocale) {
// 		return $this->getPattern((string) $n2nLocale . self::PROP_DATETIME_FORMAT_SUFFIX);
// 	}
	
// 	private function getAvailableStyles() {
// 		return array(DateTimeFormat::STYLE_SHORT, DateTimeFormat::STYLE_MEDIUM,
// 				DateTimeFormat::STYLE_LONG, DateTimeFormat::STYLE_FULL);
// 	}
}

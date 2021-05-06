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

class SimpleDateTimeFormat {
	private $formatter;
	private $timeZone;
	
	public function __construct($n2nLocale, $pattern, \DateTimeZone $timeZone = null) {		
		$this->formatter = new \IntlDateFormatter((string) $n2nLocale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL);
		$this->formatter->setPattern((string) $pattern);
		$this->timeZone = $timeZone;
		
		if ($timeZone !== null) {
			$this->formatter->setTimeZone($timeZone->getName());
		}
	}
	/**
	 * 
	 * @return bool
	 */
	public function isLenient() {
		return $this->formatter->isLenient();
	}
	/**
	 * 
	 * @param bool $lenient
	 */
	public function setLenient($lenient) {
		$this->formatter->setLenient((boolean) $lenient);
	}
	/**
	 * 
	 * @return \IntlDateFormatter
	 */
	public function getFormatter() {
		return $this->formatter;
	}
	
	public function parse($str) {
		$timestamp = $this->formatter->parse($str);
		if (!$timestamp) {
			throw new ParseException('Unable to parse: ' . $str);
		}
		$dt = new \DateTime();
		$dt->setTimestamp($timestamp);
// 		if (isset($this->timeZone)) {
// 			$dt->setTimezone($this->timeZone);
// 		}
		return $dt;
	}
	
	public function format(\DateTime $dateTime) {
		return $this->formatter->format($dateTime);
	}
}

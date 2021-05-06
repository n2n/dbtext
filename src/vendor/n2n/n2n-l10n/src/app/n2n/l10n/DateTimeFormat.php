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

class DateTimeFormat {
	const STYLE_NONE = 'none';
	const STYLE_SHORT = 'short';
	const STYLE_MEDIUM = 'medium';
	const STYLE_LONG = 'long';
	const STYLE_FULL = 'full';
	
	private $n2nLocaleId;
	private $timeZone;
	private $lenient = false;
	private $intlDateFormatter;
	private $dateTimePattern;
	
	public static function createDateInstance($n2nLocale, $dateStyle, \DateTimeZone $timeZone = null) {
		return self::createDateTimeInstance($n2nLocale, $dateStyle, self::STYLE_NONE, $timeZone);
	}
	
	public static function createTimeInstance($n2nLocale, $timeStyle, \DateTimeZone $timeZone = null) {
		return self::createDateTimeInstance($n2nLocale, self::STYLE_NONE, $timeStyle, $timeZone);
	}
	
	public static function createDateTimeInstance($n2nLocale, $dateStyle = self::STYLE_MEDIUM, 
			$timeStyle = self::STYLE_SHORT, \DateTimeZone $timeZone = null) {
		if ($n2nLocale === null) $n2nLocale = N2nLocale::getDefault();
		if ($timeZone === null) $timeZone = new \DateTimeZone(date_default_timezone_get());
		
		return new DateTimeFormat($n2nLocale, $dateStyle, $timeStyle, $timeZone);
	}
	
	private function __construct($n2nLocale, $dateStyle = null, $timeStyle = null, \DateTimeZone $timeZone = null) {
		$this->n2nLocaleId = (string) $n2nLocale;
		$this->timeZone = $timeZone;
		
		if (L10n::getL10nConfig()->isEnabled()) {
			L10nUtils::ensureL10nsupportIsAvailable();
			return $this->initL10nFormat(L10n::getL10nConfig(), 
					$this->convertStyle($dateStyle), $this->convertStyle($timeStyle), $timeZone);
		} else {
			return $this->initPseudoL10nFormat(L10n::getPseudoL10nConfig(), $dateStyle, $timeStyle, $timeZone);
		}
	}
	
	private function initL10nFormat(L10nConfig $config, $dateStyle, $timeStyle) {
		$this->intlDateFormatter = new \IntlDateFormatter((string) $this->n2nLocaleId, $dateStyle, 
				$timeStyle, ($this->timeZone === null ? null : $this->timeZone->getName()));
	}
	
	private function initPseudoL10nFormat(PseudoL10nConfig $config, $dateStyle, $timeStyle) {
		$datePattern = null;
		$dateStyle = $this->lookupStyle($dateStyle, $config->getDefaultDateStyle($this->n2nLocaleId), self::DEFAULT_DATE_STYLE);
		if ($dateStyle != self::STYLE_NONE) {
			$datePattern = $config->getDatePattern($this->n2nLocaleId, $dateStyle);
		}
		
		$timePattern = null;
		$timeStyle = $this->lookupStyle($timeStyle, $config->getDefaultTimeStyle($this->n2nLocaleId), self::DEFAULT_TIME_STYLE);
		if ($timeStyle != self::STYLE_NONE) {
			$timePattern = $config->getTimePattern($this->n2nLocaleId, $timeStyle);
		}
		
		$pattern = null;
		if (isset($datePattern) && isset($timePattern)) {
			$pattern = $config->getDateTimeFormat($this->n2nLocaleId);
			$pattern = str_replace(PseudoL10nConfig::PLACE_HOLDER_DATETIME_FORMAT_DATE, $datePattern, $pattern);
			$pattern = str_replace(PseudoL10nConfig::PLACE_HOLDER_DATETIME_FORMAT_TIME, $timePattern, $pattern);
		} else if (isset($datePattern)) {
			$pattern = $datePattern;
		} else {
			$pattern = $timePattern;
		}
		
		$this->dateTimePattern = $pattern;
	}
	
	private function lookupStyle($style, $defaultStyle, $defaultDefaultStyle) {
		if (is_null($style)) {
			$style = $defaultStyle;
		}
		
		if (is_null($style)) {
			$style = $defaultDefaultStyle;
		}
		
		return $style;
	}
	
	public function getPattern() {
		if (isset($this->intlDateFormatter)) {
			return $this->intlDateFormatter->getPattern();
		}
		
		return $this->dateTimePattern;
	}
	
	public static function convertStyle($style) {
		switch ($style) {
			case self::STYLE_NONE:
				return \IntlDateFormatter::NONE;
			case self::STYLE_SHORT:
				return \IntlDateFormatter::SHORT;
			case self::STYLE_MEDIUM:
				return \IntlDateFormatter::MEDIUM;
			case self::STYLE_LONG:
				return \IntlDateFormatter::LONG;
			case self::STYLE_FULL:
				return \IntlDateFormatter::FULL;
			default:
				throw new InvalidDateTimeFormatStyleException('L10n invalid date time format style \'' 
						. $style . '\'. Available styles: ' . implode(', ', array(
								self::STYLE_NONE, self::STYLE_SHORT, self::STYLE_MEDIUM,
								self::STYLE_LONG, self::STYLE_FULL)));
		}
	}
	
	public static function getStyles() {
		return array(self::STYLE_NONE, self::STYLE_SHORT, self::STYLE_MEDIUM, 
				self::STYLE_LONG, self::STYLE_FULL);
	}
	
	public function setLenient($lenient) {
		$this->lenient = (boolean) $lenient;
		
		if (isset($this->intlDateFormatter)) {
			$this->intlDateFormatter->setLenient($lenient);
		}
	}
	/**
	 * @param string $str
	 * @return \DateTime
	 */
	public function parse($str) {
		if (isset($this->intlDateFormatter)) {
			$timestamp = $this->intlDateFormatter->parse($str);
			if (!$timestamp || is_float($timestamp)) {
				throw new ParseException('Unable to parse: ' . $str);
			}
			
			$dt = new \DateTime();
			$dt->setTimestamp($timestamp);
			if (null !== ($timeZoneId = $this->intlDateFormatter->getTimeZoneId())) {
				$dt->setTimezone(new \DateTimeZone($timeZoneId));
			}
			return $dt;
		}
		
		$dt = null;
		if ($this->timeZone === null) {
			$dt = \DateTime::createFromFormat($this->dateTimePattern, $str);
		} else {
			$dt = \DateTime::createFromFormat($this->dateTimePattern, $str, $this->timeZone);
		}
		if (!$dt) {
			throw new ParseException('Unable to parse: ' . $str);
		}
		return $dt;
	}
	
	public function format(\DateTime $dateTime) {
		if (isset($this->intlDateFormatter)) {
			return $this->intlDateFormatter->format($dateTime->getTimestamp());
		}
		
		if (isset($this->timeZone)) {
			$nDateTime = new \DateTime();
			$nDateTime->setTimestamp($dateTime->getTimestamp());
			$nDateTime->setTimezone($this->timeZone);
		}
	
		return $dateTime->format($this->dateTimePattern);
	}
}

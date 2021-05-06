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

use n2n\core\N2N;

class L10nUtils {
	public static function isL10nSupportAvailable() {
		return class_exists('\Locale', false);
	}
		
	public static function ensureL10nsupportIsAvailable() {
		if (self::isL10nSupportAvailable()) return;
		
		throw new L10nSupportNotAvailableException(
				'PHP module not installed: PECL intl >= 1.0.0');
	}
	
	public static function translateModuleTextCode(DynamicTextCollection $dtc, $module, 
			$textCode, array $args = null, $num = null, array $replacements = null) {
		$fallbackToCode = $module === null || $dtc->containsModule($module);
		
		if (null !== ($text = $dtc->translate($textCode, $args, $num, $replacements, $fallbackToCode))) {
			return $text;
		}
					
		$dtc = new DynamicTextCollection($module, $dtc->getN2nLocaleIds());
		return $dtc->translate($textCode, $args, $num, $replacements);
	}
	
	public static function formatNumber($value, $n2nLocale, $style = \NumberFormatter::DECIMAL, $pattern = null) {
		$nf = new \NumberFormatter((string) $n2nLocale, $style, $pattern);
		return $nf->format($value);
	}
		
	/**
	 * @param float $value
	 * @param N2nLocale|string $n2nLocale
	 * @param string $currency The 3-letter ISO 4217 currency code indicating the currency to use.
	 * @return string
	 */
	public static function formatCurrency(float $value, $n2nLocale, $currency = null) {
		$nf = new \NumberFormatter((string) $n2nLocale, \NumberFormatter::CURRENCY);
		if ($currency) {
			return $nf->formatCurrency($value, $currency);
		} else {
			return $nf->format($value);
		}
	}

	public static function formatDateTimeWithIcuPattern(\DateTime $dateTime, $n2nLocale, $icuPattern, 
			\DateTimeZone $timeZone = null) {
		$dateTimeFormatter = new SimpleDateTimeFormat($n2nLocale, $icuPattern, $timeZone);
		return $dateTimeFormatter->format($dateTime);
	}
	
	public static function parseDateTimeWithIcuPattern($expression, $n2nLocale, $icuPattern, 
			\DateTimeZone $timeZone = null, $lenient = true) {
		$dateTimeFormatter = new SimpleDateTimeFormat($n2nLocale, $icuPattern, $timeZone);
		$dateTimeFormatter->setLenient($lenient);
		return $dateTimeFormatter->parse($expression);
	}
	
	public static function formatDateTimeInput(\DateTime $dateTime, N2nLocale $n2nLocale, $dateStyle = null, 
			$timeStyle = null, \DateTimeZone $timeZone = null) {
		if ($dateStyle === null) $dateStyle = self::determineDateStyle($n2nLocale, true);
		if ($timeStyle === null) $timeStyle = self::determineTimeStyle($n2nLocale, true);
		
		$dateTimeFormat = DateTimeFormat::createDateTimeInstance($n2nLocale, $dateStyle, $timeStyle, $timeZone);
		return $dateTimeFormat->format($dateTime);
	}
	
	public static function parseDateTimeInput($expression, N2nLocale $n2nLocale, $dateStyle = null, 
			$timeStyle = null, \DateTimeZone $timeZone = null, $lenient = true) {
		if ($dateStyle === null) $dateStyle = self::determineDateStyle($n2nLocale, true);
		if ($timeStyle === null) $timeStyle = self::determineTimeStyle($n2nLocale, true);
		
		$dateFormatter = DateTimeFormat::createDateTimeInstance($n2nLocale, $dateStyle, $timeStyle, $timeZone);
		$dateFormatter->setLenient($lenient);
		return $dateFormatter->parse($expression);
	}
	
	/**
	 * @param \DateTime $dateTime
	 * @param N2nLocale $n2nLocale
	 * @param string $dateStyle
	 * @param string $timeStyle
	 * @param \DateTimeZone $timeZone
	 * @return string
	 */
	public static function formatDateTime(\DateTime $dateTime, N2nLocale $n2nLocale, string $dateStyle = null, 
			string $timeStyle = null, \DateTimeZone $timeZone = null) {
		if ($dateStyle === null) $dateStyle = self::determineDateStyle($n2nLocale, false);
		if ($timeStyle === null) $timeStyle = self::determineTimeStyle($n2nLocale, false);
		
		$dateFormat = DateTimeFormat::createDateTimeInstance($n2nLocale, $dateStyle, $timeStyle, $timeZone);
		return $dateFormat->format($dateTime);
	}
	
	/**
	 * @param \DateTime $dateTime
	 * @param N2nLocale $n2nLocale
	 * @param string $dateStyle
	 * @param \DateTimeZone $timeZone
	 * @return string
	 */
	public static function formatDate(\DateTime $dateTime, N2nLocale $n2nLocale, string $dateStyle = null,
			\DateTimeZone $timeZone = null) {
		return self::formatDateTime($dateTime, $n2nLocale, $dateStyle, DateTimeFormat::STYLE_NONE, $timeZone);
	}
	
	/**
	 * @param \DateTime $dateTime
	 * @param N2nLocale $n2nLocale
	 * @param string $timeStyle
	 * @param \DateTimeZone $timeZone
	 * @return string
	 */
	public static function formatTime(\DateTime $dateTime, N2nLocale $n2nLocale, string $timeStyle = null, 
			\DateTimeZone $timeZone = null) {
		return self::formatDateTime($dateTime, $n2nLocale, DateTimeFormat::STYLE_NONE, $timeStyle, $timeZone);
	}
	
	/**
	 * @param string $expression
	 * @param N2nLocale $n2nLocale
	 * @param string $dateStyle
	 * @param string $timeStyle
	 * @param \DateTimeZone $timeZone
	 * @param bool $lenient
	 * @return \DateTime
	 */
	public static function parseDateTime($expression, $n2nLocale, $dateStyle = null, $timeStyle = null, 
			\DateTimeZone $timeZone = null, $lenient = true) {
		if ($dateStyle === null) $dateStyle = self::determineDateStyle($n2nLocale, false);
		if ($timeStyle === null) $timeStyle = self::determineTimeStyle($n2nLocale, false);

		$dateFormat = DateTimeFormat::createDateTimeInstance($n2nLocale, $dateStyle, $timeStyle, $timeZone);
		$dateFormat->setLenient($lenient);
		return $dateFormat->parse($expression);
	}
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @param bool $useInput
	 * @return string
	 */
	public static function determineDateStyle(N2nLocale $n2nLocale, bool $useInput = false) {
		if (null !== ($style = L10n::getL10nConfig()->getStyle($n2nLocale))) {
			return $useInput ? $style->geDefaultInputDateStyle() : $style->getDefaultDateStyle();
		}
		
		return DateTimeFormat::STYLE_MEDIUM;
	}
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @param bool $useInput
	 * @return string
	 */
	public static function determineTimeStyle(N2nLocale $n2nLocale, bool $useInput = false) {
		if (null !== ($style = N2N::getAppConfig()->l10n()->getStyle($n2nLocale))) {
			return $useInput ? $style->getDefaultInputTimeStyle() : $style->getDefaultTimeStyle();
		}
		
		return DateTimeFormat::STYLE_SHORT;
	}
}

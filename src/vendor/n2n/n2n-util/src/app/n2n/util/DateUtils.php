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
namespace n2n\util;

class DateUtils {
	
	public static function dateIntervalToSeconds(\DateTime $from, \DateInterval $dateInterval) {
		$to = new \DateTime();
		$to->setTimestamp($from->getTimestamp());
		$to->add($dateInterval);
		return $to->getTimestamp() - $from->getTimestamp();
	}
	
	public static function createDateTimeFromTimestamp(int $unixtimestamp) {
		$dateTime = new \DateTime();
		$dateTime->setTimestamp($unixtimestamp);
		return $dateTime;
	}
	
	public static function createDateTime($dateTimeSpec) {
		if ($dateTimeSpec === null) return null;
		
		try {
			return new \DateTime($dateTimeSpec);
		} catch (\Exception $e) {
			throw new DateParseException($e->getMessage(), 0, $e);
		}
	}

	public static function createDateTimeForThomas($dateTimeSpec = null) {
		try {
			return new \DateTime($dateTimeSpec);
		} catch (\Exception $e) {
			throw new DateParseException($e->getMessage(), 0, $e);
		}
	}	
	
	public static function createDateInterval($intervalSpec) {
		if ($intervalSpec === null) return null;
	
		try {
			return new \DateInterval($intervalSpec);
		} catch (\Exception $e) {
			throw new DateParseException($e->getMessage(), 0, $e);
		}
	}
	/**
	 * @param string $format
	 * @param string $dateTimeString
	 * @param \DateTimeZone $timeZone
	 * @throws \n2n\util\DateParseException
	 * @return \DateTime
	 */
	public static function createDateTimeFromFormat($format, $dateTimeString, \DateTimeZone $timeZone = null) {
		if (null === $timeZone) {
			$dateTime = @\DateTime::createFromFormat($format, $dateTimeString);
		} else {
			$dateTime = @\DateTime::createFromFormat($format, $dateTimeString, $timeZone);
		}
		if ($dateTime === false) {
			throw new DateParseException('Invalid date time string \'' . $dateTimeString . '\' for format \'' 
					. $format . '\' given. Reason: ' . self::buildLastDateTimeErrorsString(
							'Could not parse date: ' . $dateTimeString));
		}
		return $dateTime;
	}
	
	public static function formatDateTime(\DateTime $dateTime, $format) {
		$dateTimeString = @$dateTime->format($format);
		if ($dateTimeString === false) {
			$message = ($err = error_get_last()) ? $err['message'] : null;
			throw new \InvalidArgumentException($message);
		}
		return $dateTimeString;
	}
	
	private static function buildLastDateTimeErrorsString($defaultErrorMessage) {
		$lastErrorsString = $defaultErrorMessage;
		$lastErrors = \DateTime::getLastErrors();
		if (false !== $lastErrors && $lastErrors['error_count'] > 0) {
			$tmpArray = array();
			foreach ($lastErrors['errors'] as $key => $value) {
				$tmpArray[] = $key . ': ' . $value;
			}
			$lastErrorsString = implode(', ', $tmpArray);
		}
		return $lastErrorsString;
	}
	
	/**
	 * @param \DateTime $dateTime
	 * @return NULL|string
	 */
	static function dateTimeToIso(?\DateTime $dateTime) {
		if ($dateTime === null) {
			return null;
		}
		
		return $dateTime->format(\DateTime::ATOM);
	}
	
	/**
	 * @param string $iso
	 * @return NULL|\DateTime
	 */
	static function isoToDateTime(?string $iso) {
		if ($iso === null) {
			return null;
		}
		
		return new \DateTime($iso);
	}
	
	/**
	 * @param string $timestamp
	 * @return NULL|\DateTime
	 */
	static function timestampToDateTime(?string $timestamp) {
		if ($timestamp === null) {
			return null;
		}
		
		return self::createDateTimeFromTimestamp($timestamp);
	}
	
	const SQL_DATE_TIME_FORMAT = 'Y-m-d H:i:s';
	
	/**
	 * @param \DateTime $dateTime
	 * @return null|string
	 */
	static function dateTimeToSql(\DateTime $dateTime = null) {
		if (null === $dateTime) return null;
		
		return $dateTime->format(self::SQL_DATE_TIME_FORMAT);
	}
	
	/**
	 * @param string $sqlDateTimeString
	 * @return null|\DateTime
	 */
	static function sqlToDateTime(string $sqlDateTimeString) {
		if (null === $sqlDateTimeString) return null;
		
		return self::createDateTimeFromFormat(self::SQL_DATE_TIME_FORMAT,
				$sqlDateTimeString);
	}
}

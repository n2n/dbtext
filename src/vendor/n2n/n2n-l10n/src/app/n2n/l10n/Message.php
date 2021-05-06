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

use n2n\util\StringUtils;
use n2n\l10n\impl\LstrMessage;
use n2n\l10n\impl\StaticMessage;
use n2n\l10n\impl\TextCodeMessage;

abstract class Message {
	const SEVERITY_SUCCESS = 1;
	const SEVERITY_INFO = 2;
	const SEVERITY_WARN = 4;
	const SEVERITY_ERROR = 8;
	const ALL_SEVERITIES = 15;
	
	private $severity;
	private $processed = false;
	
	/**
	 * @param int $severity
	 */
	protected function __construct($severity = null) {
		$this->severity = $severity ?? self::SEVERITY_ERROR;
	}
	
	/**
	 * @return int
	 */
	public function getSeverity() {
		return $this->severity;
	}
	
	/**
	 * @param int $severity
	 * @return \n2n\l10n\Message
	 */
	public function setSeverity(?int $severity) {
		$this->severity = $severity;
		return $this;
	} 
	
	/**
	 * @return bool
	 */
	public function isProcessed(): bool {
		return $this->processed;
	}
	
	/**
	 * @param bool $processed
	 * @return \n2n\l10n\Message
	 */
	public function setProcessed(bool $processed) {
		$this->processed = $processed;
		return $this;
	}
	
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @param string $moduleNamespace
	 * @return string
	 */
	public abstract function t(N2nLocale $n2nLocale, string $moduleNamespace = null): string;
	
	/**
	 * @param DynamicTextCollection $dtc
	 * @param N2nLocale $n2nLocale
	 * @param string $moduleNamespace
	 * @return string
	 */
	public abstract function tByDtc(DynamicTextCollection $dtc): string;
	
	/**
	 * @return string
	 */
	public abstract function __toString(): string;
	
	/**
	 * @param string|Lstr|Message $arg
	 * @param int|null $severity
	 * @return \n2n\l10n\Message|\n2n\l10n\impl\LstrMessage|\n2n\l10n\impl\StaticMessage
	 */
	public static function create($arg, int $severity = null) {
		if ($arg instanceof Message) {
			return $arg;
		}
		
		if ($arg instanceof Lstr) {
			return new LstrMessage($arg, $severity);
		}
		
		return new StaticMessage(StringUtils::strOf($arg), $severity);
	}
	
	/**
	 * @param string|Lstr|Message|null $arg
	 * @param int|null $severity
	 * @return \n2n\l10n\Message|null
	 */
	public static function build($arg, int $severity = null) {
		if ($arg === null) {
			return null;
		}
		
		return self::create($arg, $severity);
	}
	
	
	/**
	 * @param string $code
	 * @param int $severity
	 * @param string $moduleNamespace
	 * @param int $num
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	public static function createCode(string $code, int $severity = null, string $moduleNamespace = null, int $num = null) {
		return new TextCodeMessage($code, null, $severity, $moduleNamespace, $num);
	}
	
	/**
	 * @param string $code
	 * @param array $args
	 * @param int $severity
	 * @param string $moduleNamespace
	 * @param int $num
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	public static function createCodeArg(string $code, array $args = null, int $severity = null, string $moduleNamespace = null, int $num = null) {
		return new TextCodeMessage($code, $args, $severity, $moduleNamespace, $num);
	}
}

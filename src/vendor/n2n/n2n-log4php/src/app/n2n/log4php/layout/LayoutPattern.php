<?php
namespace n2n\log4php\layout;
use n2n\log4php\LoggerException;
use n2n\log4php\LoggerLayout;
use n2n\log4php\logging\LoggingEvent;
use n2n\log4php\pattern\PatternConverter;
use n2n\log4php\pattern\PatternParser;

/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package log4php
 */

/**
 * A flexible layout configurable with a pattern string.
 * 
 * Configurable parameters:
 * 
 * * converionPattern - A string which controls the formatting of logging 
 *   events. See docs for full specification.
 * 
 * @package log4php
 * @subpackage layouts
 * @version $Revision: 1395470 $
 */
class LayoutPattern extends LoggerLayout {
	
	/** Default conversion pattern */
	const DEFAULT_CONVERSION_PATTERN = '%date %-5level %logger %message%newline';

	/** Default conversion TTCC Pattern */
	const TTCC_CONVERSION_PATTERN = '%d [%t] %p %c %x - %m%n';

	/** The conversion pattern. */ 
	protected $pattern = self::DEFAULT_CONVERSION_PATTERN;
	
	/** Maps conversion keywords to the relevant converter (default implementation). */
	protected static $defaultConverterMap = array(
		'c' => '\n2n\log4php\pattern\ConverterLogger',
		'lo' => '\n2n\log4php\pattern\ConverterLogger',
		'logger' => '\n2n\log4php\pattern\ConverterLogger',
		
		'C' => '\n2n\log4php\pattern\converter\ConverterClass',
		'class' => '\n2n\log4php\pattern\converter\ConverterClass',
		
		'cookie' => 'LoggerPatternConverterCookie',
		
		'd' => '\n2n\log4php\pattern\converter\ConverterDate',
		'date' => '\n2n\log4php\pattern\converter\ConverterDate',
		
		'e' => '\n2n\log4php\pattern\converter\ConverterEnvironment',
		'env' => '\n2n\log4php\pattern\converter\ConverterEnvironment',
		
		'ex' => '\n2n\log4php\pattern\converter\ConverterThrowable',
		'exception' => '\n2n\log4php\pattern\converter\ConverterThrowable',
		'throwable' => '\n2n\log4php\pattern\converter\ConverterThrowable',
		
		'F' => '\n2n\log4php\pattern\converter\ConverterFile',
		'file' => '\n2n\log4php\pattern\converter\ConverterFile',
			
		'l' => '\n2n\log4php\pattern\converter\ConverterLocation',
		'location' => '\n2n\log4php\pattern\converter\ConverterLocation',
		
		'L' => '\n2n\log4php\pattern\converter\ConverterLine',
		'line' => '\n2n\log4php\pattern\converter\ConverterLine',
		
		'm' => '\n2n\log4php\pattern\converter\ConverterMessage',
		'msg' => '\n2n\log4php\pattern\converter\ConverterMessage',
		'message' => '\n2n\log4php\pattern\converter\ConverterMessage',
		
		'M' => '\n2n\log4php\pattern\converter\ConverterMethod',
		'method' => '\n2n\log4php\pattern\converter\ConverterMethod',
		
		'n' => '\n2n\log4php\pattern\converter\NewLine',
		'newline' => '\n2n\log4php\pattern\converter\NewLine',
		
		'p' => '\n2n\log4php\pattern\converter\ConverterLevel',
		'le' => '\n2n\log4php\pattern\converter\ConverterLevel',
		'level' => '\n2n\log4php\pattern\converter\ConverterLevel',
	
		'r' => '\n2n\log4php\pattern\converter\ConverterRelative',
		'relative' => '\n2n\log4php\pattern\converter\ConverterRelative',
		
		'req' => '\n2n\log4php\pattern\converter\ConverterRequest',
		'request' => '\n2n\log4php\pattern\converter\ConverterRequest',
		
		's' => '\n2n\log4php\pattern\converter\ConverterServer',
		'server' => '\n2n\log4php\pattern\converter\ConverterServer',
		
		'ses' => '\n2n\log4php\pattern\converter\ConverterSession',
		'session' => '\n2n\log4php\pattern\converter\ConverterSession',
		
		'sid' => 'LoggerPatternConverterSessionID',
		'sessionid' => 'LoggerPatternConverterSessionID',
	
		't' => 'LoggerPatternConverterProcess',
		'pid' => 'LoggerPatternConverterProcess',
		'process' => 'LoggerPatternConverterProcess',
		
		'x' => 'LoggerPatternConverterNDC',
		'ndc' => 'LoggerPatternConverterNDC',
			
		'X' => '\n2n\log4php\pattern\converter\ConverterMDC',
		'mdc' => '\n2n\log4php\pattern\converter\ConverterMDC',
	);

	/** Maps conversion keywords to the relevant converter. */
	protected $converterMap = array();
	
	/** 
	 * Head of a chain of Converters.
	 * @var PatternConverter
	 */
	private $head;

	/** Returns the default converter map. */
	public static function getDefaultConverterMap() {
		return self::$defaultConverterMap;
	}
	
	/** Constructor. Initializes the converter map. */
	public function __construct() {
		$this->converterMap = self::$defaultConverterMap;
	}
	
	/**
	 * Sets the conversionPattern option. This is the string which
	 * controls formatting and consists of a mix of literal content and
	 * conversion specifiers.
	 * @param string $conversionPattern
	 */
	public function setConversionPattern($conversionPattern) {
		$this->pattern = $conversionPattern;
	}
	
	/**
	 * Processes the conversion pattern and creates a corresponding chain of 
	 * pattern converters which will be used to format logging events. 
	 */
	public function activateOptions() {
		if (!isset($this->pattern)) {
			throw new LoggerException("Mandatory parameter 'conversionPattern' is not set.");
		}
		
		$parser = new PatternParser($this->pattern, $this->converterMap);
		$this->head = $parser->parse();
	}
	
	/**
	 * Produces a formatted string as specified by the conversion pattern.
	 *
	 * @param LoggingEvent $event
	 * @return string
	 */
	public function format(LoggingEvent $event) {
		$sbuf = '';
		$converter = $this->head;
		while ($converter !== null) {
			$converter->format($sbuf, $event);
			$converter = $converter->next;
		}
		return $sbuf;
	}
}
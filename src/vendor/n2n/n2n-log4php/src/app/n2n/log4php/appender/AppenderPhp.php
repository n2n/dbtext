<?php
namespace n2n\log4php\appender;
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 *	   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * AppenderPhp logs events by creating a PHP user-level message using 
 * the PHP's throw new \n2n\log4php\LoggerException()function.
 *
 * This appender has no configurable parameters.
 *
 * Levels are mapped as follows:
 * 
 * - <b>level < WARN</b> mapped to E_USER_NOTICE
 * - <b>WARN <= level < ERROR</b> mapped to E_USER_WARNING
 * - <b>level >= ERROR</b> mapped to E_USER_ERROR  
 *
 * @version $Revision: 1337820 $
 * @package log4php
 * @subpackage appenders
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link http://logging.apache.org/log4php/docs/appenders/php.html Appender documentation
 */ 
class AppenderPhp extends \n2n\log4php\LoggerAppender {

	public function append(\n2n\log4php\logging\LoggingEvent $event) {
		$level = $event->getLevel();
		if($level->isGreaterOrEqual(\n2n\log4php\LoggerLevel::getLevelError())) {
			throw new \n2n\log4php\LoggerException($this->layout->format($event), E_USER_ERROR);
		} else if ($level->isGreaterOrEqual(\n2n\log4php\LoggerLevel::getLevelWarn())) {
			throw new \n2n\log4php\LoggerException($this->layout->format($event), E_USER_WARNING);
		} else {
			throw new \n2n\log4php\LoggerException($this->layout->format($event), E_USER_NOTICE);
		}
	}
}

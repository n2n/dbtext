<?php
namespace n2n\log4php;
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
 *
 * @package log4php
 */

/**
 * The root logger.
 *
 * @version $Revision: 1343190 $
 * @package log4php
 * @see \n2n\log4php\Logger
 */
class LoggerRoot extends \n2n\log4php\Logger {
	/**
	 * Constructor
	 *
	 * @param integer $level initial log level
	 */
	public function __construct(\n2n\log4php\LoggerLevel $level = null) {
		parent::__construct('root');

		if($level == null) {
			$level = \n2n\log4php\LoggerLevel::getLevelAll();
		}
		$this->setLevel($level);
	} 
	
	/**
	 * @return \n2n\log4php\LoggerLevel the level
	 */
	public function getEffectiveLevel() {
		return $this->getLevel();
	}
	
	/**
	 * Override level setter to prevent setting the root logger's level to 
	 * null. Root logger must always have a level.
	 * 
	 * @param \n2n\log4php\LoggerLevel $level
	 */
	public function setLevel(\n2n\log4php\LoggerLevel $level = null) {
		if (isset($level)) {
			parent::setLevel($level);
		} else {
			throw new \n2n\log4php\LoggerException("log4php: Cannot set LoggerRoot level to null.", E_USER_WARNING);
		}
	}
	
	/**
	 * Override parent setter. Root logger cannot have a parent.
	 * @param \n2n\log4php\Logger $parent
	 */
	public function setParent(\n2n\log4php\Logger $parent) {
		throw new \n2n\log4php\LoggerException("log4php: LoggerRoot cannot have a parent.", E_USER_WARNING);
	}
}
